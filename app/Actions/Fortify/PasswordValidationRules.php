<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Validation\Rule;
use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            (new Password)
                ->length(12)
                ->requireUppercase()
                ->requireNumeric()
                ->requireSpecialCharacter(),
            'confirmed',
        ];
    }
}
