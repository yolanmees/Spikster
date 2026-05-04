@extends('layouts.app')

@section('title', 'Site Tools - ' . $site->domain)

@section('content')
<div class="space-y-6">
    <x-page-header :title="'Tools: ' . $site->domain" subtitle="Manage your Laravel site">
        <x-slot name="actions">
            <x-back-button :href="route('sites.index')" />
        </x-slot>
    </x-page-header>

    @livewire('site.tools', ['site' => $site])
</div>
@endsection
