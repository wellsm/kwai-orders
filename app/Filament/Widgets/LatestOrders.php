<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LatestOrders extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.latest-orders';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('date')
            ]);
    }

    protected function getViewData(): array
    {
        $date  = $this->filters['date'];
        $start = $date ? Carbon::parse($date)->subDays(6)->startOfDay() : now()->subDays(7)->startOfDay();
        $end   = $date ? Carbon::parse($date)->endOfDay() : now()->subDay()->endOfDay();

        /** @var Collection */
        $orders = Order::query()
            ->where('team_id', Filament::getTenant()->id)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $latest = $orders->groupBy(fn (Order $order) => $order->getCreatedAt()->format('d/m/Y'))
            ->map(fn (Collection $orders, string $date) => [
                'date'    => $date,
                'revenue' => $orders->sum('revenue'),
                'orders'  => $orders->count()
            ])
            ->values();

        return compact('latest');
    }
}
