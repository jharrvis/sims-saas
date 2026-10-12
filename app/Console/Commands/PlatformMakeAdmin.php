<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Buat/upgrade akun operator platform: set is_platform + beri peran
 * platform-admin (guard `platform`) berisi seluruh permission platform.*.
 */
class PlatformMakeAdmin extends Command
{
    protected $signature = 'platform:make-admin
        {phone : Nomor WhatsApp akun (format 62...) }
        {--name= : Nama operator}';

    protected $description = 'Jadikan akun sebagai operator platform SaaS';

    public function handle(): int
    {
        $phone = (string) $this->argument('phone');
        $name = (string) ($this->option('name') ?: 'Operator Platform');

        // Sentinel team 0 = konteks platform (tanpa tenant); pivot Spatie
        // teams mewajibkan team_id non-null.
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $user = User::where('phone', $phone)->first();

        if ($user === null) {
            $user = User::create([
                'username' => 'platform-'.$phone,
                'name' => $name,
                'phone' => $phone,
                'is_platform' => true,
            ]);
            $this->info("Akun baru dibuat: {$name} ({$phone})");
        } else {
            $user->update(['is_platform' => true, 'name' => $name]);
            $this->info("Akun {$phone} di-upgrade menjadi operator platform.");
        }

        $role = Role::findOrCreate('platform-admin', 'platform');
        $role->syncPermissions(Permission::where('guard_name', 'platform')->get());

        $user->assignRole($role);

        $this->info('Peran platform-admin diberikan. Login via WA OTP seperti biasa, lalu buka /platform.');

        return self::SUCCESS;
    }
}
