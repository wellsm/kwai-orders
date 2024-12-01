<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Admin      = 'admin';
    case User       = 'user';
    
    public const ROLES = [
        self::SuperAdmin->value  => 3,
        self::Admin->value       => 2,
        self::User->value        => 1,
    ];
    
    public const ROLES_OPTIONS = [
        self::SuperAdmin->value  => 'Super Administrador',
        self::Admin->value       => 'Administrador',
        self::User->value        => 'Usuário',
    ];

    public function allowed(self $role): bool
    {
        return self::ROLES[$this->value] >= self::ROLES[$role->value];
    }
}
