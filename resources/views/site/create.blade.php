@extends('layouts.app')

@section('title', 'New Site')

@section('content')
    <div class="space-y-6">
        <x-page-header title="New Site" subtitle="Create a new site on your server" back="{{ route('site.list') }}">
        </x-page-header>

        @livewire('site.new-site')
    </div>
@endsection
