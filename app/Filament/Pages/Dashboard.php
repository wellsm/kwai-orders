<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\OrderOverview;
use App\Filament\Widgets\OrdersChart;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        $minDate = Order::query()->first('created_at')?->getCreatedAt()?->addDay();

        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Data')
                    ->minDate($minDate ?? now())
                    ->maxDate(now())
                    ->default(now()->subDay()->toDateString())
            ]);
    }

    public function getWidgets(): array
    {
        return [
            OrderOverview::class,
            OrdersChart::class,
            LatestOrders::class,
        ];
    }
}
