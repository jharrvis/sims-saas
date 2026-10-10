<div>
    <h1 class="text-2xl font-extrabold mb-1">Katalog Modul</h1>
    <p class="text-sm text-slate-500 mb-5">Aktif/nonaktif per tenant diatur di halaman detail tenant.</p>

    <div class="rounded-xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Modul</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Kategori</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600">Tenant Aktif</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($modules as $m)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-semibold">{{ $m->nama }} <span class="block text-xs font-normal text-slate-400">{{ $m->kode }}</span></td>
                        <td class="px-4 py-2.5 text-xs">{{ $m->kategori }}</td>
                        <td class="px-4 py-2.5 font-extrabold">{{ $counts[$m->id] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
