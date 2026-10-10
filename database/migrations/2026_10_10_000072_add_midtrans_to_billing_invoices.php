<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom pembayaran Midtrans pada tagihan langganan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table): void {
            $table->string('midtrans_order_id', 80)->nullable()->unique()->after('nomor');
            $table->string('snap_token', 100)->nullable()->after('midtrans_order_id');
            $table->string('payment_url', 500)->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table): void {
            $table->dropColumn(['midtrans_order_id', 'snap_token', 'payment_url']);
        });
    }
};
