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
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // null = komponen default berlaku utk semua mapel di sekolah tsb.
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('nama', 50); // Tugas, UH, PTS, PAS
            $table->decimal('bobot', 5, 2); // persen, mis. 20.00
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_components');
    }
};
