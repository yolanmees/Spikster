@extends('layouts.settings')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('settings-content')
<div class="flex flex-col gap-4">
    
        <x-card header="{{ __('spikster.panel_url') }}" size="md" dark="false">
                @livewire('settings.panel-domain')
        </x-card>

        <x-card header="{{ __('spikster.panel_api') }}" size="md" dark="false">
                @livewire('settings.api-key')
        </x-card>

</div>
@endsection        