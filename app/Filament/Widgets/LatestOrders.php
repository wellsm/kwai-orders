<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Revenue;
use DateInterval;
use DatePeriod;
use DateTime;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LatestOrders extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.latest-orders';

    protected function getViewData(): array
    {
        $period = $this->getPeriod();
        $orders = Trend::query(Order::query()->where('team_id', Filament::getTenant()->id))
            ->between($period->getStartDate(), $period->getEndDate())
            ->perDay();

        $revenues = Trend::query(Revenue::query()->where('team_id', Filament::getTenant()->id))
            ->between($period->getStartDate(), $period->getEndDate())
            ->perDay();

        $aggregator = fn (Collection $items) => $items
            ->mapWithKeys(fn (TrendValue $value) => [$value->date => $value->aggregate])
            ->toArray();

        $commission = $aggregator($orders->sum('revenue'));
        $orders     = $aggregator($orders->sum('quantity'));
        $revenue    = $aggregator($revenues->sum('value'));

        $latest = collect(iterator_to_array($period))
            ->map(function (DateTime $date) use ($commission, $orders, $revenue) {
                $key        = $date->format('Y-m-d');
                $revenue    = $revenue[$key];
                $commission = $commission[$key];

                return [
                    'date'       => $date,
                    'revenue'    => $revenue,
                    'orders'     => $orders[$key],
                    'commission' => $commission,
                    'total'      => $revenue + $commission,
                ];
            });

        return compact('latest');
    }

    private function getPeriod(): DatePeriod
    {
        $start = now()->subDays(6)->startOfDay();
        $end   = now()->endOfDay();

        return new DatePeriod(
            start: $start,
            interval: new DateInterval('P1D'),
            end: $end
        );
    }
}
