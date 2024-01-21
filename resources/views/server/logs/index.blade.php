@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.server') }}
@endsection



@section('content')
<div>


    <table class="table-auto">
        <thead>
            <tr>
                <th class="px-4 py-2">{{ __('spikster.logs.table.header') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs['logs'] as $log)
                <tr>
                    <td class="border px-4 py-2">
                        <a href="{{ route('logs.show', ['server_id' => $server->server_id, 'log' => $log]) }}">{{ $log }}</a>
                    </td>
                </tr>
            @endforeach
            @foreach ($logs['server_logs'] as $log)
                <tr>
                    <td class="border px-4 py-2">
                        <a href="{{ route('logs.show', ['server_id' => $server->server_id, 'log' => $log]) }}">{{ $log }}</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

</div>
@endsection



@section('css')

@endsection



@section('js')
<script>
    
</script>
@endsection
