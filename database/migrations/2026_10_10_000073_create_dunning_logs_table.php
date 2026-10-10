<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak pengingat dunning (anti-kirim-ganda) per tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dunning_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // trial_h7, trial_h3, trial_h1, trial_expired, suspended, invoice_overdue, renewal_issued
            $table->string('jenis', 30);
            $table->string('referensi', 80)->nullable(); // mis. nomor invoice
            $table->string('channel', 20)->default('wa');
            $table->timestamp('dikirim_pada');
            $table->timestamps();

            $table->unique(['school_id', 'jenis', 'referensi']);
            $table->index('dikirim_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dunning_logs');
    }
};
