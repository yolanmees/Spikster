<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    Custom panel domain/subdomain
    <div>
        <input class="" type="text" wire:model="panel_domain" placeholder="control.spikster.com" autocomplete="off" />
        <div class="flex space-x-2 my-4">
            <button class="btn btn-primary" type="button" wire:click="updatePanelDomain()"><i class="fas fa-edit"></i></button>
            <button class="btn btn-primary" type="button" wire:click="sslPanelDomain()" title="{{ __('cipi.panel_url_force_ssl') }}"><i class="fas fa-lock"></i></button>
        </div>  
    </div>

</div>
