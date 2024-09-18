<?php

namespace App\Filament\Pages\Tenancy;

use App\Rules\KwaiProfile;
use App\Services\Team\Kwai;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Validation\ValidationException;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Alterar Conta';
    }

    public function form(Form $form): Form
    {
        return $form
            ->extraAttributes(['id' => 'profile-form'])
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->disabled(),
                TextInput::make('username')
                    ->label(__('Nickname'))
                    ->disabled(),
                TextInput::make('url')
                    ->label('URL do Perfil')
                    ->required()
                    ->rules([new KwaiProfile()]),
            ]);
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $response = (new Kwai())
            ->withAvatar()
            ->get($data['url']);

        return array_merge($data, [
            'username' => $response->getUsername(),
            'name'     => $response->getName(),
            'avatar'   => $response->getAvatar(),
            'posts'    => $response->getFeeds()->count()
        ]);
    }
}
