@props(['submit'])

<div
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700']) }}>
    <div class="p-6">
        <!-- Header Section -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
            <x-section-title>
                <x-slot name="title">{{ $title }}</x-slot>
                <x-slot name="description">{{ $description }}</x-slot>
            </x-section-title>
        </div>

        <!-- Form Section -->
        <form wire:submit.prevent="{{ $submit }}">
            <div class="space-y-6">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
