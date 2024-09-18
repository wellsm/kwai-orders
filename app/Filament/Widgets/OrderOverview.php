<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Revenue;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class OrderOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    public function getStats(): array
    {
        $date  = $this->filters['date'] ?? now();
        $start = Carbon::parse($date)->subDays(6)->startOfDay();
        $end   = Carbon::parse($date)->endOfDay();

        $trend = Trend::query(Order::query()->where('team_id', Filament::getTenant()->id))
            ->between($start, $end)
            ->perDay();

        $commission = $this->stats($trend, 'revenue');
        $orders     = $this->stats(trend: $trend, format: false);
        $sales      = $this->stats($trend, 'price');

        $trend = Trend::query(Revenue::query()->where('team_id', Filament::getTenant()->id))
            ->between($start, $end)
            ->perDay();
    
        $revenue = $this->stats($trend, 'value');
        
        $stats = fn (Stat $stat, array $item) => empty($item['charts'])
            ? $stat
            : $stat->description("{$item['diff']} {$item['situation']}")
                ->descriptionIcon($item['icon'])
                ->chart($item['charts'])
                ->color($item['color']);

        return [
            $stats(Stat::make('Comissão', "R$ {$commission['value']}"), $commission),
            $stats(Stat::make('Receita', "R$ {$revenue['value']}"), $revenue),
            $stats(Stat::make('Pedidos', $orders['value']), $orders),
            $stats(Stat::make('Vendas', "R$ {$sales['value']}"), $sales),
        ];
    }

    private function stats(Trend $trend, ?string $property = null, bool $format = true): array
    {
        $method    = fn (?Trend $trend) => $property ? $trend?->sum($property) : $trend?->count();
        $result    = $method($trend);
        $charts    = $result->map(fn (TrendValue $value) => $value->aggregate);
        $diff      = $charts->last() - $charts->reverse()->skip(1)->take(1)->first();
        $situation = $diff === 0 ? 'equal' : ($diff < 0 ? 'decrease' : 'increase');
        $value     = $result->last();

        return [
            'charts'    => $charts->toArray(),
            'value'     => $format ? Number::format($value->aggregate, 2) : $value->aggregate,
            'situation' => match ($situation) {
                'equal'    => 'igual',
                'increase' => 'a mais',
                'decrease' => 'a menos',
            },
            'diff'      => $format ? $this->number(abs($diff)) : (int) abs($diff),
            'icon'      => match ($situation) {
                'equal'    => 'heroicon-m-equals',
                'increase' => 'heroicon-m-arrow-trending-up',
                'decrease' => 'heroicon-m-arrow-trending-down',
            },
            'color' => match ($situation) {
                'equal'    => 'gray',
                'increase' => 'success',
                'decrease' => 'danger',
            }
        ];
    }

    private function number(int|float $number): string
    {
        if ($number < 1000) {
            return (string) Number::format($number, 2);
        }

        if ($number < 1000000) {
            return Number::format($number / 1000, 2) . 'k';
        }

        return Number::format($number / 1000000, 2) . 'm';
    }
}
