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
        Schema::create('rombels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('tingkat', 10); // 'XII', '12', '6'
            $table->string('nama', 50); // 'XII IPA 1'
            $table->unsignedInteger('kapasitas')->default(36);
            // FR-303: satu wali kelas aktif per rombel
            $table->foreignId('wali_kelas_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // arsip rombel tahun lama

            $table->unique(['school_id', 'academic_year_id', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombels');
    }
};
