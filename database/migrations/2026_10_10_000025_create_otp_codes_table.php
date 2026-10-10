<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTP verifikasi WA (FR-204, FR-1306; Dok 09 §3.1).
 * Pra-login: TIDAK memakai school_id (pengecualian §6.2 Dok 02).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('code_hash', 255); // kode disimpan sbg hash, bukan plaintext
            $table->string('keperluan', 50); // 'login', 'aktivasi_pwa', 'reset_password'
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0); // batasi brute force
            $table->timestamps();

            $table->index('phone');
            $table->index('expires_at'); // utk purge kode kedaluwarsa via scheduler
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
