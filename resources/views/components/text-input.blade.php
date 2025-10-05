@props(['label' => null, 'placeholder' => '', 'type' => 'text', 'model' => null, 'debounce' => null])

<div class="relative">
    @if($label)
        <label {{ $attributes->only('for') }} class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
        </label>
    @endif
    
    @if(isset($icon))
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $icon }}
            </div>
            <input 
                type="{{ $type }}"
                @if($model)
                    @if($debounce)
                        wire:model.live.debounce.{{ $debounce }}="{{ $model }}"
                    @else
                        wire:model="{{ $model }}"
                    @endif
                @endif
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => 'block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}
            >
        </div>
    @else
        <input 
            type="{{ $type }}"
            @if($model)
                @if($debounce)
                    wire:model.live.debounce.{{ $debounce }}="{{ $model }}"
                @else
                    wire:model="{{ $model }}"
                @endif
            @endif
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm']) }}
        >
    @endif
    
    @if(isset($suffix))
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            {{ $suffix }}
        </div>
    @endif
</div>
