<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

    @for ($i = 0; $i < count($items['installed'] ?? []); $i++)
        @php
            $packages = $items[array_keys($items)[$i]];
            $installed = $items['installed'][array_keys($items['installed'])[$i]];
        @endphp
        @for ($x = 0; $x < count($packages ?? []); $x++)
            <x-card header="{{ ucfirst($packages[$x]['name']) }}" size="md" dark="false">
                <div class="flex flex-col my-4 gap-y-4">
                    <div class="flex my-4 gap-x-4">
                        @if (array_keys($items['installed'])[$i] == 'packages')
                            @if ($installed[$x]['status'] == 'INSTALLED')
                                <div class="flex-col">
                                    <p class="pb-4 text-green-500">Installed</p>
                                    <x-button variant="danger" size="sm">Uninstall</x-button>
                                </div>
                            @else
                                <x-button variant="primary" size="sm">Install</x-button>
                            @endif
                        @else
                            @if ($installed[$x]['status'] == 'ACTIVE')
                                <div class="flex-col">
                                    <span class="pb-4 text-green-500">Active</span>
                                    <div class="flex mt-4 gap-x-4">
                                        <x-button variant="danger" size="sm">Stop</x-button>
                                        <x-button variant="warning" size="sm">Restart</x-button>
                                    </div>
                                </div>
                            @elseif ($installed[$x]['status'] == 'INACTIVE')
                                <div class="flex-col gap-4">
                                    <span class="pb-4 text-yellow-500">Inactive</span>
                                    <div class="flex-1 mt-4 gap-x-4">
                                        <x-button variant="success" size="sm">Start</x-button>
                                    </div>
                                </div>
                            @else
                                <x-button variant="primary" size="sm">Install</x-button>
                                <x-button variant="danger" size="sm">Uninstall</x-button>
                            @endif
                        @endif
                    </div>
                </div>
            </x-card>
        @endfor
    @endfor
</div>
