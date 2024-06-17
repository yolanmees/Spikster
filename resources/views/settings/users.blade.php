@extends('layouts.settings')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('settings-content')
<div class="row">
    <div class="col-xl-6">
        <x-card header="Users" size="md" dark="false">
            <table class="table table-bordered w-full">
                <thead class=""
                    <tr>
                        <th scope="col">{{ __('spikster.username') }}</th>
                        <th scope="col">Email</th>
                        <th scope="col">{{ __('spikster.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border hover:bg-gray-100">
                            <td class="border text-center">
                                {{ $user->name }}
                            </td>
                            <td class="border text-center">
                                {{ $user->email }}
                            </td>
                            <td class="border text-center">
                                <form action="{{ route('settings.users.delete', $user->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection