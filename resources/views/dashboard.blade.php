<x-layouts.app title="Dashboard">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'Total Siswa', 'value' => '—', 'icon' => 'users', 'color' => 'bg-primary'],
            ['label' => 'Total Guru', 'value' => '—', 'icon' => 'graduation-cap', 'color' => 'bg-success'],
            ['label' => 'Kehadiran Hari Ini', 'value' => '—', 'icon' => 'clipboard-check', 'color' => 'bg-warning'],
            ['label' => 'SPP Tertunggak', 'value' => '—', 'icon' => 'wallet', 'color' => 'bg-danger'],
        ] as $stat)
            <div class="bg-white dark:bg-boxdark rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ $stat['value'] }}</p>
                    </div>
                    <span class="w-11 h-11 rounded-xl {{ $stat['color'] }}/10 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 bg-white dark:bg-boxdark rounded-xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="font-semibold text-slate-800 dark:text-white">Selamat datang di SIMS</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Fondasi multi-tenant aktif: isolasi fail-closed, identitas global + membership, dan guard platform.
        </p>
    </div>
</x-layouts.app>
