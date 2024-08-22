<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;

class OrdersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $date  = $this->filters['date'];
        $start = $date ? Carbon::parse($date)->subDays(6)->startOfDay() : now()->subDays(7)->startOfDay();
        $end   = $date ? Carbon::parse($date)->endOfDay() : now()->subDay()->endOfDay();

        /** @var Collection */
        $orders = Order::query()
            ->where('team_id', Filament::getTenant()->id)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $revenue = $orders->groupBy(fn (Order $order) => $order->getCreatedAt()->shortDayName)
            ->map(fn (Collection $orders) => $orders->sum('revenue'));

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $revenue->values()->toArray(),
                    'fill' => 'start',
                ],
            ],
            'labels' => $revenue->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
