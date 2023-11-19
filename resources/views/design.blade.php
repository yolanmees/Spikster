@extends('layouts.app')

@section('content')
       
<ol class="breadcrumbs">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
    <li class="ml-1 breadcrumb-item active">{{ __('spikster.sites') }}:<b><span class="ml-1" id="serversites"></span></b></li>
    <li class="ml-1 breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i class="fas fa-circle-notch fa-spin"></i></span></b></li>
</ol>
<div class="card">
    <div class="card-header">
        Card header
    </div>
    <div class="card-body">
        Card body
    </div>
</div>
<div class="flex gap-x-4 my-4">
    <button class="btn btn-primary">Primary</button>
    <button class="btn btn-secondary">Secondary</button>
    <button class="btn btn-success">Success</button>
    <button class="btn btn-danger">Danger</button>
    <button class="btn btn-warning">Warning</button>
    <button class="btn btn-info">Info</button>
    <button class="btn btn-light">Light</button>
    <button class="btn btn-dark">Dark</button>
    <button class="btn btn-link">Link</button>
</div>

<div class="pb-4">
    <div class="sm:hidden">
        <label for="tabs" class="sr-only">Select a tab</label>
        <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
        <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option selected>Monitor</option>
            <option>Server information</option>
            <option></option>
            <option>Billing</option>
        </select>
    </div>
    <div class="hidden sm:block">
        <nav class="flex space-x-4" aria-label="Tabs">
            <!-- Current: "bg-gray-200 text-gray-800", Default: "text-gray-600 hover:text-gray-800" -->
            <a href="#" class="tab-item">Monitor</a>
            <a href="#" class="tab-item">Server information</a>
            <a href="#" class="tab-item-active" aria-current="page">Security</a>
            <a href="#" class="tab-item">Billing</a>
        </nav>
    </div>
</div>


@endsection