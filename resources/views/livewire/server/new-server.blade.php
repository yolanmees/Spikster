<div>
    <dialog class="modal fade" id="newServerModal" tabindex="-1" role="dialog" aria-labelledby="newServerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" id="newserverdialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newServerModalLabel">{{ __('spikster.create_server_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 space-x-4" id="newserverform">
                        <div class="flex flex-col ml-4">
                            <label for="newservername">{{ __('spikster.server_name') }}</label>
                            <input class="form-control"  type="text"  wire:model="serverName" id="newservername" placeholder="e.g. Production" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverip">{{ __('spikster.server_ip') }}</label>
                            <input class="form-control" type="text"  wire:model="serverIp" id="newserverip" placeholder="e.g. 123.45.67.89" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverip">Server SSH port</label>
                            <input class="form-control" type="text" wire:model="serverSshPort" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverip">Server SSH Password</label>
                            <input class="form-control" type="text" wire:model="serverSshPassword" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverprovider">{{ __('spikster.server_provider') }}</label>
                            <input class="form-control" type="text" wire:model="serverProvider" id="newserverprovider" placeholder="e.g. Digital Ocean" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverlocation">{{ __('spikster.server_location') }}</label>
                            <input class="form-control" type="text"  wire:model="serverLocation" id="newserverlocation" placeholder="e.g. Amsterdam" autocomplete="off" />
                        </div>
                        <div class="flex flex-col">
                            <label for="newserverlocation">Server API key</label>
                            <input class="form-control" type="text"  wire:model="serverApiKey" id="newserverapikey" placeholder="dseswdhnhbXXXXXXX" autocomplete="off" />
                        </div>
                        <div class="space"></div>
                        <div class="flex flex-col py-4">
                            <button wire:click="submit()" class="btn btn-primary" type="button" id="submit">{{ __('spikster.confirm') }} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
</div>
