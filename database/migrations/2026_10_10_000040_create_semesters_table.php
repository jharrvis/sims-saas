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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete(); // denormalisasi utk tenant scope
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('nama', ['ganjil', 'genap']);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->boolean('aktif')->default(false);
            $table->timestamps();

            $table->unique(['academic_year_id', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
