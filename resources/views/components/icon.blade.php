@props(['name', 'class' => ''])

@php
    $map = [
        'shopping-cart' => 'shopping_cart',
        'cake' => 'cake',
        'truck' => 'local_shipping',
        'bolt' => 'bolt',
        'film' => 'movie',
        'globe-alt' => 'language',
        'heart' => 'favorite',
        'book-open' => 'menu_book',
    ];

    $materialName = $map[$name] ?? $name;
@endphp

<span {{ $attributes->merge(['class' => 'material-symbols-outlined ' . $class]) }}>
    {{ $materialName }}
</span>