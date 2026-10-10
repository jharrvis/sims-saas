<div class="text-center py-12">
    <i data-lucide="user-x" class="w-12 h-12 mx-auto text-slate-200"></i>
    <h2 class="font-bold mt-4">Akun Belum Terhubung</h2>
    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
        Nomor WhatsApp ini belum terdaftar sebagai siswa atau wali murid di sekolah mana pun.
        Hubungi tata usaha sekolah untuk mendaftarkan nomor Anda.
    </p>
    <form method="POST" action="{{ route('pwa.logout') }}" class="mt-6">
        @csrf
        <button class="text-sm font-bold text-primary">Keluar dan ganti nomor</button>
    </form>
</div>
