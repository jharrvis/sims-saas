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
        // Level PLATFORM: tanpa school_id — katalog tunggal untuk semua tenant
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique(); // 'keuangan', 'ppdb', 'e_rapor', ...
            $table->string('nama', 100);          // 'Keuangan & SPP'
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['inti', 'addon'])->default('addon');
            $table->json('termasuk_paket')->nullable(); // slug paket yg sudah mencakup modul ini
            $table->unsignedBigInteger('harga_addon_bulanan')->nullable();
            $table->json('prasyarat')->nullable(); // kode modul yg wajib aktif dulu
            $table->string('versi', 20)->default('1.0.0');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
