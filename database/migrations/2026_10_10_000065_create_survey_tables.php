<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->enum('status', ['draf', 'aktif', 'tutup'])->default('draf');
            $table->enum('target_type', ['semua', 'siswa', 'guru', 'orang_tua'])->default('semua');
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('survey_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->enum('tipe', ['pilihan_ganda', 'skala', 'teks'])->default('pilihan_ganda');
            $table->json('opsi')->nullable(); // daftar pilihan / rentang skala
            $table->integer('urutan')->default(0);
            $table->boolean('wajib')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'survey_id']);
        });

        Schema::create('survey_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('tgl');
            $table->timestamps();

            $table->index(['school_id', 'survey_id']);
        });

        Schema::create('survey_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->text('jawaban');
            $table->timestamps();

            $table->unique(['school_id', 'survey_response_id', 'survey_question_id'], 'survey_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
