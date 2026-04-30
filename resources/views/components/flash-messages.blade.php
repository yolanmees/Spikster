@if (session()->has('success'))
    <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
@endif
@if (session()->has('error'))
    <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
@endif
