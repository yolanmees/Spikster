@props([
    'tabs' => [],
    'activeTab' => null,
    'selectName' => 'tabs'
])

<div class="pb-4">
    <div class="sm:hidden">
        <label for="tabs-select" class="sr-only">Select a tab</label>
        <select id="tabs-select" name="{{ $selectName }}" class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500" onChange="location.href=this.value;">
            @foreach($tabs as $tab)
                <option value="{{ $tab['url'] }}" {{ $activeTab === $tab['name'] ? 'selected' : '' }}>
                    {{ $tab['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="hidden sm:block">
        <nav class="flex space-x-4" aria-label="Tabs">
            @foreach($tabs as $tab)
                <a href="{{ $tab['url'] }}" class="{{ $activeTab === $tab['name'] ? 'tab-item-active' : 'tab-item' }}">
                    {{ $tab['name'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>

@push('styles')
<style>
    .tab-item {
        @apply text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100;
    }
    .tab-item-active {
        @apply bg-gray-200 text-gray-800 dark:bg-gray-900 dark:text-gray-100;
    }
</style>
@endpush