@extends('layouts.settings')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('settings-content')
<div class="row">
    
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-globe fs-fw mr-1"></i>
                {{ __('spikster.panel_url') }}
            </div>
            <div class="card-body">
                @livewire('settings.panel-domain')
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-code fs-fw mr-1"></i>
                {{ __('spikster.panel_api') }}
            </div>
            <div class="card-body">
                @livewire('settings.api-key')
            </div>
        </div>
    </div>

</div>
@endsection