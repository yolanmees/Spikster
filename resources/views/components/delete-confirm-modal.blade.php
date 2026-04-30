{{--
    x-delete-confirm-modal - Reusable delete confirmation dialog.

    Usage (Alpine/Livewire toggle):
        <x-delete-confirm-modal
            :show="$confirmingDeletion"
            title="Delete Server"
            message="Are you sure you want to delete this server? This action cannot be undone."
            confirm-action="delete"
            cancel-action="cancelDelete"
        />

    Props:
        show            – bool (Livewire property that controls visibility)
        title           – string
        message         – string
        confirmAction   – Livewire method to call on confirm (wire:click)
        cancelAction    – Livewire method to call on cancel  (wire:click)
        confirmLabel    – string (default: "Delete")
        cancelLabel     – string (default: "Cancel")
        note            – optional extra note shown below the message
--}}
@props([
    'show'          => false,
    'title'         => 'Confirm Delete',
    'message'       => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirmAction' => 'confirmDelete',
    'cancelAction'  => 'cancelDelete',
    'confirmLabel'  => 'Delete',
    'cancelLabel'   => 'Cancel',
    'note'          => null,
])

@if ($show)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-0">
        {{-- Backdrop --}}
        <div wire:click="{{ $cancelAction }}"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        {{-- Panel --}}
        <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl ring-1 ring-black/10 dark:ring-white/10 transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    {{-- Warning icon --}}
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>

                        @if ($note)
                            <div class="mt-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 px-3 py-2">
                                <p class="text-xs text-yellow-800 dark:text-yellow-300">{{ $note }}</p>
                            </div>
                        @endif

                        @isset($extra)
                            <div class="mt-3">{{ $extra }}</div>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <x-secondary-button type="button" wire:click="{{ $cancelAction }}">
                    {{ $cancelLabel }}
                </x-secondary-button>
                <x-danger-button type="button" wire:click="{{ $confirmAction }}">
                    {{ $confirmLabel }}
                </x-danger-button>
            </div>
        </div>
    </div>
</div>
@endif
