<?php

namespace App\Livewire\TwoFactorAuth;

use App\Services\TwoFactorAuthService;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class Enable2FAForm extends Component
{
    public $step = 1; // 1: Generate QR, 2: Verify and Enable

    public $secret;

    public $qrCode;

    public $verificationCode;

    public $recoveryEmail;

    public $backupCodes = [];

    public $showBackupCodes = false;

    protected $rules = [
        'verificationCode' => 'required|string|size:6',
        'recoveryEmail' => 'nullable|email',
    ];

    protected TwoFactorAuthService $twoFactorService;

    public function boot(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    public function mount()
    {
        // Check if 2FA is already enabled
        if ($this->twoFactorService->has2FAEnabled(auth()->user())) {
            session()->flash('error', '2FA is already enabled for your account.');

            return redirect()->route('settings.profile');
        }
    }

    public function generateSecret()
    {
        try {
            $user = auth()->user();
            $this->secret = $this->twoFactorService->generateSecret();
            $this->qrCode = $this->twoFactorService->getQRCode($user, $this->secret);
            $this->step = 2;

            session()->flash('message', 'Scan the QR code with your authenticator app.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate QR code: '.$e->getMessage());
        }
    }

    public function enable2FA()
    {
        $this->validate();

        try {
            // Verify the code before enabling
            $google2fa = new Google2FA;
            $valid = $google2fa->verifyKey($this->secret, $this->verificationCode);

            if (! $valid) {
                session()->flash('error', 'Invalid verification code. Please try again.');

                return;
            }

            $user = auth()->user();
            $this->twoFactorService->enable2FA($user, $this->secret, $this->recoveryEmail);
            $this->backupCodes = $this->twoFactorService->generateBackupCodes($user);

            $this->showBackupCodes = true;
            $this->step = 3;

            session()->flash('message', '2FA has been enabled successfully! Please save your backup codes.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to enable 2FA: '.$e->getMessage());
        }
    }

    public function downloadBackupCodes()
    {
        $content = "Spikster 2FA Backup Codes\n";
        $content .= 'Generated: '.now()->format('Y-m-d H:i:s')."\n";
        $content .= 'User: '.auth()->user()->email."\n\n";
        $content .= "IMPORTANT: Keep these codes in a safe place.\n";
        $content .= "Each code can only be used once.\n\n";

        foreach ($this->backupCodes as $code) {
            $content .= $code."\n";
        }

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'spikster-2fa-backup-codes-'.now()->format('Y-m-d').'.txt');
    }

    public function finish()
    {
        session()->flash('success', '2FA has been successfully configured.');

        return redirect()->route('settings.profile');
    }

    public function render()
    {
        return view('livewire.two-factor-auth.enable2-f-a-form');
    }
}
