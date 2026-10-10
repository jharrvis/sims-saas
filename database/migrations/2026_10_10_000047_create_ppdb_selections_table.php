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
        Schema::create('ppdb_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_applicant_id')->constrained()->cascadeOnDelete();
            $table->decimal('nilai_tes', 5, 2)->nullable();
            $table->decimal('nilai_wawancara', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->integer('peringkat')->nullable();
            $table->enum('keputusan', ['lulus', 'tidak_lulus', 'cadangan'])->nullable();
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('ppdb_applicant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_selections');
    }
};
