<?php

namespace Modules\Greeter\Services;

class GreeterService
{
    public function filterGreeting(string $greeting): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            $greeting = str_replace('Hello', 'Good morning', $greeting);
        } elseif ($hour < 18) {
            $greeting = str_replace('Hello', 'Good afternoon', $greeting);
        } else {
            $greeting = str_replace('Hello', 'Good evening', $greeting);
        }

        return $greeting;
    }
}
