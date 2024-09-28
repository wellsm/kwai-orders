<?php

namespace App\Enums;

enum Plan: string
{
    case Free  = 'free';
    case Basic = 'basic';
    case Pro   = 'pro';
    case Beta  = 'beta';
    
    public const PLANS = [
        self::Free->value  => 1,
        self::Basic->value => 2,
        self::Pro->value   => 3,
        self::Beta->value  => 4,
    ];

    public function allowed(self $plan): bool
    {
        return self::PLANS[$this->value] >= self::PLANS[$plan->value];
    }
}
