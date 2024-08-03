@extends('layouts.app')

@section('content')

    <body>
        @if (Session::has('success'))
            <div class="alert alert-success">
                <p>{{ Session::get('success') }}</p>
            </div>
        @elseif (Session::has('failed'))
            <div class="alert alert-danger">
                <p>{{ Session::get('failed') }}</p>
            </div>
        @endif
        <div class="container-fluid">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-card header="Create new Wordpress" size="md" dark="false">
                       <form class="flex flex-col" action="{{ route('site.wordpress.create', $site_id) }}" method="POST">
                        @csrf
                        <label class="my-2" for="path">Install path:</label>
                        <input type="text" id="path" name="path" class="form-control" required>
                        <label class="my-2" for="username">Wordpress Admin Username:</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <label class="my-2" for="password">Wordpress Admin Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button type="submit" class="mt-2 btn btn-primary">Install Wordpress</button>
                    </form>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                </x-card>
                <x-card header="List of Wordpress" size="md" dark="false">
                    <table class="table w-full table-bordered">
                        <thead>
                            <tr>
                                <th>Path</th>
                                <th>Username</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wordpresses as $wordpress)
                                <tr class="text-center hover:bg-gray-100">
                                    <td>{{ $wordpress->path }}</td>
                                    <td>{{ $wordpress->username }}</td>
                                    <td>
                                        <form action="{{ route('site.wordpress.delete', $wordpress->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    {{ $wordpresses->links() }}
                </x-card>

            </div>
        </div>
    </body>
@endsection
