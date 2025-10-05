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

        <!-- Content Section -->
        <div class="space-y-6">
            {{ $content }}
        </div>
    </div>
</div>
