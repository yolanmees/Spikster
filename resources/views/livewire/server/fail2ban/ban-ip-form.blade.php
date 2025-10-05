<div class="rounded-lg bg-white shadow">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Ban or Whitelist IP Address</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Manually ban or whitelist an IP address. Banned IPs will be blocked from accessing the server.</p>
        </div>

        @if (session()->has('success'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mt-4 rounded-md bg-red-50 p-4">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="mt-4 rounded-md bg-blue-50 p-4">
                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
            </div>
        @endif

        <form wire:submit="submit" class="mt-5">
            <div class="space-y-4">
                <!-- Action Type -->
                <div>
                    <label class="text-base font-semibold text-gray-900">Action</label>
                    <p class="text-sm text-gray-500">Choose whether to ban or whitelist the IP address</p>
                    <fieldset class="mt-4">
                        <div class="space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                            <div class="flex items-center">
                                <input wire:model.live="action" id="action-ban" name="action" type="radio"
                                    value="ban"
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="action-ban" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                    Ban IP
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input wire:model.live="action" id="action-whitelist" name="action" type="radio"
                                    value="whitelist"
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="action-whitelist"
                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                    Whitelist IP
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <!-- IP Address -->
                <div>
                    <label for="ip" class="block text-sm font-medium leading-6 text-gray-900">IP Address</label>
                    <div class="mt-2 flex gap-2">
                        <input wire:model.live="ip" type="text" name="ip" id="ip"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="192.168.1.100">
                        <button type="button" wire:click="checkIpStatus"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Check Status
                        </button>
                    </div>
                    @error('ip')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jail Selection (only for ban action) -->
                @if ($action === 'ban')
                    <div>
                        <label for="jail" class="block text-sm font-medium leading-6 text-gray-900">Jail</label>
                        <select wire:model="jail" id="jail" name="jail"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @if (!empty($jails))
                                @foreach ($jails as $jailOption)
                                    <option value="{{ $jailOption['name'] }}">{{ $jailOption['name'] }}</option>
                                @endforeach
                            @else
                                <option value="sshd">sshd</option>
                                <option value="nginx-http-auth">nginx-http-auth</option>
                                <option value="nginx-noscript">nginx-noscript</option>
                            @endif
                        </select>
                        @error('jail')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">Select the Fail2ban jail to ban the IP in.</p>
                    </div>
                @endif
            </div>

            <div class="mt-5">
                @if ($action === 'ban')
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Ban IP Address
                    </button>
                @else
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Whitelist IP Address
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
