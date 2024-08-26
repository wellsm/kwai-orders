<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Revenue;
use DateInterval;
use DatePeriod;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Comissão e Receita';

    protected static ?string $pollingInterval = null;

    private const FILTER_7_DAYS  = '7-days';
    private const FILTER_WEEK    = 'week';
    private const FILTER_30_DAYS = '30-days';
    private const FILTER_MONTH   = 'month';

    protected function getData(): array
    {
        $period = $this->getPeriod();
        $orders = Trend::query(Order::query()->where('team_id', Filament::getTenant()->id))
            ->between($period->getStartDate(), $period->getEndDate())
            ->perDay()
            ->sum('revenue');

        $revenues = Trend::query(Revenue::query()->where('team_id', Filament::getTenant()->id))
            ->between($period->getStartDate(), $period->getEndDate())
            ->perDay()
            ->sum('value');

        return [
            'datasets' => [
                [
                    'label' => 'Comissão',
                    'data'  => $orders->map(fn(TrendValue $value) => $value->aggregate),
                    'fill'  => 'start'
                ],
                [
                    'label'           => 'Receita',
                    'data'            => $revenues->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor'     => 'green',
                    'backgroundColor' => '#1E2D25',
                    'fill'            => 'start'
                ],
            ],
            'labels' => $orders->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('d/m')),
        ];
    }

    private function getPeriod(): DatePeriod
    {
        $filter = $this->filter ?? self::FILTER_7_DAYS;
        $days   = match ($filter) {
            self::FILTER_7_DAYS  => 6,
            self::FILTER_WEEK    => (int) ceil(now()->startOfWeek()->diffInDays(now())),
            self::FILTER_30_DAYS => 30,
            self::FILTER_MONTH   => (int) ceil(now()->startOfMonth()->diffInDays(now())),
        };

        return new DatePeriod(
            start: now()->subDays($days)->startOfDay(),
            interval: new DateInterval('P1D'),
            end: now()->endOfDay()
        );
    }

    protected function getFilters(): ?array
    {
        return [
            self::FILTER_7_DAYS  => 'Últimos 7 dias',
            self::FILTER_WEEK    => 'Esta semana',
            self::FILTER_30_DAYS => 'Últimos 30 dias',
            self::FILTER_MONTH   => 'Este mês',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
