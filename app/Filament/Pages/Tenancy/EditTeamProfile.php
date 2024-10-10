<?php

namespace App\Filament\Pages\Tenancy;

use App\Filament\Pages\Dashboard;
use App\Rules\KwaiProfile;
use App\Services\Team\Kwai;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Validation\ValidationException;

class EditTeamProfile extends EditTenantProfile
{
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public static function getLabel(): string
    {
        return 'Alterar Conta';
    }

    public function form(Form $form): Form
    {
        return $form
            ->extraAttributes(['class' => 'form-without-validation'])
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->disabled(),
                TextInput::make('username')
                    ->label('ID da Conta')
                    ->disabled(),
                TextInput::make('url')
                    ->label('URL do Perfil')
                    ->required()
                    ->rules([new KwaiProfile(Filament::getTenant())]),
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
            'url'      => $response->getUrl(),
            'username' => $response->getUsername(),
            'slug'     => $response->getUsername(),
            'name'     => $response->getName(),
            'avatar'   => $response->getAvatar(),
            'posts'    => $response->getFeeds()->count()
        ]);
    }

    protected function getRedirectUrl(): ?string
    {
        return Dashboard::getUrl(tenant: $this->tenant);
    }
}
