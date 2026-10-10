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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nip_nuptk', 30)->nullable();
            $table->string('nama', 150);
            $table->enum('jenis', ['guru_mapel', 'guru_kelas', 'tendik']);
            $table->enum('status_kepegawaian', ['pns', 'pppk', 'honorer', 'kontrak']);
            $table->string('jabatan', 100)->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'jenis']);
            $table->index('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
