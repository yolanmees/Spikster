@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }}
@endsection

@section('settings-content')
    <div class="row">
        <div class="col-xl-12">
            <x-card header="Users - Legacy View" size="md" dark="false">
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-lg mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-blue-800 dark:text-blue-200 font-medium">
                            Please use the new <a href="{{ route('settings.users') }}" class="underline font-bold">User
                                Management</a> page for full functionality.
                        </p>
                    </div>
                </div>

                <table class="table table-bordered w-full">
                    <thead class="" <tr>
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
