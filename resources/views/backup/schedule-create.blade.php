@extends('layouts.app')

@section('title', 'Create Backup Schedule')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Create Backup Schedule" subtitle="{{ $site->domain }}" back="{{ route('backups.index', $site) }}">
        </x-page-header>

        @livewire('backup.create-backup-schedule', ['site' => $site])
    </div>
@endsection
