<div class="screen app-screen screen-enter profile-screen">
    @php
        $displayName = $role === 'orang-tua' ? $user->name : ($student?->nama ?? $user->name);
        $roleLabel = $role === 'orang-tua' ? 'Orang Tua/Wali' : 'Siswa';
    @endphp

    <div class="page-title">
        <span class="overline">AKUN</span>
        <h1>Profil pengguna</h1>
        <p>Identitas, perangkat, dan preferensi aplikasi sekolah.</p>
    </div>

    <section class="profile-hero" aria-labelledby="profile-name">
        <div class="profile-avatar">
            @if ($role !== 'orang-tua' && $student?->fotoUrl())
                <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $displayName }}" class="h-full w-full rounded-[inherit] object-cover">
            @else
                {{ mb_substr($displayName, 0, 1) }}
            @endif
        </div>
        <h2 id="profile-name">{{ $displayName }}</h2>
        <p>{{ $user->phone ?: 'Nomor telepon belum tersedia' }}</p>
        <span>{{ $roleLabel }}{{ $student?->nis ? ' · NIS '.$student->nis : '' }}</span>
    </section>

    @if ($role !== 'orang-tua' && $student)
        <section class="surface-card profile-info-list" aria-labelledby="student-data-title">
            <h2 id="student-data-title" class="sr-only">Data siswa</h2>
            <div>
                <span class="row-icon violet"><x-icon name="graduation-cap" class="h-5 w-5" /></span>
                <span><small>NIS / NISN</small><strong>{{ $student->nis }}{{ $student->nisn ? ' / '.$student->nisn : '' }}</strong></span>
            </div>
            @if ($student->tgl_lahir)
                <div>
                    <span class="row-icon amber"><x-icon name="calendar" class="h-5 w-5" /></span>
                    <span><small>Tanggal lahir</small><strong>{{ $student->tgl_lahir->locale('id')->isoFormat('D MMMM Y') }}</strong></span>
                </div>
            @endif
            @if ($student->alamat)
                <div>
                    <span class="row-icon blue"><x-icon name="map-pin" class="h-5 w-5" /></span>
                    <span><small>Alamat</small><strong>{{ $student->alamat }}</strong></span>
                </div>
            @endif
        </section>
    @endif

    @if ($student)
        <section aria-labelledby="qr-absensi-title">
            <div class="section-head">
                <h2 id="qr-absensi-title">QR Absensi</h2>
                <span class="pwa-section-meta">{{ $student->nis ? 'Tunjukkan ke scanner di sekolah' : 'NIS belum tersedia' }}</span>
            </div>
            @if ($student->nis)
                <div class="surface-card text-center">
                    <div class="bg-white p-4 rounded-2xl inline-block">
                        {!! \App\Domains\Pwa\Services\QrLoginService::qrSvg($student->nis, 220) !!}
                    </div>
                    <p><strong>{{ $student->nama }} · NIS {{ $student->nis }}</strong></p>
                    <p class="profile-helper">Pindai kode ini dengan scanner absensi sekolah. Kode berisi NIS siswa dan bukan token login.</p>
                </div>
            @else
                <div class="surface-card">
                    <p><strong>QR belum tersedia.</strong></p>
                    <p class="profile-helper">NIS siswa belum diisi. Minta admin sekolah melengkapi data siswa agar QR absensi dapat ditampilkan.</p>
                </div>
            @endif
        </section>
    @endif

    @if ($role === 'orang-tua' && $children->count() > 1)
        <section aria-labelledby="child-profile-title">
            <div class="section-head">
                <h2 id="child-profile-title">Profil Anak</h2>
                <span class="pwa-section-meta">{{ $children->count() }} anak terhubung</span>
            </div>
            <div class="pwa-card-stack">
                @foreach ($children as $child)
                    <form method="POST" action="{{ route('pwa.switch-child') }}" wire:key="profile-child-{{ $child->id }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $child->id }}">
                        <button type="submit" class="surface-card child-card {{ $child->id === $student?->id ? 'is-active' : '' }}" @disabled($child->id === $student?->id)>
                            <span class="child-avatar">{{ mb_substr($child->nama, 0, 1) }}</span>
                            <span class="child-copy"><strong>{{ $child->nama }}</strong><small>{{ $child->nis ? 'NIS '.$child->nis : 'Data siswa terhubung' }}</small></span>
                            <span class="status-chip {{ $child->id === $student?->id ? 'success' : 'info' }}">{{ $child->id === $student?->id ? 'AKTIF' : 'PILIH' }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
            <p class="profile-helper"><x-icon name="shield-check" class="h-4 w-4" /> Perpindahan anak tercatat atas nama akun wali dan hanya mengubah konteks baca.</p>
        </section>
    @endif

    <section aria-labelledby="device-title">
        <div class="section-head">
            <h2 id="device-title">Perangkat Aktif</h2>
            <span class="pwa-section-meta">{{ $tokens->count() }} token</span>
        </div>
        <div class="surface-card settings-list profile-settings-list">
            @forelse ($tokens as $token)
                <div class="settings-row" wire:key="device-token-{{ $token->id }}">
                    <span class="row-icon green"><x-icon name="smartphone" class="h-5 w-5" /></span>
                    <span><strong>{{ $token->name }}</strong><small>Terakhir dipakai {{ $token->last_used_at?->diffForHumans() ?? 'belum pernah' }}</small></span>
                    <button type="button" class="profile-text-action danger" wire:click="revokeToken({{ $token->id }})" wire:confirm="Cabut akses perangkat {{ $token->name }}?" wire:loading.attr="disabled" wire:target="revokeToken({{ $token->id }})">
                        <span wire:loading.remove wire:target="revokeToken({{ $token->id }})">Cabut</span>
                        <span wire:loading wire:target="revokeToken({{ $token->id }})">Memproses</span>
                    </button>
                </div>
            @empty
                <div class="pwa-empty-state pwa-empty-state-plain">
                    <span class="row-icon green"><x-icon name="shield-check" class="h-5 w-5" /></span>
                    <span><strong>Tidak ada token API aktif</strong><small>Sesi browser saat ini tetap dilindungi oleh autentikasi SIMS.</small></span>
                </div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="notification-title">
        <div class="section-head"><h2 id="notification-title">Aplikasi & Notifikasi</h2></div>
        <div class="surface-card settings-list profile-settings-list">
            <div class="settings-row" data-pwa-push data-vapid-url="{{ route('pwa.push.vapid-key') }}" data-subscribe-url="{{ route('pwa.push.subscribe') }}" data-unsubscribe-url="{{ route('pwa.push.unsubscribe') }}" aria-busy="false">
                <span class="row-icon blue"><x-icon name="bell" class="h-5 w-5" /></span>
                <span><strong>Notifikasi perangkat</strong><small data-pwa-push-status>Memeriksa status notifikasi…</small></span>
                <button type="button" class="profile-text-action" data-pwa-push-enable hidden>Aktifkan</button>
                <button type="button" class="profile-text-action danger" data-pwa-push-disable hidden>Matikan</button>
            </div>
            <button type="button" class="settings-row settings-button" data-pwa-install hidden>
                <span class="row-icon violet"><x-icon name="download" class="h-5 w-5" /></span>
                <span><strong>Instal aplikasi SIMS</strong><small>Tambahkan ke layar utama perangkat.</small></span>
                <x-icon name="chevron-right" class="h-4 w-4" />
            </button>
        </div>
    </section>

    <button type="button" class="logout-button profile-logout" wire:click="logout" wire:confirm="Keluar dari akun SIMS di perangkat ini?" wire:loading.attr="disabled" wire:target="logout">
        <x-icon name="log-out" class="h-4 w-4" />
        <span wire:loading.remove wire:target="logout">Keluar dari akun</span>
        <span wire:loading wire:target="logout">Mengakhiri sesi…</span>
    </button>
</div>
