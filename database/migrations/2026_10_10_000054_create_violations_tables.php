<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->enum('kategori', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->unsignedSmallInteger('poin')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });

        Schema::create('violations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('violation_type_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->string('tindak_lanjut', 200)->nullable();
            $table->foreignId('dilaporkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
        Schema::dropIfExists('violation_types');
    }
};
