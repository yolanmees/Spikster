@extends('layouts.app')

@section('title', 'Servers')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Servers">
            <x-slot name="actions">
                <a href="{{ route('server.create') }}" wire:navigate>
                    <x-primary-button>
                        <x-icon icon="plus" class="-ml-1 mr-2 h-5 w-5" />
                        New Server
                    </x-primary-button>
                </a>
            </x-slot>
        </x-page-header>

        @livewire('server.server-table')
    </div>

@endsection
