@props(['variant' => 'primary','type' => 'button'])
<button type="{{ $type }}" {{ $attributes->class(['btn','btn-'.$variant]) }}>
    {{ $slot }}
</button>
