@props(['id'])

<button 
    type="button" 
    id="drop-zone-{{ $id }}"
    data-district="{{ $id }}"
    {{ $attributes->merge(['class' => 'cell flex items-center justify-center bg-white border-2 border-dashed border-gray-300 rounded-lg w-full aspect-square text-center cursor-pointer transition-all hover:border-blue-400 hover:bg-blue-50']) }}
    aria-pressed="false" 
    data-testid="district-{{ $id }}"
>
    <span class="text-gray-400 font-bold text-xl">{{ $id }}</span>
</button>