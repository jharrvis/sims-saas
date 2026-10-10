<?php

declare(strict_types=1);

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Identity\Services\OtpService;
use App\Models\Membership;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;

function buatSekolahTs(string $nama, string $slug): School
{
    return School::create([
        'nama' => $nama, 'slug' => $slug, 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
}

it('user hanya bisa switch ke tenant tempat ia anggota aktif', function (): void {
    $a = buatSekolahTs('Sekolah A', 'ts-a');
    $b = buatSekolahTs('Sekolah B', 'ts-b');

    $user = User::create([
        'username' => 'switcher', 'name' => 'Switcher', 'phone' => '628990000001',
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $a->id, 'status' => 'aktif']);

    // Switch ke A (anggota) → sukses
    $res = $this->actingAs($user)->postJson('/api/v1/auth/tenant/switch', ['school_id' => $a->id]);
    $res->assertOk();
    expect(app(TenantContext::class)->schoolId())->toBe($a->id);

    // Switch ke B (bukan anggota) → 403
    $res = $this->actingAs($user)->postJson('/api/v1/auth/tenant/switch', ['school_id' => $b->id]);
    $res->assertForbidden();
});

it('login OTP menerbitkan token dengan satu tenant aktif', function (): void {
    $a = buatSekolahTs('Sekolah A', 'ts-a2');

    $user = User::create([
        'username' => 'otplogin', 'name' => 'OTP Login', 'phone' => '628990000002',
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $a->id, 'status' => 'aktif']);

    $this->app->bind(WaSender::class, FakeWaSender::class);
    FakeWaSender::$lastCode = null;

    $otp = app(OtpService::class);
    $otp->request('08990000002', 'login', 'Sekolah A');
    $code = FakeWaSender::$lastCode;

    $res = $this->postJson('/api/v1/auth/otp/verify', [
        'phone' => '08990000002',
        'code' => $code,
    ]);

    $res->assertOk()
        ->assertJsonPath('school.id', $a->id)
        ->assertJsonStructure(['token', 'user', 'school', 'memberships']);
});
