@extends('layouts.app')

@section('content')
       
<ol class="breadcrumbs">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
    <li class="ml-1 breadcrumb-item active">{{ __('spikster.sites') }}:<b><span class="ml-1" id="serversites"></span></b></li>
    <li class="ml-1 breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i class="fas fa-circle-notch fa-spin"></i></span></b></li>
</ol>


<hr class="my-8" />

<h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Cards</h1>

<div class="flex flex-col gap-y-4 my-4">
    <x-card header="Card Header" size="md" dark="false">
        Card body content goes here.
    </x-card>

    <x-card header="Card Header" size="lg" dark="true">
        Card body content goes here.
    </x-card>

    <x-card size="sm" dark="true">
        Card body without header.
    </x-card>
</div>






<hr class="my-8" />

<h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Buttons</h1>

<div class="flex gap-x-4 my-4">
    <x-button variant="primary">Primary</x-button>
    <x-button variant="secondary">Secondary</x-button>
    <x-button variant="success">Success</x-button>
    <x-button variant="danger">Danger</x-button>
    <x-button variant="warning">Warning</x-button>
    <x-button variant="info">Info</x-button>
    <x-button variant="light">Light</x-button>
    <x-button variant="dark">Dark</x-button>
    <x-button variant="link">Link</x-button>
</div>

<div class="flex gap-x-4 my-4">
    <x-button variant="primary" outline="true">Primary Outline</x-button>
    <x-button variant="secondary" outline="true">Secondary Outline</x-button>
    <x-button variant="success" outline="true">Success Outline</x-button>
    <x-button variant="danger" outline="true">Danger Outline</x-button>
    <x-button variant="warning" outline="true">Warning Outline</x-button>
    <x-button variant="info" outline="true">Info Outline</x-button>
    <x-button variant="light" outline="true">Light Outline</x-button>
    <x-button variant="dark" outline="true">Dark Outline</x-button>
</div>

<div class="flex gap-x-4 my-4">
    <x-button variant="primary" size="sm">Small Primary</x-button>
    <x-button variant="secondary" size="md">Medium Secondary</x-button>
    <x-button variant="success" size="lg">Large Success</x-button>
    <x-button variant="danger" size="sm" outline="true">Small Danger Outline</x-button>
    <x-button variant="warning" size="md" outline="true">Medium Warning Outline</x-button>
    <x-button variant="info" size="lg" outline="true">Large Info Outline</x-button>
</div>








<hr class="my-8" />

<h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tabs</h1>

<x-tabs :tabs="[
    ['name' => 'Monitor', 'url' => '#monitor'],
    ['name' => 'Server information', 'url' => '#server-information'],
    ['name' => 'Security', 'url' => '#security'],
    ['name' => 'Billing', 'url' => '#billing'],
]" activeTab="Security" selectName="tabs">
</x-tabs>



<hr class="my-8" />

<h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Inputs</h1>

<x-input
    type="text"
    name="name"
    label="Name"
    placeholder="Devid Jhon" />

<x-input
    type="email"
    name="email"
    label="Email"
    placeholder="info@yourmail.com" />

<x-input
    type="text"
    name="company_name"
    label="Company Name"
    placeholder="Pimjo Labs" />

<x-input
    type="text"
    name="currency"
    label="Currency"
    prefix="USD"
    placeholder="Pimjo Labs" />

<x-input
    type="email"
    name="invalid_email"
    label="Email"
    placeholder="Devid Jhon"
    error="Invalid email address" />

