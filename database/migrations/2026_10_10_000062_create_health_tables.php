<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->dateTime('tgl');
            $table->text('keluhan');
            $table->string('diagnosa', 200)->nullable();
            $table->text('tindakan')->nullable();
            $table->decimal('suhu', 4, 1)->nullable();
            $table->string('tekanan_darah', 20)->nullable();
            $table->enum('rujukan', ['tidak', 'puskesmas', 'rumah_sakit'])->default('tidak');
            $table->foreignId('ditangani_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
        });

        Schema::create('medicines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('satuan', 30)->default('tablet');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->timestamps();

            $table->index('school_id');
        });

        Schema::create('medicine_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('jumlah');
            $table->date('tgl')->nullable();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_usages');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('health_visits');
    }
};
