@props(['field'])

    @error($field)
        <p class="text-red-600 uppercase text-sm font-bold">{{ $message }}</p>
    @enderror