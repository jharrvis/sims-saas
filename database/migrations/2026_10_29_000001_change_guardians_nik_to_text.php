<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NIK disimpan terenkripsi (cast 'encrypted' di Guardian) dengan
        // payload sekitar 200 karakter, sementara kolom aslinya VARCHAR(25):
        // setiap insert wali ber-NIK gagal 1406 di MySQL (2026-10-29).
        // SQLite mengabaikan batas VARCHAR, jadi di sana tidak ada yang diubah.
        // Raw statement dipakai karena doctrine/dbal tidak terpasang, jadi
        // ->change() tidak tersedia (lihat juga 2026_10_10_000036).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE guardians MODIFY nik TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PERINGATAN: rollback setelah ada baris ber-NIK akan memotong nilai
        // terenkripsi (payload sekitar 200 karakter) ke 25 karakter dan
        // merusaknya permanen. Jangan rollback bila sudah ada NIK tersimpan.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE guardians MODIFY nik VARCHAR(25) NULL');
        }
    }
};
