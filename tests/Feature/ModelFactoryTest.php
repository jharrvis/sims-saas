<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\GuardianIndex;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Guardian;
use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

it('factory membuat sekolah, siswa, wali, dan pegawai yang valid (QA B-20)', function (): void {
    $school = School::factory()->create();
    expect($school->exists)->toBeTrue();

    $student = Student::factory()->create(['school_id' => $school->id]);
    expect($student->school_id)->toBe($school->id)
        ->and($student->status)->toBe('aktif');

    $guardian = Guardian::factory()->create(['school_id' => $school->id]);
    expect($guardian->school_id)->toBe($school->id);

    $employee = Employee::factory()->create(['school_id' => $school->id]);
    expect($employee->school_id)->toBe($school->id)
        ->and($employee->status_aktif)->toBeTrue();

    expect(Student::factory()->count(3)->make())->toHaveCount(3);
});

it('aksi hapus wali tercatat di audit log (QA B-20)', function (): void {
    $school = School::factory()->create();

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($school->id);
    app(TenantContext::class)->set($school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

    $admin = User::create([
        'username' => 'auditadmin-'.uniqid(), 'name' => 'Admin Audit',
        'phone' => '62857'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $admin->id, 'school_id' => $school->id, 'status' => 'aktif']);
    $admin->assignRole('admin-sekolah');

    $guardian = Guardian::factory()->create(['school_id' => $school->id, 'nama' => 'Wali Audit']);

    Livewire::actingAs($admin)
        ->test(GuardianIndex::class)
        ->call('delete', $guardian->id);

    $log = AuditLog::where('aksi', 'wali.delete')
        ->where('model_type', Guardian::class)
        ->where('model_id', (string) $guardian->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->school_id)->toBe($school->id)
        ->and($log->before['nama'])->toBe('Wali Audit');

    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});
