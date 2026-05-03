@extends('layouts.app')

@section('title', 'New Server')

@section('content')
    <div class="space-y-6">
        <x-page-header title="New Server" subtitle="Add a new server to manage" back="{{ route('server.list') }}">
        </x-page-header>

        @livewire('server.new-server')
    </div>
@endsection
