<?php

namespace App\Livewire\Email;

use App\Services\EmailService;
use Livewire\Component;

class EmailAutoresponderManager extends Component
{
    public $accountId;

    public $account;

    public $autoresponder;

    public $showCreateModal = false;

    public $subject = '';

    public $message = '';

    public $start_date = '';

    public $end_date = '';

    public $is_active = true;

    protected function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'subject.required' => 'Subject is required.',
        'message.required' => 'Message is required.',
        'message.max' => 'Message cannot exceed 5000 characters.',
        'start_date.required' => 'Start date is required.',
        'start_date.after_or_equal' => 'Start date must be today or later.',
        'end_date.after' => 'End date must be after start date.',
    ];

    /**
     * Mount the component.
     */
    public function mount(string $accountId, EmailService $emailService): void
    {
        $this->accountId = $accountId;
        $this->loadAutoresponder($emailService);
    }

    /**
     * Load autoresponder data.
     */
    public function loadAutoresponder(EmailService $emailService): void
    {
        $this->account = $emailService->getEmailAccount($this->accountId);

        if (! $this->account) {
            abort(404, 'Email account not found');
        }

        $this->autoresponder = $this->account->autoresponder()->active()->first();

        if ($this->autoresponder) {
            $this->subject = $this->autoresponder->subject;
            $this->message = $this->autoresponder->message;
            $this->start_date = $this->autoresponder->start_date?->format('Y-m-d');
            $this->end_date = $this->autoresponder->end_date?->format('Y-m-d');
            $this->is_active = $this->autoresponder->is_active;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.email.email-autoresponder-manager');
    }

    /**
     * Show create/edit modal.
     */
    public function showModal(): void
    {
        if ($this->autoresponder) {
            $this->subject = $this->autoresponder->subject;
            $this->message = $this->autoresponder->message;
            $this->start_date = $this->autoresponder->start_date?->format('Y-m-d');
            $this->end_date = $this->autoresponder->end_date?->format('Y-m-d');
            $this->is_active = $this->autoresponder->is_active;
        } else {
            // Set defaults for new autoresponder
            $this->start_date = now()->format('Y-m-d');
            $this->end_date = now()->addDays(7)->format('Y-m-d');
            $this->subject = 'Out of Office';
            $this->message = "Thank you for your email. I am currently out of office and will respond to your message when I return.\n\nBest regards";
            $this->is_active = true;
        }

        $this->showCreateModal = true;
    }

    /**
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showCreateModal = false;
    }

    /**
     * Save autoresponder.
     */
    public function save(EmailService $emailService): void
    {
        $this->validate();

        try {
            $emailService->setAutoresponder($this->accountId, [
                'subject' => $this->subject,
                'message' => $this->message,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', $this->autoresponder ? 'Autoresponder updated successfully.' : 'Autoresponder created successfully.');

            $this->loadAutoresponder($emailService);
            $this->closeModals();

            $this->dispatch('autoresponder-saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving autoresponder: '.$e->getMessage());
        }
    }

    /**
     * Disable autoresponder.
     */
    public function disable(EmailService $emailService): void
    {
        if (! $this->autoresponder) {
            return;
        }

        try {
            $emailService->setAutoresponder($this->accountId, [
                'subject' => $this->autoresponder->subject,
                'message' => $this->autoresponder->message,
                'start_date' => $this->autoresponder->start_date,
                'end_date' => $this->autoresponder->end_date,
                'is_active' => false,
            ]);

            session()->flash('success', 'Autoresponder disabled successfully.');

            $this->loadAutoresponder($emailService);

            $this->dispatch('autoresponder-disabled');
        } catch (\Exception $e) {
            session()->flash('error', 'Error disabling autoresponder: '.$e->getMessage());
        }
    }

    /**
     * Refresh component.
     */
    public function refresh(EmailService $emailService): void
    {
        $this->loadAutoresponder($emailService);
    }
}
