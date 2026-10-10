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
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 255)->unique();
            $table->enum('tipe', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('terverifikasi')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['domain', 'terverifikasi', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
    }
};
