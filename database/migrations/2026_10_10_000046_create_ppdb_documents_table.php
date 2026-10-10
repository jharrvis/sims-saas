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
        Schema::create('ppdb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_applicant_id')->constrained()->cascadeOnDelete();
            $table->string('jenis', 50); // kk, akta, ijazah, foto, dll
            $table->string('file_path');
            $table->string('nama_asli', 255)->nullable();
            $table->boolean('terverifikasi')->default(false);
            $table->timestamps();

            $table->index('ppdb_applicant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
    }
};
