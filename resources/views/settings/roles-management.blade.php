@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }} - Roles
@endsection

@section('settings-content')
    <x-page-header title="Role Management" subtitle="Create and manage roles with permissions" />

    <livewire:settings.role-management />
@endsection
