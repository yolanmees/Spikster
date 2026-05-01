@extends('layouts.app')

@section('title', 'Greeter Module')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Greeter Module" subtitle="Example module for the Spikster module API">
            <x-slot name="actions">
                <x-back-button :href="route('dashboard')" />
            </x-slot>
        </x-page-header>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
            <div class="prose dark:prose-invert max-w-none mb-6">
                <h3>About this module</h3>
                <p>
                    This is an example module that demonstrates how to build Spikster modules.
                    It shows menu registration, permissions, hooks, widgets, Livewire components,
                    and database migrations.
                </p>
                <ul>
                    <li><strong>Module:</strong> Greeter (alias: <code>greeter</code>)</li>
                    <li><strong>Permissions:</strong> <code>greeter.view</code>, <code>greeter.manage</code></li>
                    <li><strong>Hooks:</strong> <code>greeter.greeting</code> (filter)</li>
                    <li><strong>Database:</strong> <code>greetings</code> table</li>
                </ul>
            </div>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                @livewire('greeter::greeter')
            </div>
        </div>
    </div>
@endsection
