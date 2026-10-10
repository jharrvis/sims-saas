<div>
    <div class="rounded-2xl bg-slate-50 p-4 mb-4">
        <p class="font-bold">{{ $user->name }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ $user->phone }}</p>
        <p class="text-[11px] text-slate-400 mt-1">
            {{ $role === 'orang-tua' ? 'Orang Tua/Wali' : 'Siswa' }}
            @if ($student) · {{ $student->nama }} @endif
        </p>
    </div>

    @if ($role === 'orang-tua' && $children->count() > 1)
        <h3 class="font-bold text-sm mb-2">Mode Profil Anak</h3>
        <div class="space-y-2 mb-4">
            @foreach ($children as $child)
                <form method="POST" action="{{ route('pwa.switch-child') }}">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $child->id }}">
                    <button class="w-full flex items-center justify-between rounded-xl border px-4 py-3 text-left {{ $child->id === $student?->id ? 'border-primary bg-primary/5' : 'border-slate-200' }}">
                        <span class="text-sm font-semibold">{{ $child->nama }}</span>
                        @if ($child->id === $student?->id)
                            <span class="text-[10px] font-bold text-primary">AKTIF</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mb-4">Mode anak bersifat read-only dan tercatat atas nama akun Anda.</p>
    @endif

    <h3 class="font-bold text-sm mb-2">Perangkat Aktif</h3>
    @forelse ($tokens as $t)
        <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 mb-1.5">
            <div>
                <p class="text-sm font-semibold">{{ $t->name }}</p>
                <p class="text-[11px] text-slate-400">
                    Terakhir dipakai {{ $t->last_used_at?->diffForHumans() ?? 'belum pernah' }}
                </p>
            </div>
            <button wire:click="revokeToken({{ $t->id }})" class="text-xs font-bold text-danger">Cabut</button>
        </div>
    @empty
        <p class="text-xs text-slate-400 italic mb-4">Tidak ada token API aktif.</p>
    @endforelse

    <button wire:click="logout"
            class="w-full mt-4 rounded-xl border border-danger/30 text-danger py-3 font-bold text-sm">
        Keluar
    </button>
</div>
