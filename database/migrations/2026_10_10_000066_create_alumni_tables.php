<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama', 150);
            $table->string('tahun_lulus', 9)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'tahun_lulus']);
        });

        Schema::create('alumni_careers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->enum('status', ['kuliah', 'kerja', 'wirausaha', 'lainnya'])->default('lainnya');
            $table->string('institusi', 200)->nullable();
            $table->string('jurusan_posisi', 200)->nullable();
            $table->string('tahun_mulai', 9)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'alumni_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_careers');
        Schema::dropIfExists('alumni');
    }
};
