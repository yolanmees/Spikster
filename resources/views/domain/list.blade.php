@extends('layouts.app')

@section('title')
    Domains & DNS
@endsection

@section('content')
    <div class="space-y-6">
        <x-page-header title="Domains & DNS">
            <x-slot name="description">
                Manage all domains and their DNS records across your servers
            </x-slot>
        </x-page-header>

        @livewire('domain.domain-table')
    </div>
@endsection
