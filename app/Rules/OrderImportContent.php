<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderImportContent implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $iterator = new \ArrayIterator(explode(PHP_EOL, $value));
        $line     = $iterator->offsetGet($iterator->count() - 1);

        if (!str_contains($line, 'Liquidado')) {
            $fail('Você copiou mais caracteres que o permitido no seu celular' . PHP_EOL . 'A ultima linha precisa conter o termo `Liquidado`');
        }
    }
}
