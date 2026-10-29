<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Models\Guardian;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah wali murid (FR-302).
 *
 * NIK tersimpan terenkripsi (cast 'encrypted' di model) dan tidak bisa
 * dicari lewat kolomnya; pencarian daftar memakai nama dan nomor WA.
 */
class GuardianForm extends Component
{
    public ?Guardian $guardian = null;

    public string $nik = '';

    public string $nama = '';

    public string $hubungan = 'ayah';

    public string $pekerjaan = '';

    public string $no_wa = '';

    public string $email = '';

    public string $alamat = '';

    public function mount(?Guardian $guardian = null): void
    {
        if ($guardian?->exists) {
            $this->guardian = $guardian;
            $this->nik = $guardian->nik ?? '';
            $this->nama = $guardian->nama;
            $this->hubungan = $guardian->hubungan;
            $this->pekerjaan = $guardian->pekerjaan ?? '';
            $this->no_wa = $guardian->no_wa;
            $this->email = $guardian->email ?? '';
            $this->alamat = $guardian->alamat ?? '';
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        return [
            // NIK Indonesia selalu 16 digit (QA B-05).
            'nik' => ['nullable', 'string', 'digits:16'],
            'nama' => ['required', 'string', 'max:150'],
            'hubungan' => ['required', Rule::in(['ayah', 'ibu', 'wali'])],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            // Nomor WA dipakai untuk OTP: wajib format internasional 62xx (QA B-06).
            'no_wa' => ['required', 'string', 'max:20', 'regex:/^62[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string,string>
     */
    protected function messages(): array
    {
        return [
            'no_wa.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
        ];
    }

    public function save(): void
    {
        // Authorization berjalan sebelum validasi: route middleware hanya
        // melindungi GET, bukan aksi Livewire.
        $this->authorize($this->guardian?->exists ? 'wali-murid.update' : 'wali-murid.create');

        // Samakan perilaku dengan EmployeeAccountForm: 08xx diterima dan
        // dinormalisasi ke 62xx sebelum validasi format (QA B-06).
        $this->no_wa = $this->normalisasiTelepon($this->no_wa);

        $this->validate();

        // Duplikat NIK hanya bisa dideteksi lewat nik_hash karena kolom
        // nik terenkripsi (QA B-05). SchoolScope membatasi ke sekolah ini.
        if ($this->nik !== '') {
            $duplikat = Guardian::query()
                ->where('nik_hash', Guardian::hashNik($this->nik))
                ->when($this->guardian?->exists, fn ($q) => $q->whereKeyNot($this->guardian->id))
                ->exists();

            if ($duplikat) {
                $this->addError('nik', 'NIK ini sudah terdaftar untuk wali lain di sekolah ini.');

                return;
            }
        }

        $data = [
            'nik' => $this->nik ?: null,
            'nama' => $this->nama,
            'hubungan' => $this->hubungan,
            'pekerjaan' => $this->pekerjaan ?: null,
            'no_wa' => $this->no_wa,
            'email' => $this->email ?: null,
            'alamat' => $this->alamat ?: null,
        ];

        if ($this->guardian?->exists) {
            $this->guardian->update($data);
        } else {
            Guardian::create($data);
        }

        $this->redirect(route('wali.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kesiswaan.guardian-form')
            ->layout('layouts.app', ['title' => $this->guardian?->exists ? 'Ubah Wali' : 'Tambah Wali']);
    }

    private function normalisasiTelepon(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
