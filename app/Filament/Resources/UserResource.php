<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->disabled(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->disabled(),
                Select::make('role')
                    ->options(Role::ROLES_OPTIONS)
                    ->selectablePlaceholder(false),
                Select::make('teams')
                    ->multiple()
                    ->relationship(name: 'teams', titleAttribute: 'name')
                    ->preload()
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var User */
        $user = Auth::user();

        return $table
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->pluralModelLabel('Usuários')
            ->modelLabel('Usuário')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->badge()
                    ->color(Color::Blue),
                TextColumn::make('role')
                    ->label('Papel')
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label('Verificado Em')
                    ->dateTime(),
                TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modelLabel('Usuário')
                    ->visible($user->isRole(Role::SuperAdmin)),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Usuários';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
