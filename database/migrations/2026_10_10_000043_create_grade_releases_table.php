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
        Schema::create('grade_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->timestamp('dirilis_pada');
            $table->foreignId('dirilis_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Nilai tampil di PWA hanya jika baris ini ada (FR-707)
            $table->unique(['semester_id', 'rombel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_releases');
    }
};
