<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan akun login guru/pegawai 1:1 ke data kepegawaian.
 *
 * Satu baris Employee boleh punya maksimal satu User (unique), dan akun
 * tetap hidup bila baris Employee dihapus permanen (nullOnDelete) — yang
 * putus hanya tautannya, bukan akses loginnya. Soft delete Employee tidak
 * memutus tautan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('employee_id')->nullable()->unique()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
