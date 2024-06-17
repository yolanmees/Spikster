{{-- @props(['disabled' => false])

{{-- <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm']) !!}> --}}

@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'prefix' => '',
    'placeholder' => '',
    'value' => '',
    'error' => null,
    'success' => null
])

<div class="mb-8">
    @if($label)
        <label for="{{ $name }}" class="mb-[10px] block text-base font-medium text-gray-900 dark:text-white">
            {{ $label }}
        </label>
    @endif
    <div class="relative">
        @if($prefix)
            <div class="flex items-center">
                <span class="full rounded-l border border-r-0 py-2  border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700 py-[10px] px-4 text-base uppercase text-gray-900 dark:text-gray-300">
                    {{ $prefix }}
                </span>
                <input {{ $attributes->merge(['class' => 'w-full bg-transparent rounded-br-md rounded-tr-md border border-gray-300 dark:border-gray-600 py-[10px] pr-3 pl-5 text-gray-900 dark:text-gray-300 outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-200' . ($error ? ' border-red-500' : '') . ($success ? 'border-green-500' : '')]) }} type="text" name="{{ $name }}" placeholder="{{ $placeholder }}" value="{{ old($name, $value) }}">
            </div>
        @else
            <input {{ $attributes->merge(['class' => 'w-full bg-transparent rounded-md border border-gray-300 dark:border-gray-600 py-[10px] px-5 text-gray-900 dark:text-gray-300 outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-200' . ($error ? ' border-red-500' : '') . ($success ? 'border-green-500' : '')]) }} type="{{ $type }}" name="{{ $name }}" placeholder="{{ $placeholder }}" value="{{ old($name, $value) }}">
            @if($error)
                <span class="absolute top-1/2 right-4 -translate-y-1/2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.9987 2.50065C5.85656 2.50065 2.4987 5.85852 2.4987 10.0007C2.4987 14.1428 5.85656 17.5007 9.9987 17.5007C14.1408 17.5007 17.4987 14.1428 17.4987 10.0007C17.4987 5.85852 14.1408 2.50065 9.9987 2.50065ZM0.832031 10.0007C0.832031 4.93804 4.93609 0.833984 9.9987 0.833984C15.0613 0.833984 19.1654 4.93804 19.1654 10.0007C19.1654 15.0633 15.0613 19.1673 9.9987 19.1673C4.93609 19.1673 0.832031 15.0633 0.832031 10.0007Z" fill="#DC3545" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0013 5.83398C10.4615 5.83398 10.8346 6.20708 10.8346 6.66732V10.0007C10.8346 10.4609 10.4615 10.834 10.0013 10.834C9.54106 10.834 9.16797 10.4609 9.16797 10.0007V6.66732C9.16797 6.20708 9.54106 5.83398 10.0013 5.83398Z" fill="#DC3545" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.16797 13.3333C9.16797 12.8731 9.54106 12.5 10.0013 12.5H10.0096C10.4699 12.5 10.843 12.8731 10.843 13.3333C10.843 13.7936 10.4699 14.1667 10.0096 14.1667H10.0013C9.54106 14.1667 9.16797 13.7936 9.16797 13.3333Z" fill="#DC3545" />
                    </svg>
                </span>
            @elseif($success)
                <span class="absolute top-1/2 right-4 -translate-y-1/2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M13.0512 3.14409C11.5739 2.48584 9.9234 2.32277 8.34584 2.6792C6.76829 3.03562 5.34821 3.89245 4.29741 5.12189C3.2466 6.35133 2.62137 7.88751 2.51496 9.50132C2.40854 11.1151 2.82665 12.7201 3.70692 14.0769C4.58719 15.4337 5.88246 16.4695 7.39955 17.03C8.91664 17.5905 10.5743 17.6456 12.1252 17.187C13.6762 16.7284 15.0373 15.7808 16.0057 14.4855C16.9741 13.1901 17.4978 11.6164 17.4987 9.99909V9.2329C17.4987 8.77266 17.8718 8.39956 18.332 8.39956C18.7923 8.39956 19.1654 8.77266 19.1654 9.2329V9.99956C19.1642 11.9763 18.5242 13.9002 17.3406 15.4834C16.157 17.0666 14.4934 18.2248 12.5978 18.7853C10.7022 19.3457 8.67619 19.2784 6.82196 18.5934C4.96774 17.9084 3.38463 16.6423 2.30875 14.984C1.23286 13.3257 0.72184 11.3641 0.851902 9.39166C0.981963 7.41922 1.74614 5.54167 3.03045 4.03902C4.31477 2.53637 6.05042 1.48914 7.97854 1.05351C9.90666 0.617872 11.9239 0.817181 13.7295 1.62171C14.1499 1.80902 14.3389 2.30167 14.1516 2.72206C13.9642 3.14246 13.4716 3.3314 13.0512 3.14409Z" fill="#22AD5C" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18.9236 2.74378C19.2492 3.06906 19.2495 3.59669 18.9242 3.92229L10.5909 12.264C10.4346 12.4204 10.2226 12.5083 10.0015 12.5083C9.78042 12.5084 9.56838 12.4206 9.41205 12.2643L6.91205 9.76426C6.58661 9.43882 6.58661 8.91118 6.91205 8.58574C7.23748 8.26031 7.76512 8.26031 8.09056 8.58574L10.001 10.4962L17.7451 2.74437C18.0704 2.41877 18.598 2.41851 18.9236 2.74378Z" fill="#22AD5C" />
                    </svg>
                </span>
            @endif
        @endif
    </div>
    @if($error)
        <p class="mt-[10px] text-sm text-red-700">{{ $error }}</p>
    @elseif($success)
        <p class="mt-[10px] text-sm text-green-800">{{ $success }}</p>
    @endif
</div>