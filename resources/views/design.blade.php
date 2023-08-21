@extends('layouts.app')

@section('content')
       
<ol class="breadcrumbs">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
    <li class="ml-1 breadcrumb-item active">{{ __('cipi.sites') }}:<b><span class="ml-1" id="serversites"></span></b></li>
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

@endsection