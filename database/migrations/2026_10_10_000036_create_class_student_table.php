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
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['aktif', 'pindah'])->default('aktif');
            $table->timestamps();

            // FR-102: satu siswa = satu rombel AKTIF per tahun ajaran; riwayat pindah tersimpan.
            // Partial unique index (hanya status='aktif') agar baris 'pindah' boleh ganda.
            $table->index(['rombel_id', 'status']);
        });

        // Partial unique index tidak didukung $table->unique(); pakai raw.
        DB::statement('CREATE UNIQUE INDEX class_student_active_unique ON class_student (student_id, academic_year_id) WHERE status = \'aktif\'');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};
