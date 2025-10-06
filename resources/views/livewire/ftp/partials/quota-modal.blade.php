{{-- Update Quota Modal --}}
<div x-data="{ show: @entangle('showQuotaModal') }" x-show="show" x-cloak class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form wire:submit.prevent="updateQuota">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Update FTP Quota
                            </h3>
                            <div class="mt-4 space-y-4">
                                {{-- Quota --}}
                                <div>
                                    <label for="quota_update_mb" class="block text-sm font-medium text-gray-700">Quota
                                        (MB) *</label>
                                    <input wire:model="quota_mb" type="number" id="quota_update_mb" min="100"
                                        max="100000"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required>
                                    @error('quota_mb')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Min: 100 MB (0.1 GB), Max: 100,000 MB (100 GB)
                                    </p>
                                </div>

                                {{-- Quick Presets --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Presets</label>
                                    <div class="grid grid-cols-4 gap-2">
                                        <button type="button" wire:click="$set('quota_mb', 512)"
                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                                            512 MB
                                        </button>
                                        <button type="button" wire:click="$set('quota_mb', 1024)"
                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                                            1 GB
                                        </button>
                                        <button type="button" wire:click="$set('quota_mb', 5120)"
                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                                            5 GB
                                        </button>
                                        <button type="button" wire:click="$set('quota_mb', 10240)"
                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                                            10 GB
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update Quota
                    </button>
                    <button wire:click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
