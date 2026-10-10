<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('pesan');
            $table->enum('channel', ['whatsapp', 'notifikasi']);
            $table->enum('target_type', ['semua', 'siswa', 'guru', 'rombel'])->default('semua');
            $table->json('target_data')->nullable(); // id rombel / daftar penerima
            $table->dateTime('dijadwalkan_pada')->nullable();
            $table->enum('status', ['draf', 'terjadwal', 'terkirim', 'gagal'])->default('draf');
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('broadcast_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broadcast_id')->constrained()->cascadeOnDelete();
            $table->string('penerima', 200); // nama / nomor
            $table->enum('status', ['terkirim', 'gagal'])->default('terkirim');
            $table->string('error', 500)->nullable();
            $table->dateTime('dikirim_pada')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'broadcast_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_logs');
        Schema::dropIfExists('broadcasts');
    }
};