<x-input
    type="email"
    name="valid_email"
    label="Email"
    placeholder="Devid Jhon"
    success="Password is strong">
    <span class="absolute top-1/2 left-4 -translate-y-1/2">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g opacity="0.8">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.16797 10.0007C3.70773 10.0007 3.33464 10.3737 3.33464 10.834V16.6673C3.33464 17.1276 3.70773 17.5007 4.16797 17.5007H15.8346C16.2949 17.5007 16.668 17.1276 16.668 16.6673V10.834C16.668 10.3737 16.2949 10.0007 15.8346 10.0007H4.16797ZM1.66797 10.834C1.66797 9.45327 2.78726 8.33398 4.16797 8.33398H15.8346C17.2153 8.33398 18.3346 9.45327 18.3346 10.834V16.6673C18.3346 18.048 17.2153 19.1673 15.8346 19.1673H4.16797C2.78726 19.1673 1.66797 18.048 1.66797 16.6673V10.834Z" fill="#9CA3AF" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M10 2.50065C9.11594 2.50065 8.2681 2.85184 7.64298 3.47696C7.01786 4.10208 6.66667 4.94993 6.66667 5.83398V9.16732C6.66667 9.62756 6.29357 10.0007 5.83333 10.0007C5.3731 10.0007 5 9.62756 5 9.16732V5.83398C5 4.5079 5.52678 3.23613 6.46447 2.29845C7.40215 1.36077 8.67392 0.833984 10 0.833984C11.3261 0.833984 12.5979 1.36077 13.5355 2.29845C14.4732 3.23613 15 4.5079 15 5.83398V9.16732C15 9.62756 14.6269 10.0007 14.1667 10.0007C13.7064 10.0007 13.3333 9.62756 13.3333 9.16732V5.83398C13.3333 4.94993 12.9821 4.10208 12.357 3.47696C11.7319 2.85184 10.8841 2.50065 10 2.50065Z" fill="#9CA3AF" />
            </g>
        </svg>
    </span>
    <span class="absolute top-1/2 right-4 -translate-y-1/2">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.0512 3.14409C11.5739 2.48584 9.9234 2.32277 8.34584 2.6792C6.76829 3.03562 5.34821 3.89245 4.29741 5.12189C3.2466 6.35133 2.62137 7.88751 2.51496 9.50132C2.40854 11.1151 2.82665 12.7201 3.70692 14.0769C4.58719 15.4337 5.88246 16.4695 7.39955 17.03C8.91664 17.5905 10.5743 17.6456 12.1252 17.187C13.6762 16.7284 15.0373 15.7808 16.0057 14.4855C16.9741 13.1901 17.4978 11.6164 17.4987 9.99909V9.2329C17.4987 8.77266 17.8718 8.39956 18.332 8.39956C18.7923 8.39956 19.1654 8.77266 19.1654 9.2329V9.99956C19.1642 11.9763 18.5242 13.9002 17.3406 15.4834C16.157 17.0666 14.4934 18.2248 12.5978 18.7853C10.7022 19.3457 8.67619 19.2784 6.82196 18.5934C4.96774 17.9084 3.38463 16.6423 2.30875 14.984C1.23286 13.3257 0.72184 11.3641 0.851902 9.39166C0.981963 7.41922 1.74614 5.54167 3.03045 4.03902C4.31477 2.53637 6.05042 1.48914 7.97854 1.05351C9.90666 0.617872 11.9239 0.817181 13.7295 1.62171C14.1499 1.80902 14.3389 2.30167 14.1516 2.72206C13.9642 3.14246 13.4716 3.3314 13.0512 3.14409Z" fill="#22AD5C"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M18.9236 2.74378C19.2492 3.06906 19.2495 3.59669 18.9242 3.92229L10.5909 12.264C10.4346 12.4204 10.2226 12.5083 10.0015 12.5083C9.78042 12.5084 9.56838 12.4206 9.41205 12.2643L6.91205 9.76426C6.58661 9.43882 6.58661 8.91118 6.91205 8.58574C7.23748 8.26031 7.76512 8.26031 8.09056 8.58574L10.001 10.4962L17.7451 2.74437C18.0704 2.41877 18.598 2.41851 18.9236 2.74378Z" fill="#22AD5C" />
        </svg>
    </span>
</x-input>

@endsection