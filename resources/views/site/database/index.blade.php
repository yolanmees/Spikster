@extends('layouts.app')


@section('title')
    Database
@endsection



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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="card mb-4 col-span-2 md:col-span-1">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        Create new Database
                    </div>
                    <div class="card-body">
                        <form class="flex flex-col max-w-md" action="{{ route('site.database.create.database', $siteId) }}" method="post">
                            @csrf
                            <input type="text" name="database_name" class="form-control mb-4"
                                placeholder="database name">
                            <button class="btn btn-primary">Create database</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4 col-span-2 md:col-span-1">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        MYSQL Users
                    </div>
                    <div class="card-body">
                        <p>Add New Users</p>
                        <div>

                            <form class="flex flex-col max-w-md" action="{{ route('site.database.create.user', $siteId) }}" method="post">
                                @csrf
                                <input type="text" name="username" class="form-control mb-4" placeholder="username">
                                <input type="password" name="password" class="form-control mb-4" placeholder="password">
                                <input type="password" name="password_confirmation" class="form-control mb-4"
                                    placeholder="Confirm password">
                                <button class="btn btn-primary">Create User</button>
                            </form>
                            <div class="space"></div>
                        </div>

                    </div>
                </div>

                <div class="card mb-4 col-span-2 md:col-span-1">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        Add User to Database
                    </div>
                    <div class="card-body">
                        {{-- <p>Add New Users</p> --}}
                        <div>

                            <form class="flex flex-col max-w-md" action="{{ route('site.database.create.link', $siteId) }}" method="post">
                                @csrf
                                <label for="" class="">User</label>
                                <select name="user" class="form-control mb-4" id="username">
                                    @foreach ($databaseUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}</option>
                                    @endforeach
                                </select>

                                <label for="">Database</label>
                                <select name="database" class="form-control mb-4" id="database_name">
                                    @foreach ($databases as $database)
                                        <option value="{{ $database->id }}">{{ $database->database_name }}</option>
                                    @endforeach
                                </select>

                                <button class="btn btn-primary">Add</button>
                            </form>
                            <div class="space"></div>
                        </div>

                    </div>
                </div>

                <div class="card mb-4 col-span-2 md:col-span-1">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        List of MYSQL Users
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    {{-- <th scope="col">#</th> --}}
                                    <th scope="col">Username</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($databaseUsers as $user)
                                    <tr>
                                        <td>{{ $user->username }}</td>
                                    </tr>
                                    {{-- <option value="{{ $user->id }}">{{ $user->username }}</option> --}}
                                @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>
    
                <div class="card mb-4 col-span-2">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        List of Databases
                    </div>
                    <div class="card-body">
                        <table class="table border w-full">
                            <thead>
                                <tr>
                                    <th class="border" scope="col">Database Name</th>
                                    <th class="border" scope="col">Database Usernames</th>
                                    {{-- <th scope="col">Handle</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @if ($databases->count() > 0)
                                    @foreach ($databases as $database)
                                        <tr>
                                            <td class="border text-center">
                                                @if (!$database->database_name == "")
                                                    {{ $database->database_name }}
                                                @else
                                                    <span class="text-muted">No Database</span>
                                                @endif
                                            </td>
                                            <td class="border text-center">
                                                @if ($database->users)
                                                    @foreach ($database->users as $user)
                                                        {{ $user->username }} <br/>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No Mysqluser</span>
                                                @endif

                                            </td>
                                            {{-- <td>@mdo</td> --}}
                                        </tr>
                                        {{-- <option value="{{ $user->id }}">{{ $user->username }}</option> --}}
                                    @endforeach
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </body>
@endsection