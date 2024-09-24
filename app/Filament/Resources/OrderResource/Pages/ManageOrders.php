<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\Order\OrderImport;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Pedidos';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('import-orders')
                ->outlined()
                ->createAnother(false)
                ->label('Importar Pedidos')
                ->modelLabel('Pedido')
                ->form([
                    Textarea::make('content')
                        ->label('Conteúdo')
                        ->required()
                        ->rows(20)
                ])
                ->using(function (array $data): Model {
                    return (new OrderImport())->run($data['content']);
                }),
        ];
    }
}
