<?php

namespace App\Rules;

use App\Models\Team;
use App\Services\Team\Kwai;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Translation\PotentiallyTranslatedString;

class KwaiProfile implements ValidationRule
{
    public function __construct(
        private readonly Team|null $team = null
    ) { }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = (new Kwai())->get($value);

        if (empty($response)) {
            $fail('Perfil não encontrado, verifique a URL informada');
        }

        $team = Team::query()
            ->where('username', $response->getUsername())
            ->when($this->team, fn (Builder $query) => $query->whereNot('id', $this->team->getId()))
            ->first();

        if (!empty($team)) {
            $fail('Perfil já cadastrado por outro usuário');
        }
    }
}
