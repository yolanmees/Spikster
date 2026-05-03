@extends('layouts.app')


@section('title')
    Database
@endsection



@section('content')
    @if (Session::has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-green-800 dark:text-green-200">{{ Session::get('success') }}</p>
        </div>
    @elseif (Session::has('failed'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-800 dark:text-red-200">{{ Session::get('failed') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Create Database Card -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    Create new Database
                </div>
            </x-slot>
            <form class="space-y-4" action="{{ route('site.database.create.database', $siteId) }}" method="post">
                @csrf
                <input type="text" name="database_name"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                    placeholder="database name">
                <button
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                    Create database
                </button>
            </form>
        </x-card>

        <!-- MySQL Users Card -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    MYSQL Users
                </div>
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Add New Users</p>
            <form class="space-y-4" action="{{ route('site.database.create.user', $siteId) }}" method="post">
                @csrf
                <input type="text" name="username"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                    placeholder="username">
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                    placeholder="password">
                <input type="password" name="password_confirmation"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                    placeholder="Confirm password">
                <button
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                    Create User
                </button>
            </form>
        </x-card>

        <!-- Add User to Database Card -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Add User to Database
                </div>
            </x-slot>
            <form class="space-y-4" action="{{ route('site.database.create.link', $siteId) }}" method="post">
                @csrf
                <div>
                    <label for="username"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User</label>
                    <select name="user"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                        id="username">
                        @foreach ($databaseUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="database_name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database</label>
                    <select name="database"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                        id="database_name">
                        @foreach ($databases as $database)
                            <option value="{{ $database->id }}">{{ $database->database_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                    Add
                </button>
            </form>
        </x-card>

        <!-- List of MySQL Users Card -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    List of MYSQL Users
                </div>
            </x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">Username</th>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($databaseUsers as $user)
                            <tr
                                class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $user->username }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('site.database.delete.user', $siteId) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button
                                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-all duration-200">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
        <!-- List of Databases Card - Full Width -->
        <div class="lg:col-span-2">
            <x-card size="lg" dark="false">
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        List of Databases
                    </div>
                </x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">Database Name</th>
                                <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">Database Usernames</th>
                                <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($databases->count() > 0)
                                @foreach ($databases as $database)
                                    <tr
                                        class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            @if (!$database->database_name == '')
                                                {{ $database->database_name }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">No Database</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            @if ($database->users)
                                                @foreach ($database->users as $user)
                                                    {{ $user->username }} <br />
                                                @endforeach
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">No Mysqluser</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <form action="{{ route('site.database.delete.database', $siteId) }}"
                                                method="post">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="database_id" value="{{ $database->id }}">
                                                <button
                                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-all duration-200">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
