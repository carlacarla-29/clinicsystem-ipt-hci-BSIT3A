@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-gray-900 bg-primary-light focus:outline-none focus:text-gray-900 focus:bg-primary-light focus:border-primary-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-primary-light hover:border-primary/60 focus:outline-none focus:text-gray-800 focus:bg-primary-light focus:border-primary/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>


