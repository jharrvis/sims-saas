<?php

declare(strict_types=1);

namespace App\Domains\Perpus\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API Perpustakaan v1 (modul add-on — di belakang middleware module:perpustakaan).
 */
class PerpusController extends Controller
{
    public function bookIndex(Request $request): JsonResponse
    {
        return response()->json(
            Book::query()
                ->when($request->filled('q'), fn ($q) => $q->where('judul', 'like', '%'.$request->string('q').'%'))
                ->orderBy('judul')
                ->paginate(20)
        );
    }

    public function bookStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'judul' => ['required', 'string', 'max:200'],
            'pengarang' => ['nullable', 'string', 'max:150'],
            'penerbit' => ['nullable', 'string', 'max:150'],
            'tahun_terbit' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'isbn' => ['nullable', 'string', 'max:30'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        return response()->json(Book::create($data), 201);
    }

    public function loanIndex(Request $request): JsonResponse
    {
        return response()->json(
            BookLoan::with(['book:id,judul', 'student:id,nis,nama'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('tgl_pinjam')
                ->paginate(20)
        );
    }

    public function loanStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'durasi_hari' => ['sometimes', 'integer', 'min:1', 'max:60'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        if ($book->tersedia < 1) {
            return response()->json(['message' => 'Stok buku habis dipinjam.', 'code' => 'STOK_HABIS'], 422);
        }

        $durasi = (int) ($data['durasi_hari'] ?? 7);

        $loan = BookLoan::create([
            'book_id' => $book->id,
            'student_id' => $data['student_id'],
            'tgl_pinjam' => now()->toDateString(),
            'tgl_jatuh_tempo' => now()->addDays($durasi)->toDateString(),
            'dipinjamkan_oleh' => $request->user()->id,
        ]);

        return response()->json($loan, 201);
    }

    public function loanReturn(BookLoan $loan): JsonResponse
    {
        $loan->tandaiKembali();

        return response()->json($loan->fresh());
    }
}
