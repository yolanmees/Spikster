<?php

namespace App\Livewire\TwoFactorAuth;

use App\Services\TwoFactorAuthService;
use Livewire\Component;

class Disable2FAForm extends Component
{
    public $verificationCode;
    public $confirmDisable = false;

    protected $rules = [
        'verificationCode' => 'required|string',
    ];

    protected TwoFactorAuthService $twoFactorService;

    public function boot(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function mount()
    {
        // Check if 2FA is enabled
        if (!$this->twoFactorService->has2FAEnabled(auth()->user())) {
            session()->flash('error', '2FA is not enabled for your account.');
            return redirect()->route('settings.profile');
        }
    }

    public function disable2FA()
    {
        $this->validate();

        try {
            $user = auth()->user();

            // Verify the code before disabling
            if (!$this->twoFactorService->verify2FA($user, $this->verificationCode)) {
                session()->flash('error', 'Invalid verification code. Please try again.');
                return;
            }

            $this->twoFactorService->disable2FA($user);

            session()->flash('success', '2FA has been disabled successfully.');
            return redirect()->route('settings.profile');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to disable 2FA: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.two-factor-auth.disable2-f-a-form');
    }
}
