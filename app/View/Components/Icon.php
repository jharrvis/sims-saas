<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Render ikon Lucide sebagai SVG inline di server.
 *
 * Tidak bergantung pada createIcons() di browser, sehingga aman
 * terhadap morphing Livewire (ikon tidak hilang setelah update DOM).
 *
 * Contoh: <x-icon name="eye" class="w-4 h-4" />
 */
class Icon extends Component
{
    public string $markup;

    public function __construct(
        public string $name,
        public string $class = 'w-4 h-4',
    ) {
        $this->markup = self::renderSvg($name, $class);
    }

    public function render(): View
    {
        return view('components.icon');
    }

    public static function renderSvg(string $name, string $class = 'w-4 h-4'): string
    {
        // Normalisasi: "trash-2" -> "trash-2", "Trash2" -> "trash-2"
        $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name) ?? $name);
        $slug = str_replace('_', '-', $slug);

        return Cache::rememberForever('lucide-svg:'.$slug.':'.$class, function () use ($slug, $class) {
            $inner = self::innerSvg($slug);

            if ($inner === null) {
                return '';
            }

            $escClass = e($class);

            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"'
                .' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"'
                .' stroke-linejoin="round" class="'.$escClass.'" data-lucide="'.$slug.'" aria-hidden="true">'
                .$inner.'</svg>';
        });
    }

    /**
     * Ambil isi SVG dari file ikon yang di-pre-generate (resources/lucide-icons.php).
     * File ini di-commit ke repo sehingga tidak bergantung pada node_modules di production.
     */
    private static function innerSvg(string $slug): ?string
    {
        static $icons = null;

        if ($icons === null) {
            $path = resource_path('lucide-icons.php');
            $icons = is_file($path) ? require $path : [];
        }

        return $icons[$slug] ?? null;
    }
}
