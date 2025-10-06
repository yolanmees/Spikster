@extends('layouts.app')

@section('title', 'Module Manager')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Module Manager</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage and configure your Spikster modules. Enable or disable features to customize your control panel.
        </p>
    </div>

    @livewire('module-manager')
@endsection
