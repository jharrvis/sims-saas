<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', ['individu', 'kelompok', 'klasikal'])->default('individu');
            $table->string('topik', 200);
            $table->text('catatan')->nullable(); // rahasia: hanya guru BK + admin + kepsek
            $table->string('tindak_lanjut', 200)->nullable();
            $table->enum('status', ['terjadwal', 'selesai', 'batal'])->default('terjadwal');
            $table->timestamps();

            $table->index(['school_id', 'tanggal']);
            $table->index(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};
