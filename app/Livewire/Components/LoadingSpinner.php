<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LoadingSpinner extends Component
{
    public string $size = 'md'; // sm, md, lg, xl

    public string $color = 'blue'; // blue, green, red, yellow, gray

    public string $message = '';

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.components.loading-spinner');
    }
}
