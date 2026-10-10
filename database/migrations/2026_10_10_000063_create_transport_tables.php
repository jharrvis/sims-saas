<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('sopir', 150)->nullable();
            $table->string('no_kendaraan', 30)->nullable();
            $table->integer('kapasitas')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });

        Schema::create('transport_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->integer('urutan')->default(0);
            $table->time('jam_berangkat')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'transport_route_id']);
        });

        Schema::create('transport_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_stop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['aktif', 'berhenti'])->default('aktif');
            $table->timestamps();

            $table->unique(['school_id', 'transport_route_id', 'student_id', 'status'], 'transport_member_unique');
            $table->index(['school_id', 'transport_route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_members');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
    }
};
