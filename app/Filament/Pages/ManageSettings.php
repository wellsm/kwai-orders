<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Support\Enums\MaxWidth;
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
                Section::make([
                    Toggle::make('registration')
                        ->label('Habilitar Registro?')
                ]),
            ]);
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Configurações';
    }

    public static function canAccess(): bool
    {
        /** @var User */
        $user = Auth::user();

        return $user->isRole(Role::SuperAdmin);
    }
}
