<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('isi');
            $table->enum('target_type', ['semua', 'peran', 'kelas'])->default('semua');
            $table->json('target_data')->nullable(); // {roles:[...]} atau {rombel_ids:[...]}
            $table->boolean('penting')->default(false);
            $table->enum('status', ['draf', 'tayang'])->default('draf');
            $table->date('tayang_dari');
            $table->date('tayang_sampai')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'tayang_dari']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
