@extends('layouts.app')


@section('title')
{{ __('cipi.titles.server') }}
@endsection



@section('content')
<livewire:server.packages-installed server_id="{{$server_id}}" />
@endsection



@section('css')

@endsection



@section('js')

@endsection

