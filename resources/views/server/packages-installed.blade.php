@extends('layouts.app')

@section('title', 'Installed Packages')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Installed Packages">
            <x-slot name="actions">
                <x-back-button :href="route('server.edit', $server_id)" label="Back to Server" />
            </x-slot>
        </x-page-header>

        <livewire:server.packages-installed :server_id="$server_id" />
    </div>
@endsection
