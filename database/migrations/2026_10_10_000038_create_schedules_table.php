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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->unsignedTinyInteger('jam_ke');
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('ruang', 30)->nullable();
            $table->timestamps();

            // FR-502 (lapis DB): cegah satu rombel punya 2 mapel di jam yang sama
            $table->unique(['rombel_id', 'hari', 'jam_ke', 'academic_year_id'], 'schedules_rombel_unique');
            $table->index(['school_id', 'hari']);
            $table->index(['employee_id', 'hari', 'jam_ke']); // bantu deteksi bentrok guru
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
