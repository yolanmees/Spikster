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
                <tr>
                    <td class="border px-4 py-2">
                        {{ $log['log'] }}
                    </td>
                </tr>

        </tbody>

</div>
@endsection



@section('css')

@endsection



@section('js')
<script>
    
</script>
@endsection
