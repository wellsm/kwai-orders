<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use DateInterval;
use DatePeriod;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Illuminate\Support\Collection;

class BestSellers extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '5 Produtos Mais Vendidos';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '300px';

    private const FILTER_YESTERDAY = 'yesterday';
    private const FILTER_TODAY     = 'today';
    private const FILTER_7_DAYS    = '7-days';
    private const FILTER_WEEK      = 'week';
    private const FILTER_30_DAYS   = '30-days';
    private const FILTER_MONTH     = 'month';

    protected function getData(): array
    {
        $period = $this->getPeriod();
        $orders = Order::query()->where('team_id', Filament::getTenant()->id)
            ->whereBetween('created_at', [$period->getStartDate(), $period->getEndDate()])
            ->get();

        $products = $orders->pluck('name', 'product');
        $data     = $orders->groupBy('product')
            ->map(fn(Collection $orders, string $product) => [
                'product'  => $product,
                'quantity' => $orders->sum('quantity'),
                'name'     => str($products[$product])->limit(110)
            ])
            ->sortByDesc('quantity')
            ->take(5);

        return [
            'datasets' => [
                [
                    'label' => '5 Produtos mais vendidos',
                    'data'  => $data->pluck('quantity')->toArray(),
                    'backgroundColor' => [
                        '#003f5c',
                        '#58508d',
                        '#bc5090',
                        '#ff6361',
                        '#ffa600',
                    ],
                    'hoverOffset' => 4,
                    'showLine'    => false
                ]
            ],
            'labels' => $data->pluck('name')->toArray()
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        align: 'start',
                    },
                    
                    tooltip: {
                        usePointStyle: true,
                        boxPadding: 6,
                        callbacks: {
                            label: ({ parsed }) => parsed,
                            title: ([ { label } ]) => label,
                        }
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                }
            }
        JS);
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getFilters(): ?array
    {
        return [
            self::FILTER_YESTERDAY => 'Ontem',
            self::FILTER_TODAY     => 'Hoje',
            self::FILTER_7_DAYS    => 'Últimos 7 dias',
            self::FILTER_WEEK      => 'Esta semana',
            self::FILTER_30_DAYS   => 'Últimos 30 dias',
            self::FILTER_MONTH     => 'Este mês',
        ];
    }

    private function getPeriod(): DatePeriod
    {
        $filter = $this->filter ?? self::FILTER_YESTERDAY;
        $days   = match ($filter) {
            self::FILTER_YESTERDAY => [1, 1],
            self::FILTER_TODAY     => 0,
            self::FILTER_7_DAYS    => 6,
            self::FILTER_WEEK      => (int) ceil(now()->startOfWeek()->diffInDays(now())),
            self::FILTER_30_DAYS   => 30,
            self::FILTER_MONTH     => (int) ceil(now()->startOfMonth()->diffInDays(now())),
        };

        $sub = $days[0] ?? $days;
        $add = $days[1] ?? 0;

        return new DatePeriod(
            start: now()->subDays($sub)->startOfDay(),
            interval: new DateInterval('P1D'),
            end: now()->addDays($add)->endOfDay()
        );
    }
}
