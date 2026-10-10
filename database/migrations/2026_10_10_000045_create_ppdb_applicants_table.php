<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ppdb_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppdb_period_id')->constrained()->cascadeOnDelete();
            $table->string('no_pendaftaran', 30); // nomor unik per sekolah
            $table->string('nama', 150);
            $table->string('nisn', 20)->nullable();
            $table->enum('jk', ['L', 'P']);
            $table->date('tgl_lahir')->nullable();
            $table->string('asal_sekolah', 150)->nullable();
            $table->string('nama_ortu', 150)->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->enum('status', [
                'draft',        // baru isi formulir
                'submitted',    // formulir dikirim
                'verified',     // berkas terverifikasi
                'accepted',     // lulus seleksi
                'rejected',     // tidak lulus
                'enrolled',     // daftar ulang → jadi siswa
            ])->default('draft');
            $table->text('catatan')->nullable();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete(); // hasil daftar ulang
            $table->timestamps();

            $table->unique(['school_id', 'no_pendaftaran']);
            $table->index(['school_id', 'ppdb_period_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_applicants');
    }
};
