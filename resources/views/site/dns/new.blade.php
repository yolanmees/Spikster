@extends('layouts.app')

@section('content')

    <div class="mt-8">
        <div class="mb-4 flex justify-start">
            <a class="btn btn-primary" type="button" href="{{ route('site.dns', ['site_id' => $site_id]) }}">
                Back
            </a>
        </div>
        <!-- Form Container -->
        <div class="mx-auto bg-white p-4 dark:bg-gray-800 shadow-md rounded">
            <div class="mb-4">
                <h1 class="text-gray-900 dark:text-gray-100 font-bold text-xl mb-2">Add New DNS Record</h1>
            </div>

            <!-- show DNS form errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Whoops!</strong>
                    <span class="block sm:inline">{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Add DNS Record Form -->
            <form method="POST" action="{{ route('site.dns.create', ['site_id' => $site_id]) }}">
                @csrf
                <!-- Host Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="host">
                        Host
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-900 dark:border-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="zone" id="zone" type="text" placeholder="e.g., subdomain.example.com">
                </div>

                <!-- TTL Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="ttl">
                        TTL
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-900 dark:border-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="ttl" id="ttl" type="text" placeholder="Time To Live">
                </div>

                <!-- Record Type Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="record_type">
                        Record Type
                    </label>
                    <select class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-900 dark:border-gray-700 bg-white dark:bg-gray-800 focus:outline-none focus:shadow-outline" id="record_type" name="type">
                        <option>A</option>
                        <option>AAAA</option>
                        <option>CNAME</option>
                        <option>MX</option>
                        <option>TXT</option>
                        <!-- Add other record types as necessary -->
                    </select>
                </div>

                <!-- Value Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="value">
                        Value
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-900 dark:border-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="value" id="value" type="text" placeholder="Record Value">
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button class="btn btn-primary" type="submit">
                        Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection