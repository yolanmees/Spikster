<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Modal extends Component
{
    public bool $show = false;

    public string $title = '';

    public string $size = 'md'; // sm, md, lg, xl, full

    public bool $closeable = true;

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.components.modal');
    }

    /**
     * Close the modal.
     */
    public function close(): void
    {
        if ($this->closeable) {
            $this->show = false;
            $this->dispatch('modal-closed');
        }
    }

    /**
     * Open the modal.
     */
    public function open(): void
    {
        $this->show = true;
        $this->dispatch('modal-opened');
    }
}
