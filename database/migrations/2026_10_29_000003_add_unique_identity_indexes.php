<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tegakkan keunikan identitas di level database (QA B-07/B-10).
     *
     * Sebelumnya NISN siswa serta NIP/NIK/email pegawai hanya unik (atau
     * bahkan tidak) di validasi aplikasi, sehingga jalur lain (API, impor,
     * edit bersamaan) bisa menulis duplikat. Kolom-kolom ini nullable dan
     * MySQL/SQLite memperbolehkan banyak NULL pada indeks unik, jadi hanya
     * nilai terisi yang wajib unik per sekolah.
     *
     * CATATAN DEPLOY: bila data lama sudah mengandung duplikat, pembuatan
     * indeks akan gagal — periksa duplikat dulu sebelum migrasi dijalankan.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->unique(['school_id', 'nisn']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->unique(['school_id', 'nip_nuptk']);
            $table->unique(['school_id', 'nik']);
            $table->unique(['school_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropUnique(['school_id', 'nisn']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropUnique(['school_id', 'nip_nuptk']);
            $table->dropUnique(['school_id', 'nik']);
            $table->dropUnique(['school_id', 'email']);
        });
    }
};
