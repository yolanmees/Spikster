@props([
    'tabs' => [],      {{-- [['key' => 'monitor', 'label' => 'Monitor', 'icon' => '<svg...>']] --}}
    'model' => 'tab',  {{-- Alpine x-model variable --}}
])

<div class="pb-5">
    {{-- Mobile select --}}
    <div class="sm:hidden">
        <label class="sr-only">Select a tab</label>
        <select x-model="{{ $model }}"
            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-purple-700 focus:ring-purple-700 text-sm">
            @foreach($tabs as $tab)
                <option value="{{ $tab['key'] }}">{{ $tab['label'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Desktop tabs --}}
    <div class="hidden sm:block">
        <div class="border-b-2 border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px space-x-1" aria-label="Tabs">
                @foreach($tabs as $tab)
                    <button
                        type="button"
                        @click="{{ $model }} = '{{ $tab['key'] }}'"
                        :class="{{ $model }} === '{{ $tab['key'] }}'
                            ? 'border-purple-700 text-purple-700 dark:text-purple-400 bg-purple-50/50 dark:bg-purple-700/10'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="group inline-flex items-center gap-2 py-3.5 px-4 border-b-2 font-semibold text-sm rounded-t-lg transition-all duration-200"
                    >
                        @if(!empty($tab['icon']))
                            <span :class="{{ $model }} === '{{ $tab['key'] }}' ? 'text-purple-500' : 'text-gray-400 group-hover:text-gray-500'"
                                class="transition-colors">{!! $tab['icon'] !!}</span>
                        @endif
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>
</div>
