@extends('layouts.app', [

])

@section('content')
  <div class="intro-y grid grid-cols-1 gap-y-4 m-4">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="">
                    @livewire('profile.update-password-form')
                </div>
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="">
                    @livewire('profile.two-factor-authentication-form')
                </div>
            @endif

            <div class="">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div class="">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
  </div>

@endsection
