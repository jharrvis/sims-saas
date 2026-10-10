<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('kategori', 100)->nullable();
            $table->decimal('harga', 15, 2);
            $table->integer('stok')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });

        Schema::create('canteen_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 50);
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total', 15, 2);
            $table->decimal('bayar', 15, 2);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('tgl');
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
            $table->index(['school_id', 'tgl']);
        });

        Schema::create('canteen_transaction_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('canteen_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('canteen_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama', 150);
            $table->decimal('harga', 15, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_transaction_items');
        Schema::dropIfExists('canteen_transactions');
        Schema::dropIfExists('canteen_products');
    }
};
