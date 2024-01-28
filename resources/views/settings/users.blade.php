@extends('layouts.settings')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('setings-content')
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-users fs-fw mr-1"></i>
                Users
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('spikster.username') }}</th>
                            <th scope="col">{{ __('spikster.password') }}</th>
                            <th scope="col">{{ __('spikster.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection