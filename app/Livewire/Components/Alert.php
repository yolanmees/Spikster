<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Alert extends Component
{
    public string $type = 'info'; // success, error, warning, info

    public string $message = '';

    public bool $dismissible = true;

    public bool $show = true;

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.components.alert');
    }

    /**
     * Dismiss the alert.
     */
    public function dismiss(): void
    {
        $this->show = false;
    }
}
