<div class="px-4 pt-4 pb-28">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-center mt-4">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
            <x-icon name="user-x" class="w-8 h-8 text-slate-400" />
        </div>
        <h2 class="font-bold mt-4 text-slate-800">Akun Belum Terhubung</h2>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Nomor WhatsApp ini belum terdaftar sebagai siswa atau wali murid di sekolah mana pun.
            Hubungi tata usaha sekolah untuk mendaftarkan nomor Anda.
        </p>
        <form method="POST" action="{{ route('pwa.logout') }}" class="mt-6">
            @csrf
            <button class="text-sm font-bold text-primary">Keluar dan ganti nomor</button>
        </form>
    </div>
</div>
