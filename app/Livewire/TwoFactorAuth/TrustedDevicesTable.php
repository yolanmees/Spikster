<?php

namespace App\Livewire\TwoFactorAuth;

use App\Services\TwoFactorAuthService;
use Livewire\Component;

class TrustedDevicesTable extends Component
{
    public $devices = [];

    protected TwoFactorAuthService $twoFactorService;

    public function boot(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function mount()
    {
        $this->loadDevices();
    }

    public function loadDevices()
    {
        $this->devices = auth()->user()
            ->trustedDevices()
            ->active()
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->toArray();
    }

    public function removeDevice($deviceId)
    {
        try {
            $this->twoFactorService->removeTrustedDevice(auth()->user(), $deviceId);
            $this->loadDevices();
            session()->flash('message', 'Device removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to remove device: ' . $e->getMessage());
        }
    }

    public function cleanupExpired()
    {
        try {
            $count = $this->twoFactorService->cleanupExpiredDevices(auth()->user());
            $this->loadDevices();
            session()->flash('message', "Removed $count expired device(s).");
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cleanup devices: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.two-factor-auth.trusted-devices-table');
    }
}
