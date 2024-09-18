<?php

namespace App\Filament\Resources\RevenueResource\Pages;

use App\Filament\Resources\RevenueResource;
use App\Models\Revenue;
use App\Services\Revenue\RevenueImport;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageRevenues extends ManageRecords
{
    protected static string $resource = RevenueResource::class;

    protected static ?string $title = 'Receitas';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('insert-revenue')
                ->outlined()
                ->label('Inserir Receita')
                ->modelLabel('Pedido')
                ->form([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required(),
                    TextInput::make('value')
                        ->label('Receita')
                        ->numeric()
                        ->required(),
                    DatePicker::make('created_at')
                        ->label('Data da Receita')
                        ->before('today')
                ])
                ->using(function (array $data): Model {
                    return Revenue::create(array_merge($data, [
                        'team_id' => Filament::getTenant()->id,
                        'hash'    => md5($data['name'] . $data['value'] . $data['created_at'])
                    ]));
                }),
            Actions\CreateAction::make('import-revenues')
                ->outlined()
                ->createAnother(false)
                ->label('Importar Receitas')
                ->modelLabel('Receita')
                ->form([
                    Textarea::make('content')
                        ->label('Conteúdo')
                        ->required()
                        ->rows(20)
                ])
                ->using(function (array $data): Model {
                    return (new RevenueImport())->run($data['content']);
                }),
        ];
    }
}
