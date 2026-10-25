<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Services;

use App\Domains\Pwa\Jobs\SendPushNotification;
use App\Models\Announcement;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\PushSubscription;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Scopes\SchoolScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Web Push notification untuk PWA (Dok 03 §5.3/5.4, FR-1304).
 *
 * Alur: browser subscribe → endpoint tersimpan per perangkat →
 * event domain (pengumuman tayang, nilai dirilis, tagihan, alpa) →
 * job antrean per subscription.
 */
class PushService
{
    public function enabled(): bool
    {
        if ($this->publicKey() === null || $this->privateKey() === null) {
            return false;
        }

        if (app()->runningUnitTests()) {
            return (bool) config('push.enabled_in_tests', false);
        }

        return (bool) config('push.enabled', true);
    }

    public function publicKey(): ?string
    {
        $key = config('push.vapid.public_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    private function privateKey(): ?string
    {
        $key = config('push.vapid.private_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    /**
     * Daftarkan perangkat. Idempoten: endpoint yang sama → baris yang sama
     * (diperbarui ke user/sekolah terbaru — perangkat bersama).
     */
    public function subscribe(User $user, int $schoolId, string $endpoint, string $p256dh, string $auth, ?string $userAgent = null): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'school_id' => $schoolId,
                'user_id' => $user->id,
                'p256dh' => $p256dh,
                'auth' => $auth,
                'user_agent' => $userAgent ? Str::limit($userAgent, 500) : null,
                'terakhir_dipakai_at' => now(),
            ]
        );
    }

    public function unsubscribe(User $user, string $endpoint): bool
    {
        return (bool) PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->delete();
    }

    // ---------- Pemicu event (FR-1304) ----------

    /**
     * Pengumuman tayang → siswa & orang tua sesuai target segmen.
     */
    public function notifyAnnouncement(Announcement $announcement): void
    {
        if (! $this->enabled()) {
            return;
        }

        $users = $this->announcementAudience($announcement);

        $this->notifyUsers(
            $users,
            ($announcement->penting ? '[PENTING] ' : '').$announcement->judul,
            Str::limit(strip_tags((string) $announcement->isi), 120),
            route('pwa.pengumuman.show', $announcement),
        );
    }

    /**
     * Nilai dirilis → siswa rombel + orang tuanya.
     */
    public function notifyGradeRelease(Rombel $rombel, Semester $semester): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->notifyUsers(
            $this->usersForStudents($this->rombelStudents($rombel)),
            'Nilai Telah Dirilis',
            "Nilai {$rombel->nama} semester {$semester->nama} sudah dapat dilihat.",
            route('pwa.nilai'),
        );
    }

    /**
     * Tagihan baru → orang tua siswa.
     */
    public function notifyNewInvoice(Invoice $invoice): void
    {
        if (! $this->enabled()) {
            return;
        }

        $student = $invoice->student;

        if ($student === null) {
            return;
        }

        $this->notifyUsers(
            $this->guardianUsersForStudents(collect([$student])),
            'Tagihan Baru',
            "{$invoice->feeType?->nama} {$invoice->periode_label} sebesar Rp".number_format((float) $invoice->nominal, 0, ',', '.')." untuk {$student->nama}.",
            route('pwa.tagihan'),
        );
    }

    /**
     * Siswa alpa → orang tuanya.
     *
     * @param  Collection<int, Student>  $students
     */
    public function notifyAbsence(Collection $students, string $tanggal): void
    {
        if (! $this->enabled() || $students->isEmpty()) {
            return;
        }

        $nama = $students->map->nama->join(', ');
        $tgl = Carbon::parse($tanggal)->translatedFormat('d M Y');

        $this->notifyUsers(
            $this->guardianUsersForStudents($students),
            'Kehadiran Anak',
            "{$nama} tercatat alpa pada {$tgl}.",
            route('pwa.absensi'),
        );
    }

    // ---------- Resolusi audiens ----------

    /**
     * @return Collection<int, User>
     */
    private function announcementAudience(Announcement $announcement): Collection
    {
        $schoolId = $announcement->school_id;

        /** @var array<string,mixed>|null $targetData */
        $targetData = $announcement->target_data;
        $targetData = $targetData ?? [];
        /** @var array<int> $rombelIds */
        $rombelIds = array_values(array_map('intval', (array) ($targetData['rombel_ids'] ?? [])));
        /** @var array<string> $roles */
        $roles = array_values(array_map('strval', (array) ($targetData['roles'] ?? [])));

        $students = match ($announcement->target_type) {
            'kelas' => $this->studentsInRombels($schoolId, $rombelIds),
            'peran' => $this->studentsForRoles($schoolId, $roles),
            default => Student::where('school_id', $schoolId)->where('status', 'aktif')->get(),
        };

        // Target peran non-PWA (mis. guru) → tidak ada audiens push.
        if ($announcement->target_type === 'peran'
            && ! in_array('siswa', $roles, true)
            && ! in_array('orang-tua', $roles, true)
        ) {
            return collect();
        }

        return $this->usersForStudents($students);
    }

