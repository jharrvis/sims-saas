<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pendamping NIK untuk deteksi duplikat (QA B-05).
     *
     * NIK wali tersimpan terenkripsi (ciphertext acak), sehingga tidak
     * bisa diindeks unik maupun dicari. nik_hash menyimpan HMAC-SHA256
     * dari NIK polos dengan kunci aplikasi: deterministik, tidak bisa
     * dibalik, dan aman diindeks unik per sekolah. Baris lama di-backfill
     * dengan membuka enkripsi memakai APP_KEY aktif.
     */
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table): void {
            $table->string('nik_hash', 64)->nullable()->after('nik');
        });

        DB::table('guardians')
            ->whereNotNull('nik')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    try {
                        $plain = Crypt::decryptString($row->nik);
                    } catch (DecryptException) {
                        continue; // Payload tidak terbaca: lewati, hash tetap null.
                    }

                    DB::table('guardians')
                        ->where('id', $row->id)
                        ->update(['nik_hash' => hash_hmac('sha256', $plain, (string) config('app.key'))]);
                }
            });

        Schema::table('guardians', function (Blueprint $table): void {
            $table->unique(['school_id', 'nik_hash']);
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table): void {
            $table->dropUnique(['school_id', 'nik_hash']);
            $table->dropColumn('nik_hash');
        });
    }
};
