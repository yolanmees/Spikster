@extends('layouts.app')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

    <div class="col-span-1 bg-white rounded-lg">
        <h5 class="font-bold text-gray-600 px-4 py-4">{{ __('spikster.titles.settings') }}</h5>
        <ul class="list-group list-group-flush mb-4">
            <a href="{{ route('settings.general') }}" class="text-decoration-none">
                <li class="list-group-item hover:bg-gray-100 text-sm font-bold px-4 py-1 @if (request()->routeIs('settings.users')) font-bold @endif">
                    General Settings
                </li>
            </a>
            <a href="{{ route('settings.users') }}" class="text-decoration-none">   
                <li class="list-group-item hover:bg-gray-100 text-sm px-4 py-1 @if (request()->routeIs('settings.users')) font-bold @endif">
                        Users
                </li>
            </a>
    </div>

    <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
        @yield('settings-content')
    </div>
    
</div>
@endsection