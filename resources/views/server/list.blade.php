@extends('layouts.app')

@section('title', 'Servers')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Servers">
            <x-slot name="actions">
                <x-primary-button @click="$dispatch('open-modal', 'new-server')">
                    <x-icon icon="plus" class="-ml-1 mr-2 h-5 w-5" />
                    New Server
                </x-primary-button>
            </x-slot>
        </x-page-header>

        @livewire('server.server-table')
    </div>

    <x-modal id="new-server" title="New Server" max-width="2xl">
        @livewire('server.new-server')
    </x-modal>
@endsection

@section('js')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-modal', () => window.dispatchEvent(new CustomEvent('close-modal')));
        Livewire.on('server-created', () => window.dispatchEvent(new CustomEvent('close-modal')));
    });
</script>
@endsection
