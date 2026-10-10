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
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 100); // 'SPP', 'Uang Gedung'
            $table->decimal('nominal', 12, 2);
            $table->enum('periode', ['bulanan', 'semesteran', 'tahunan', 'sekali']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
