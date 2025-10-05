@extends('layouts.app')

@section('title')
    Sites
@endsection

@section('content')
    <div class="space-y-6">
        <x-page-header title="Sites">
            <x-slot name="actions">
                <x-primary-button id="newSite">
                    <x-icon icon="plus" class="-ml-1 mr-2 h-5 w-5" />
                    New Site
                </x-primary-button>
            </x-slot>
        </x-page-header>
        
        @livewire('site.site-table')
    </div>
@endsection



@section('extra')
<!-- New Site Modal -->
<div id="newSiteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        New Site
                    </h3>
                    <button type="button" id="closeNewSiteModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4">
                    @livewire('site.new-site')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('js')
<script>
    // Modal handling
    const newSiteModal = document.getElementById('newSiteModal');
    const newSiteButton = document.getElementById('newSite');
    const closeNewSiteModalButton = document.getElementById('closeNewSiteModal');

    // Open modal
    newSiteButton?.addEventListener('click', function() {
        newSiteModal?.classList.remove('hidden');
    });

    // Close modal
    closeNewSiteModalButton?.addEventListener('click', function() {
        newSiteModal?.classList.add('hidden');
    });

    // Close modal on background click
    newSiteModal?.addEventListener('click', function(e) {
        if (e.target === newSiteModal || e.target.classList.contains('bg-opacity-75')) {
            newSiteModal?.classList.add('hidden');
        }
    });

    // Listen for Livewire event to close modal
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-modal', () => {
            newSiteModal?.classList.add('hidden');
        });

        Livewire.on('site-created', () => {
            newSiteModal?.classList.add('hidden');
        });
    });
</script>
@endsection
