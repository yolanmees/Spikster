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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="card mb-4 col-span-2 md:col-span-1">
                    <div class="card-header">
                        <i class="fab fa-github fs-fw mr-1"></i>
                        Create new Wordpress
                    </div>
                    <div class="card-body">
                        <form action="{{ route('wordpress.create') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="path">Installatiepad:</label>
                                <input type="text" id="path" name="path" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="username">WordPress Admin Gebruikersnaam:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">WordPress Admin Wachtwoord:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">WordPress Installeren</button>
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
                    </div>
                </div>
            </div>
        </div>
    </body>
@endsection