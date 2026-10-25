<div class="screen app-screen screen-enter unlinked-screen">
    <section class="surface-card unlinked-card" aria-labelledby="unlinked-title">
        <span class="unlinked-icon"><x-icon name="user-x" class="h-8 w-8" /></span>
        <span class="overline">PERLU BANTUAN SEKOLAH</span>
        <h1 id="unlinked-title">Akun belum terhubung</h1>
        <p>Nomor WhatsApp akun ini belum terhubung dengan data siswa atau wali. Hubungi tata usaha sekolah agar nomor Anda didaftarkan pada profil yang benar.</p>

        <div class="unlinked-steps">
            <span><strong>1</strong><small>Hubungi tata usaha sekolah</small></span>
            <span><strong>2</strong><small>Minta nomor akun ditautkan</small></span>
            <span><strong>3</strong><small>Masuk kembali setelah diperbarui</small></span>
        </div>

        <form method="POST" action="{{ route('pwa.logout') }}">
            @csrf
            <button class="sims-btn sims-btn-primary sims-btn-lg w-full" type="submit">
                <x-icon name="log-out" class="h-4 w-4" /> Keluar dan ganti akun
            </button>
        </form>
    </section>
</div>
