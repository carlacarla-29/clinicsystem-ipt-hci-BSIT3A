@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'auth-success']) }}>
        {{ $status }}
    </div>
@endif


