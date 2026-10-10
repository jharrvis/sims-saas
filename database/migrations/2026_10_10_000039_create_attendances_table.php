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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['H', 'I', 'S', 'A']); // Hadir, Izin, Sakit, Alpa
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable(); // FR-604: kunci absensi
            $table->timestamps();

            $table->unique(['student_id', 'tanggal']); // satu baris absensi harian per siswa
            $table->index(['school_id', 'tanggal']);
            $table->index(['rombel_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
