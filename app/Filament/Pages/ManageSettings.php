<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $navigationGroup = 'Sistema';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('registration')
                    ->label('Habilitar Registro?')
            ]);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Configurações';
    }

    public static function canAccess(): bool
    {
        return Auth::user()->id === 1;
    }
}
