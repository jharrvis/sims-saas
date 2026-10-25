@php
    $extra = isset($attributes) ? $attributes->except('class')->toHtml() : '';
    $markup = $extra !== '' ? preg_replace('/^<svg/', '<svg '.$extra, $markup, 1) : $markup;
@endphp
{!! $markup !!}
