<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->text('alamat')->nullable()->after('jenjang');
            $table->string('telepon', 30)->nullable()->after('alamat');
            $table->string('email', 100)->nullable()->after('telepon');
            $table->string('logo_path', 255)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn(['alamat', 'telepon', 'email', 'logo_path']);
        });
    }
};
