<div>
    <div x-data="{ show: @entangle('show') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @if ($closeable) @click="$wire.close()" @endif></div>

        <!-- Modal panel -->
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @class([
                    'relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full',
                    'sm:max-w-sm' => $size === 'sm',
                    'sm:max-w-lg' => $size === 'md',
                    'sm:max-w-2xl' => $size === 'lg',
                    'sm:max-w-4xl' => $size === 'xl',
                    'sm:max-w-full mx-4' => $size === 'full',
                ])>
                <!-- Header -->
                @if ($title)
                    <div
                        class="bg-gray-50 px-4 py-3 sm:px-6 flex items-center justify-between border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                            {{ $title }}
                        </h3>
                        @if ($closeable)
                            <button type="button" wire:click="close"
                                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2">
                                <span class="sr-only">Sluiten</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Body -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if (isset($footer))
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
