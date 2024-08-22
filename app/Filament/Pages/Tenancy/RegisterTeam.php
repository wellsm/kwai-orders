<?php

namespace App\Filament\Pages\Tenancy;
 
use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Criar Time';
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }
 
    protected function handleRegistration(array $data): Team
    {
        $slug = Str::slug($data['name']);
        $team = Team::create(array_merge($data, compact('slug')));
        $team->members()->attach(Auth::user());
 
        return $team;
    }
}