<?php

namespace App\Rules;

use App\Services\Team\Kwai;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KwaiProfile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = (new Kwai())->get($value);

        if (empty($response)) {
            $fail('Perfil não encontrado, verifique a URL informada');
        }
    }
}
