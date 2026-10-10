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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nis', 30);
            $table->string('nisn', 20)->nullable();
            $table->string('nama', 150);
            $table->enum('jk', ['L', 'P']);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('agama', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('foto_path')->nullable();
            $table->enum('status', ['aktif', 'pindah', 'lulus', 'keluar', 'do'])->default('aktif');
            $table->timestamps();
            $table->softDeletes(); // FR-106: histori tetap tersimpan

            $table->unique(['school_id', 'nis']); // NIS unik per sekolah
            $table->index(['school_id', 'status']);
            $table->index('nama'); // FR-104 pencarian cepat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
