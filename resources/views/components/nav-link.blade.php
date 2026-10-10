@props(['href' => '#', 'icon' => 'circle', 'label' => '', 'active' => false])

<a href="{{ $href }}"
   class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
          {{ $active ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-slate-800 hover:text-white' }}"
   title="{{ $label }}">
    <i data-lucide="{{ $icon }}" class="w-5 h-5 shrink-0"></i>
    <span>{{ $label }}</span>
</a>
