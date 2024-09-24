<?php

namespace App\Filament\Pages\Tenancy;
 
use App\Rules\KwaiProfile;
use App\Services\Team\Kwai;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Adicionar Conta';
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->extraAttributes(['class' => 'form-without-validation'])
            ->schema([
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

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $response = (new Kwai())
            ->withAvatar()
            ->get($data['url']);

        return array_merge($data, [
            'url'      => $response->getUrl(),
            'username' => $response->getUsername(),
            'name'     => $response->getName(),
            'avatar'   => $response->getAvatar(),
            'posts'    => $response->getFeeds()->count(),
            'slug'     => $response->getUsername(),
        ]);
    }
 
    protected function afterRegister(): void
    {
        $this->tenant->members()->attach(Auth::user());
    }
}