    /**
     * @param  array<int>  $rombelIds
     * @return Collection<int, Student>
     */
    private function studentsInRombels(int $schoolId, array $rombelIds): Collection
    {
        if ($rombelIds === []) {
            return collect();
        }

        return Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->whereHas('rombels', fn ($q) => $q->whereIn('rombels.id', $rombelIds)
                ->where('class_student.status', 'aktif'))
            ->get();
    }

    /**
     * @param  array<string>  $roles
     * @return Collection<int, Student>
     */
    private function studentsForRoles(int $schoolId, array $roles): Collection
    {
        if (! in_array('siswa', $roles, true) && ! in_array('orang-tua', $roles, true)) {
            return collect();
        }

        return Student::where('school_id', $schoolId)->where('status', 'aktif')->get();
    }

    /**
     * @return Collection<int, Student>
     */
    private function rombelStudents(Rombel $rombel): Collection
    {
        return $rombel->students()->wherePivot('status', 'aktif')->get();
    }

    /**
     * Akun-akun yang berhak menerima notif untuk siswa-siswa ini:
     * akun siswa itu sendiri + akun orang tua/wali yang tertaut.
     *
     * @param  Collection<int, Student>  $students
     * @return Collection<int, User>
     */
    private function usersForStudents(Collection $students): Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $studentUsers = User::whereIn('student_id', $students->pluck('id'))->get();

        return $studentUsers->merge($this->guardianUsersForStudents($students))->unique('id');
    }

    /**
     * @param  Collection<int, Student>  $students
     * @return Collection<int, User>
     */
    private function guardianUsersForStudents(Collection $students): Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $guardianIds = Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $students->pluck('id')))
            ->pluck('guardians.id');

        if ($guardianIds->isEmpty()) {
            return collect();
        }

        return User::whereHas('guardians', fn ($q) => $q->whereIn('guardians.id', $guardianIds))->get();
    }

    // ---------- Pengiriman ----------

    /**
     * @param  Collection<int, User>|iterable<User>  $users
     */
    public function notifyUsers(iterable $users, string $title, string $body, ?string $url = null): void
    {
        if (! $this->enabled()) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => '/pwa-assets/icons/icon-192.png',
            'url' => $url ?? route('pwa.home'),
        ];

        $ids = $users instanceof Collection ? $users->pluck('id') : collect($users)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        // Perangkat milik user, bukan tenant: orang tua multi-sekolah tetap
        // menerima notif semua anaknya walau subscribe dari satu konteks.
        // Scope dilepas eksplisit; payload sudah diotorisasi saat resolusi audiens.
        PushSubscription::withoutGlobalScope(SchoolScope::class)
            ->whereIn('user_id', $ids)
            ->chunkById(200, function (Collection $subs) use ($payload): void {
                foreach ($subs as $sub) {
                    SendPushNotification::dispatch($sub->id, $payload);
                }
            });
    }

    /**
     * Kirim satu notifikasi sekarang (dipakai job antrean).
     *
     * @param  array{title:string, body:string, icon:string, url:string}  $payload
     */
    public function deliver(PushSubscription $sub, array $payload): void
    {
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('push.vapid.subject'),
                'publicKey' => $this->publicKey(),
                'privateKey' => $this->privateKey(),
            ],
        ]);

        $subscription = Subscription::create([
            'endpoint' => $sub->endpoint,
            'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
        ]);

        try {
            $report = $webPush->sendOneNotification($subscription, json_encode($payload), ['TTL' => 86400]);
        } catch (\Throwable $e) {
            Log::warning('push_gagal', ['subscription_id' => $sub->id, 'error' => $e->getMessage()]);

            return;
        }

        if ($report->isSuccess()) {
            $sub->update(['terakhir_dipakai_at' => now()]);

            return;
        }

        $status = $report->getResponse()?->getStatusCode();

        if (in_array($status, [404, 410], true)) {
            // Endpoint mati (aplikasi di-uninstall) → hapus agar tak menumpuk.
            $sub->delete();
        } else {
            Log::warning('push_gagal', [
                'subscription_id' => $sub->id,
                'status' => $status,
                'reason' => $report->getReason(),
            ]);
        }
    }
}
