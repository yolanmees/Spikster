@extends('layouts.app')

@section('title', __('spikster.server_crontab'))

@section('content')
    <div class="space-y-6">
        <x-page-header title="{{ __('spikster.server_crontab') }}">
            <x-slot name="actions">
                <x-back-button :href="route('server.edit', $server_id)" label="Back to Server" />
            </x-slot>
        </x-page-header>

        @livewire('server.cron-manager', ['server' => \App\Models\Server::where('server_id', $server_id)->firstOrFail()])
    </div>
@endsection
