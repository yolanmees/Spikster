@if (session()->has('success'))
    <x-alert type="success" :dismissible="true" class="mb-4">{{ session('success') }}</x-alert>
@endif
@if (session()->has('error'))
    <x-alert type="error" :dismissible="true" class="mb-4">{{ session('error') }}</x-alert>
@endif
@if (session()->has('warning'))
    <x-alert type="warning" :dismissible="true" class="mb-4">{{ session('warning') }}</x-alert>
@endif
@if (session()->has('info'))
    <x-alert type="info" :dismissible="true" class="mb-4">{{ session('info') }}</x-alert>
@endif
