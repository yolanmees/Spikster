@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid box border p-5 w-full md:gap-6']) }}>
    <div class="flex items-center w-full">
        <div class="font-medium text-base truncate w-full">

          <div class="border-b border-slate-200/60 dark:border-darkmode-400 pb-5">
            <x-section-title>
                <x-slot name="title">{{ $title }}</x-slot>
                <x-slot name="description">{{ $description }}</x-slot>
            </x-section-title>
          </div>


            <div class="mt-5 md:mt-0 md:col-span-2">
                <form wire:submit.prevent="{{ $submit }}">
                    <div class="py-5 bg-white {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                        <div class="grid grid-cols-6 gap-6">
                            {{ $form }}
                        </div>
                    </div>

                    @if (isset($actions))
                    <div class="flex items-left justify-start text-left sm:rounded-bl-md sm:rounded-br-md">
                        {{ $actions }}
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
