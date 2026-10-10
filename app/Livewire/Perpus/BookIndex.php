<?php

declare(strict_types=1);

namespace App\Livewire\Perpus;

use App\Models\Book;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Katalog buku + ketersediaan.
 */
class BookIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $books = Book::query()
            ->when($this->search !== '', fn ($q) => $q->where('judul', 'like', '%'.$this->search.'%')->orWhere('kode', 'like', '%'.$this->search.'%'))
            ->orderBy('judul')
            ->paginate(15);

        return view('livewire.perpus.book-index', ['books' => $books])
            ->layout('layouts.app', ['title' => 'Perpustakaan']);
    }
}
