<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengurus kelas per rombel per tahun ajaran: satu jabatan dipegang satu
     * siswa, dan satu siswa memegang paling banyak satu jabatan.
     */
    public function up(): void
    {
        Schema::create('class_officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('jabatan', 20);
            $table->timestamps();

            $table->unique(['rombel_id', 'academic_year_id', 'jabatan'], 'class_officer_role_unique');
            $table->unique(['rombel_id', 'academic_year_id', 'student_id'], 'class_officer_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_officers');
    }
};
