<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class GmailAddress implements ValidationRule
{
    /**
     * Accept only Gmail addresses such as name9@gmail.com.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', trim($value))) {
            $fail('The :attribute must be a Gmail address, for example name9@gmail.com.');
        }
    }
}
