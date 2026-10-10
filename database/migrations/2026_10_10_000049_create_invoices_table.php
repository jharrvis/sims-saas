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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_type_id')->constrained()->restrictOnDelete();
            $table->string('no_invoice', 40);
            $table->string('periode_label', 30); // '2026-07', '2026/2027 Ganjil'
            $table->decimal('nominal', 12, 2);
            $table->decimal('dibayar', 12, 2)->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->timestamps();

            $table->unique(['school_id', 'no_invoice']);
            $table->unique(['student_id', 'fee_type_id', 'periode_label'], 'invoices_student_fee_period_unique');
            $table->index(['school_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
