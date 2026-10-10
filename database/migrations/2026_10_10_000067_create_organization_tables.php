<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->enum('jenis', ['osis', 'mpk', 'pramuka', 'paskibra', 'pmr', 'rohis', 'lainnya'])->default('lainnya');
            $table->foreignId('pembina_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'aktif']);
        });

        Schema::create('organization_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('jabatan', 100)->nullable();
            $table->string('periode', 20)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->unique(['school_id', 'organization_id', 'student_id', 'status'], 'org_member_unique');
        });

        Schema::create('organization_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 200);
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['rencana', 'berjalan', 'selesai', 'batal'])->default('rencana');
            $table->timestamps();

            $table->index(['school_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_activities');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
    }
};
