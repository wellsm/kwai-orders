<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\Order\OrderImport;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Pedidos';

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('create-order')
                ->outlined()
                ->createAnother(false)
                ->label('Importar Pedidos')
                ->using(function (array $data): Model {
                    return (new OrderImport())->run($data['content']);
                }),
        ];
    }
}
