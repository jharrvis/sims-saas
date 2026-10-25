<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / Survei</span>
            <h2 class="management-page-title">Survei</h2>
            <p>Survei sekolah beserta target, status, dan jumlah respondennya.</p>
        </div>
        <div class="page-actions">
            @can('survei.create')
                <a href="{{ route('survei.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Buat Survei
                </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Target</th>
                        <th scope="col">Status</th>
                        <th scope="col">Responden</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="survei-{{ $item->id }}">
                            <td>
                                <span class="block font-semibold">{{ $item->judul }}</span>
                                <span class="text-xs text-slate-400">{{ $item->questions_count }} pertanyaan</span>
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ \App\Models\Survey::TARGET[$item->target_type] }}</td>
                            <td>
                                <span class="badge {{ $item->status === 'aktif' ? 'badge-green' : ($item->status === 'tutup' ? 'badge-red' : 'badge-gray') }}">
                                    {{ \App\Models\Survey::STATUS[$item->status] }}
                                </span>
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->responses_count }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if ($item->isDapatDiisi())
                                    @can('survei.isi')
                                        <a href="{{ route('survei.isi', $item) }}" class="text-xs font-semibold text-success hover:underline">Isi</a>
                                    @endcan
                                @endif
                                @can('survei.hasil')
                                    <a href="{{ route('survei.hasil', $item) }}" class="ml-2 text-xs font-semibold text-primary hover:underline">Hasil</a>
                                @endcan
                                @can('survei.update')
                                    <a href="{{ route('survei.edit', $item) }}" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">Ubah</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada survei.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
