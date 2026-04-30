@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }} - Users
@endsection

@section('settings-content')
    <x-page-header title="User Management" subtitle="Manage users and their access" />

    <livewire:settings.user-management />
@endsection
