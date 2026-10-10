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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nik', 25)->nullable(); // cast 'encrypted' di model
            $table->string('nama', 150);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
            $table->string('pekerjaan', 100)->nullable();
            $table->string('no_wa', 20); // utk OTP & notifikasi (FR-204, FR-606)
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'no_wa']);
            $table->index('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
