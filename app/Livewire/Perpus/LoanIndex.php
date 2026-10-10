<?php

declare(strict_types=1);

namespace App\Livewire\Perpus;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Peminjaman & pengembalian buku.
 */
class LoanIndex extends Component
{
    use WithPagination;

    public string $status = 'dipinjam';

    public ?int $book_id = null;

    public ?int $student_id = null;

    public string $searchSiswa = '';

    public string $searchBuku = '';

    public int $durasiHari = 7;

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function pinjam(): void
    {
        $this->authorize('perpus.pinjam');

        $this->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'durasiHari' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $book = Book::findOrFail($this->book_id);

        if ($book->tersedia < 1) {
            $this->addError('book_id', 'Stok buku habis dipinjam.');

            return;
        }

        BookLoan::create([
            'book_id' => $book->id,
            'student_id' => $this->student_id,
            'tgl_pinjam' => now()->toDateString(),
            'tgl_jatuh_tempo' => now()->addDays($this->durasiHari)->toDateString(),
            'dipinjamkan_oleh' => auth()->id(),
        ]);

        $this->reset(['book_id', 'student_id', 'searchSiswa', 'searchBuku']);
        session()->flash('ok', 'Peminjaman dicatat.');
    }

    public function kembali(BookLoan $loan): void
    {
        $this->authorize('perpus.kembali');

        $loan->tandaiKembali();

        $pesan = $loan->denda > 0
            ? 'Buku kembali terlambat. Denda Rp '.number_format($loan->denda, 0, ',', '.').'.'
            : 'Buku telah kembali.';

        session()->flash('ok', $pesan);
    }

    public function render(): View
    {
        $loans = BookLoan::with(['book:id,judul', 'student:id,nis,nama'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('tgl_pinjam')
            ->paginate(15);

        $bukuList = Book::when($this->searchBuku !== '', fn ($q) => $q->where('judul', 'like', '%'.$this->searchBuku.'%'))
            ->orderBy('judul')->limit(10)->get(['id', 'judul']);

        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.perpus.loan-index', [
            'loans' => $loans,
            'bukuList' => $bukuList,
            'siswaList' => $siswaList,
        ])->layout('layouts.app', ['title' => 'Peminjaman Buku']);
    }
}
