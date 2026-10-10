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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 20); // mis. 'MTK', 'BIN'
            $table->string('nama', 100);
            $table->enum('kelompok', ['wajib', 'peminatan', 'mulok']);
            $table->decimal('kkm_default', 5, 2)->default(75);
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
