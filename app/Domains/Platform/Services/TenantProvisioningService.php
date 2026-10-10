<?php

declare(strict_types=1);

namespace App\Domains\Platform\Services;

use App\Models\Membership;
use App\Models\Module;
use App\Models\Plan;
use App\Models\School;
use App\Models\TenantModule;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisioning tenant baru: sekolah + langganan trial + admin + peran + modul inti.
 * Dipakai onboarding mandiri (`/daftar`) dan dapat dipakai ulang panel platform.
 */
class TenantProvisioningService
{
    public function __construct(
        private readonly PlatformService $platform,
    ) {}

    /**
     * @param  array{nama:string, jenjang:string, npsn?:?string, admin_nama:string, admin_phone:string, plan_slug?:string}  $data
     */
    public function provision(array $data): School
    {
        return DB::transaction(function () use ($data): School {
            $school = School::create([
                'nama' => $data['nama'],
                'slug' => $this->uniqueSlug($data['nama']),
                'npsn' => $data['npsn'] ?? null,
                'jenjang' => $data['jenjang'],
                'status' => 'aktif',
                'status_langganan' => 'trialing',
            ]);

            // Peran & permission per tenant (Spatie teams).
            RoleSeeder::seedForSchool($school->id);

            // Admin sekolah: identitas global + membership.
            $user = User::firstOrCreate(
                ['phone' => $data['admin_phone']],
                [
                    'username' => 'admin-'.Str::lower(Str::random(8)),
                    'name' => $data['admin_nama'],
                    'is_platform' => false,
                ]
            );

            Membership::firstOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                ['status' => 'aktif']
            );

            app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
            $user->assignRole(Role::findByName('superadmin', 'web'));

            // Modul inti aktif otomatis; add-on tetap nonaktif.
            $inti = Module::where('kategori', 'inti')->where('aktif', true)->get();

            foreach ($inti as $module) {
                TenantModule::create([
                    'school_id' => $school->id,
                    'module_id' => $module->id,
                    'aktif' => true,
                    'diaktifkan_pada' => now(),
                ]);
            }

            // Langganan trial 30 hari (default paket Basic).
            $plan = Plan::where('slug', $data['plan_slug'] ?? 'basic')->firstOrFail();
            $this->platform->createSubscription($school, $plan, 'bulanan', $user, true);

            return $school;
        });
    }

    private function uniqueSlug(string $nama): string
    {
        $base = Str::slug($nama);
        $slug = $base;
        $i = 2;

        while (School::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
