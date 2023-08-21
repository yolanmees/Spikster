<div {{ $attributes->merge(['class' => 'md:grid box border p-5 w-full']) }}>
  <div class="flex items-center w-full">
      <div class="font-medium text-base truncate w-full">

        <div class="border-b border-slate-200/60 dark:border-darkmode-400 pb-5">
          <x-section-title>
              <x-slot name="title">{{ $title }}</x-slot>
              <x-slot name="description">{{ $description }}</x-slot>
          </x-section-title>
        </div>

          <div class="mt-5 md:mt-0 md:col-span-2">
              <div class="pt-5 bg-white shadow sm:rounded-lg">
                  {{ $content }}
              </div>
          </div>
        </div>
    </div>
</div>
