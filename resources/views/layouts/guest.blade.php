<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — SIMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        if (localStorage.getItem('sims-theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="bg-slate-50 dark:bg-boxdark2 font-sans transition-colors duration-200 flex items-center justify-center min-h-screen p-4">
    <div class="absolute top-4 right-4">
        <button onclick="document.documentElement.classList.toggle('dark');localStorage.setItem('sims-theme',document.documentElement.classList.contains('dark')?'dark':'light')"
                class="p-2 rounded-full border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 text-slate-600 dark:text-slate-300"
                aria-label="Alihkan mode gelap">
            <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
            <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
        </button>
    </div>

    {{ $slot }}

    @livewireScripts
    <script src="https://unpkg.com/lucide@0.475.0"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
