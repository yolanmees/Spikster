@extends('layouts.app')

@section('title', 'Sites')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Sites">
            <x-slot name="actions">
                <x-primary-button @click="$dispatch('open-modal', 'new-site')">
                    <x-icon icon="plus" class="-ml-1 mr-2 h-5 w-5" />
                    New Site
                </x-primary-button>
            </x-slot>
        </x-page-header>

        @livewire('site.site-table')
    </div>

    <x-modal id="new-site" title="New Site" max-width="2xl">
        @livewire('site.new-site')
    </x-modal>
@endsection

@section('js')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-modal', () => window.dispatchEvent(new CustomEvent('close-modal')));
        Livewire.on('site-created', () => window.dispatchEvent(new CustomEvent('close-modal')));
        Livewire.on('open-new-site-modal', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'new-site' })));
    });
</script>
@endsection
