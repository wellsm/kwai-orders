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
                TextInput::make('name')
                    ->required()
                    ->live(true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->disabled()
                    ->required()
                    ->unique(Team::class, 'slug')
                    ->validationMessages([
                        'unique' => 'O nome informado já está sendo utilizado'
                    ]),
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