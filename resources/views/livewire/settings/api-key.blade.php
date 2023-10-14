<div class="flex flex-col space-y-4">
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    API endpoint: <b>{{ $api_endpoint }}</b>
    @if ($show_api_key)
        <div>
            <label for="api_key" class="block text-sm font-medium text-gray-700">
                API Key
            </label>
            <div class="mt-1">
                <input wire:model="api_key" type="text" name="api_key" id="api_key" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" readonly>
            </div>
        </div>
    @endif
    <div clas="flex space-x-2 my-4">
        <button wire:click="generateApiKey" class="btn btn-primary">
            Generate API Key
        </button>
        <a type="button" class="btn btn-primary" href="{{ $api_endpoint }}/docs" target="_blank">
            API Documentation
        </a>  
    </div>
</div>
