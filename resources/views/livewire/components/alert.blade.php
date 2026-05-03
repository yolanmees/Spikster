<div>
    @if ($show)
        <div x-data="{ show: @entangle('show') }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95" @class([
                'rounded-lg p-4 mb-4 flex items-start',
                'bg-green-50 border border-green-200 text-green-800' => $type === 'success',
                'bg-red-50 border border-red-200 text-red-800' => $type === 'error',
                'bg-yellow-50 border border-yellow-200 text-yellow-800' =>
                    $type === 'warning',
                'bg-zinc-100 border border-zinc-200 text-zinc-800' => $type === 'info',
            ]) role="alert">
            <!-- Icon -->
            <div class="flex-shrink-0 mr-3">
                @if ($type === 'success')
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                @elseif($type === 'error')
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                @elseif($type === 'warning')
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="w-5 h-5 text-zinc-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                @endif
            </div>

            <!-- Message -->
            <div class="flex-1">
                <p class="text-sm font-medium">{{ $message }}</p>
            </div>

            <!-- Dismiss button -->
            @if ($dismissible)
                <button type="button" wire:click="dismiss" @class([
                    'ml-3 flex-shrink-0 inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
                    'text-green-600 hover:bg-green-100 focus:ring-green-500' =>
                        $type === 'success',
                    'text-red-600 hover:bg-red-100 focus:ring-red-500' => $type === 'error',
                    'text-yellow-600 hover:bg-yellow-100 focus:ring-yellow-500' =>
                        $type === 'warning',
                    'text-zinc-600 hover:bg-zinc-100 focus:ring-zinc-500' => $type === 'info',
                ])>
                    <span class="sr-only">Sluiten</span>
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            @endif
        </div>
    @endif
</div>
