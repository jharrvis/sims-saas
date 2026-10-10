<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('judul', 200);
            $table->string('pengarang', 150)->nullable();
            $table->string('penerbit', 150)->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('isbn', 30)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->unsignedSmallInteger('jumlah')->default(1);
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
            $table->index(['school_id', 'judul']);
        });

        Schema::create('book_loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('tgl_pinjam');
            $table->date('tgl_jatuh_tempo');
            $table->date('tgl_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'kembali', 'terlambat'])->default('dipinjam');
            $table->unsignedInteger('denda')->default(0);
            $table->foreignId('dipinjamkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('books');
    }
};
