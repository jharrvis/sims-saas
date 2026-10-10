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
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->restrictOnDelete();
            $table->boolean('aktif')->default(true); // feature flag per tenant
            $table->json('konfigurasi')->nullable();
            $table->timestamp('diaktifkan_pada')->useCurrent();
            $table->timestamp('dinonaktifkan_pada')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'module_id']);
            $table->index(['school_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
