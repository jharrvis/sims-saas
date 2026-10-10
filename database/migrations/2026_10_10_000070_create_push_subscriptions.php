<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Langganan Web Push per perangkat (Dok 03 §5.3/5.4, FR-1304).
        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint', 500);
            $table->string('p256dh', 255);
            $table->string('auth', 255);
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('terakhir_dipakai_at')->nullable();
            $table->timestamps();

            $table->unique('endpoint');
            $table->index(['school_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
