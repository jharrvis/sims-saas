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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 20); // '2026/2027'
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->boolean('aktif')->default(false); // satu aktif per sekolah (dijaga di service)
            $table->timestamps();

            $table->unique(['school_id', 'nama']);
            $table->index('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
