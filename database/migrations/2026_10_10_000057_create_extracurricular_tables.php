<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurriculars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('jadwal', 100)->nullable(); // cth. "Senin 15.30–17.00"
            $table->foreignId('pembina_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });

        Schema::create('extracurricular_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal_gabung')->nullable();
            $table->enum('status', ['aktif', 'keluar'])->default('aktif');
            $table->timestamps();

            $table->unique(['school_id', 'extracurricular_id', 'student_id', 'status'], 'ex_members_unique');
        });

        Schema::create('achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 200);
            $table->enum('kategori', ['akademik', 'non_akademik'])->default('non_akademik');
            $table->enum('tingkat', ['sekolah', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional'])->default('sekolah');
            $table->date('tanggal')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('extracurricular_members');
        Schema::dropIfExists('extracurriculars');
    }
};
