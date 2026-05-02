<?php

namespace App\Livewire\Email;

use App\Models\Site;
use App\Services\EmailService;
use Livewire\Component;

class EmailDkimManager extends Component
{
    public Site $site;

    public $dkimKey;

    public $spfRecord;

    public $dmarcRecord;

    public $showSetupModal = false;

    public $isSettingUpDkim = false;

    /**
     * Mount the component.
     */
    public function mount(Site $site, EmailService $emailService): void
    {
        $this->site = $site;
        $this->loadDnsRecords($emailService);
    }

    /**
     * Load DNS records.
     */
    public function loadDnsRecords(EmailService $emailService): void
    {
        // Get DKIM key if exists
        $this->dkimKey = $this->site->emailDkimKeys()->first();

        // Generate SPF record
        $this->spfRecord = $emailService->generateSPFRecord($this->site);

        // Generate DMARC record
        $this->dmarcRecord = $emailService->generateDMARCRecord($this->site);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.email.email-dkim-manager');
    }

    /**
     * Setup DKIM.
     */
    public function setupDkim(EmailService $emailService): void
    {
        $this->isSettingUpDkim = true;

        try {
            $dkimKey = $emailService->setupDKIM($this->site->id);

            session()->flash('success', 'DKIM setup initiated. The DNS record will be available shortly.');

            $this->loadDnsRecords($emailService);
            $this->closeModals();

            $this->dispatch('dkim-setup');
        } catch (\Exception $e) {
            session()->flash('error', 'Error setting up DKIM: '.$e->getMessage());
        } finally {
            $this->isSettingUpDkim = false;
        }
    }

    /**
     * Show setup modal.
     */
    public function showSetup(): void
    {
        $this->showSetupModal = true;
    }

    /**
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showSetupModal = false;
    }

    /**
     * Refresh DNS records.
     */
    public function refresh(EmailService $emailService): void
    {
        $this->loadDnsRecords($emailService);
    }

    /**
     * Copy text to clipboard helper.
     */
    public function copyToClipboard(string $text): void
    {
        $this->dispatch('copy-to-clipboard', text: $text);
    }
}
