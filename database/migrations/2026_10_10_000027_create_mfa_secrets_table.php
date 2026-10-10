<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MFA TOTP — wajib untuk peran platform (FR-904, Dok 09).
 * Secret disimpan terenkripsi; recovery codes di-hash.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mfa_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('secret'); // terenkripsi via cast 'encrypted'
            $table->timestamp('confirmed_at')->nullable(); // null = setup belum selesai
            $table->json('recovery_codes')->nullable(); // hash, sekali pakai
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_secrets');
    }
};
