<?php

namespace Modules\Greeter\Livewire;

use App\Services\ModuleHookManager;
use Livewire\Component;
use Modules\Greeter\Models\Greeting;

class Greeter extends Component
{
    public string $name = '';

    public string $greeting = 'Hello!';

    public bool $showHistory = false;

    public function greet(): void
    {
        $displayName = trim($this->name) ?: 'World';
        $greeting = "Hello, {$displayName}! Welcome to Spikster.";

        $this->greeting = app(ModuleHookManager::class)
            ->applyFilter('greeter.greeting', $greeting);

        Greeting::create([
            'name' => $displayName,
            'message' => $this->greeting,
        ]);
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
    }

    public function render()
    {
        return view('greeter::livewire.greeter', [
            'greetings' => $this->showHistory
                ? Greeting::latest()->take(10)->get()
                : collect(),
        ]);
    }
}
