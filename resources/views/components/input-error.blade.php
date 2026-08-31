@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-sm text-red-700']) }}>{{ $message }}</p>
@enderror
