<?php

declare(strict_types=1);

namespace App\Livewire\Perpus;

use App\Models\Book;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah buku.
 */
class BookForm extends Component
{
    public ?Book $book = null;

    public string $kode = '';

    public string $judul = '';

    public string $pengarang = '';

    public string $penerbit = '';

    public string $tahun_terbit = '';

    public string $isbn = '';

    public string $kategori = '';

    public int $jumlah = 1;

    public function mount(?Book $book = null): void
    {
        $this->authorize('perpus.buku');

        if ($book?->exists) {
            $this->book = $book;
            $this->kode = $book->kode;
            $this->judul = $book->judul;
            $this->pengarang = $book->pengarang ?? '';
            $this->penerbit = $book->penerbit ?? '';
            $this->tahun_terbit = (string) ($book->tahun_terbit ?? '');
            $this->isbn = $book->isbn ?? '';
            $this->kategori = $book->kategori ?? '';
            $this->jumlah = $book->jumlah;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'kode' => ['required', 'string', 'max:50'],
            'judul' => ['required', 'string', 'max:200'],
            'pengarang' => ['nullable', 'string', 'max:150'],
            'penerbit' => ['nullable', 'string', 'max:150'],
            'tahun_terbit' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'isbn' => ['nullable', 'string', 'max:30'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $data['tahun_terbit'] = $data['tahun_terbit'] ?: null;

        if ($this->book?->exists) {
            $this->book->update($data);
            session()->flash('ok', 'Data buku diperbarui.');
        } else {
            Book::create($data);
            session()->flash('ok', 'Buku ditambahkan ke katalog.');
        }

        $this->redirectRoute('perpus.index');
    }

    public function render(): View
    {
        return view('livewire.perpus.book-form', ['isEdit' => (bool) $this->book?->exists])
            ->layout('layouts.app', ['title' => $this->book?->exists ? 'Ubah Buku' : 'Tambah Buku']);
    }
}
