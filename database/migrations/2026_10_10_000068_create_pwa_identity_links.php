<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Akun siswa: tautan 1:1 ke data siswa (untuk login PWA siswa).
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('student_id')->nullable()->after('phone')
                ->constrained('students')->nullOnDelete();
        });

        // Akun orang tua: satu user bisa terhubung ke banyak wali
        // (multi-anak lintas sekolah — Dok 03 §4).
        Schema::create('guardian_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'guardian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_user');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('student_id');
        });
    }
};
