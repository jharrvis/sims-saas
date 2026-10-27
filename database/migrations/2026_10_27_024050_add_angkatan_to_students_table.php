<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom angkatan (tahun masuk) siswa untuk papan manajemen kelas:
     * filter dan sortir calon siswa berdasarkan kohort. Nullable agar siswa
     * lama tanpa data angkatan tetap valid; backfill mengisi dari tahun
     * ajaran penempatan paling awal (perkiraan untuk siswa mutasi).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'angkatan')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedSmallInteger('angkatan')->nullable()->after('nisn');
            });
        }

        // Backfill idempotent: hanya mengisi baris yang masih null.
        $placements = DB::table('class_student')
            ->join('academic_years', 'academic_years.id', '=', 'class_student.academic_year_id')
            ->select('class_student.student_id', 'academic_years.tgl_mulai')
            ->orderBy('academic_years.tgl_mulai')
            ->get();

        $seen = [];
        foreach ($placements as $placement) {
            if (isset($seen[$placement->student_id]) || $placement->tgl_mulai === null) {
                continue;
            }
            $seen[$placement->student_id] = true;

            DB::table('students')
                ->where('id', $placement->student_id)
                ->whereNull('angkatan')
                ->update(['angkatan' => (int) Carbon::parse($placement->tgl_mulai)->format('Y')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('angkatan');
        });
    }
};
