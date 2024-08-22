<?php

namespace App\Filament\Pages\Tenancy;
 
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Editar Time';
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $slug = Str::slug($data['name']);

        $record->update(array_merge($data, compact('slug')));

        return $record;
    }
}