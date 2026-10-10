<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 30);
            $table->string('nama', 150);
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
        });

        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('nama', 200);
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->default('baik');
            $table->string('lokasi', 150)->nullable();
            $table->date('tgl_beli')->nullable();
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->string('sumber_dana', 100)->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
            $table->index(['school_id', 'kondisi']);
        });

        Schema::create('asset_mutations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['masuk', 'pindah', 'kondisi', 'keluar']);
            $table->string('dari', 150)->nullable();
            $table->string('ke', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->date('tgl')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_mutations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
