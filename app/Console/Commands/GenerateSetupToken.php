<?php

namespace App\Console\Commands;

use App\Http\Controllers\SetupController;
use Illuminate\Console\Command;

class GenerateSetupToken extends Command
{
    protected $signature = 'spikster:setup-token';
    protected $description = 'Generate a one-time setup token for first-run wizard';

    public function handle(): void
    {
        $token = SetupController::generateToken();
        $url = config('app.url') . '/setup/' . $token;

        $this->line('');
        $this->line('***********************************************************');
        $this->line('                  SPIKSTER SETUP LINK');
        $this->line('***********************************************************');
        $this->line('');
        $this->line("  $url");
        $this->line('');
        $this->line('  Open this link to set up your admin account.');
        $this->line('  Valid for 24 hours, one-time use only.');
        $this->line('');
        $this->line('***********************************************************');
    }
}
