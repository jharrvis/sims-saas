<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel billing & langganan level platform/tenant (Dok 02 §8).
 *
 * Deviasi dari Dok 02: tabel tagihan langganan bernama `billing_invoices`
 * (bukan `invoices`) karena nama `invoices` sudah dipakai tagihan SPP
 * sekolah (modul Keuangan).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Level PLATFORM: tanpa school_id
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 50);
            $table->string('slug', 50)->unique();
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('harga_bulanan');
            $table->unsignedBigInteger('harga_tahunan')->nullable();
            $table->unsignedInteger('max_siswa')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->unsignedSmallInteger('max_user_staf')->nullable();
            $table->json('fitur')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Level TENANT
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])->default('trial');
            $table->enum('periode', ['bulanan', 'tahunan'])->default('bulanan');
            $table->timestamp('trial_berakhir_pada')->nullable();
            $table->timestamp('periode_berakhir_pada')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index('periode_berakhir_pada');
        });

        Schema::create('subscription_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deskripsi', 200);
            $table->unsignedBigInteger('harga');
            $table->timestamps();
        });

        Schema::create('billing_invoices', function (Blueprint $table): void {
            $table->id();
            // nullOnDelete: arsip fiskal wajib selamat dari purge tenant
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_sekolah_snapshot', 150);
            $table->string('npsn_snapshot', 20)->nullable();
            $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
            $table->string('nomor', 50)->unique();
            $table->date('periode_dari');
            $table->date('periode_sampai');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('pajak')->default(0);
            $table->unsignedBigInteger('total');
            $table->enum('status', ['draft', 'issued', 'paid', 'overdue', 'void'])->default('draft');
            $table->date('jatuh_tempo');
            $table->timestamp('dibayar_pada')->nullable();
            $table->string('metode_bayar', 50)->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index('jatuh_tempo');
        });

        Schema::create('tenant_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('metrik', 50);
            $table->unsignedBigInteger('nilai')->default(0);
            $table->string('periode', 7);
            $table->timestamps();

            $table->unique(['school_id', 'metrik', 'periode']);
        });

        Schema::create('tenant_quota_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('metrik', 50);
            $table->unsignedBigInteger('batas_custom')->nullable();
            $table->text('alasan');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('berlaku_sampai')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'metrik']);
            $table->index('berlaku_sampai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_quota_overrides');
        Schema::dropIfExists('tenant_usages');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
