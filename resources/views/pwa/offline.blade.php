<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#3c50e0">
    <title>Offline — SIMS</title>
    @vite('resources/css/pwa.css')
</head>
<body class="bg-[#f8fafc] font-sans antialiased">
<div class="app-shell flex flex-col items-center justify-center px-8 text-center">
    <div class="w-20 h-20 rounded-2xl bg-[#3c50e0] flex items-center justify-center mx-auto mb-4 shadow-lg">
        <x-icon name="wifi-off" class="w-10 h-10 text-white" />
    </div>
    <h1 class="text-xl font-extrabold text-slate-800">Tidak ada koneksi</h1>
    <p class="text-sm text-slate-500 mt-2">Periksa koneksi internet Anda, lalu coba lagi.</p>
    <button onclick="window.location.reload()" class="mt-6 rounded-xl bg-[#3c50e0] px-6 py-3.5 text-white font-bold text-sm shadow-lg shadow-[#3c50e0]/30">
        Coba Lagi
    </button>
</div>
<script>
    window.addEventListener('online', () => window.location.reload());
</script>
</body>
</html>
