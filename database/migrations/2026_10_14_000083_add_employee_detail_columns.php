<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data guru paket A (identitas & kontak), B (pendidikan), C (kepegawaian).
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            // A — identitas & kontak
            $table->string('nik', 20)->nullable()->after('nip_nuptk');
            $table->string('tempat_lahir')->nullable()->after('nama');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            $table->string('jk', 1)->nullable()->after('tgl_lahir');
            $table->string('agama', 30)->nullable()->after('jk');
            $table->text('alamat')->nullable()->after('agama');
            $table->string('email', 150)->nullable()->after('alamat');

            // B — pendidikan
            $table->string('pendidikan_terakhir', 10)->nullable()->after('status_kepegawaian');
            $table->string('jurusan', 100)->nullable()->after('pendidikan_terakhir');
            $table->string('perguruan_tinggi', 150)->nullable()->after('jurusan');
            $table->integer('tahun_lulus')->nullable()->after('perguruan_tinggi');

            // C — kepegawaian
            $table->date('tmt')->nullable()->after('tahun_lulus');
            $table->string('golongan', 10)->nullable()->after('tmt');
            $table->string('sertifikasi', 10)->nullable()->after('golongan');
            $table->string('no_sertifikat', 50)->nullable()->after('sertifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'nik', 'tempat_lahir', 'tgl_lahir', 'jk', 'agama', 'alamat', 'email',
                'pendidikan_terakhir', 'jurusan', 'perguruan_tinggi', 'tahun_lulus',
                'tmt', 'golongan', 'sertifikasi', 'no_sertifikat',
            ]);
        });
    }
};
