<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Number;

class OrderOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    public function getStats(): array
    {
        $date  = $this->filters['date'];
        $start = $date ? Carbon::parse($date)->subDays(6)->startOfDay() : now()->subDays(7)->startOfDay();
        $end   = $date ? Carbon::parse($date)->endOfDay() : now()->subDay()->endOfDay();

        /** @var Collection */
        $orders = Order::query()
            ->where('team_id', Filament::getTenant()->id)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $grouped = $orders->groupBy(fn (Order $order) => $order->getCreatedAt()->toDateString());

        $revenue = $this->stats($grouped, 'revenue');
        $orders  = $this->stats(grouped: $grouped, format: false);
        $sales   = $this->stats($grouped, 'price');

        $stats = fn (Stat $stat, array $item) => empty($item['charts'])
            ? $stat
            : $stat->description("{$item['diff']} {$item['situation']}")
                ->descriptionIcon($item['icon'])
                ->chart($item['charts'])
                ->color($item['color']);


        return [
            $stats(Stat::make('Receita', "R$ {$revenue['value']}"), $revenue),
            $stats(Stat::make('Pedidos', $orders['value']), $orders),
            $stats(Stat::make('Vendas', "R$ {$sales['value']}"), $sales),
        ];
    }

    private function stats(Collection $grouped, ?string $property = null, bool $format = true): array
    {
        $method    = fn (?Collection $items) => $property ? $items?->sum($property) : $items?->count();
        $charts    = $grouped->map(fn (Collection $orders) => $method($orders))->toArray();
        $diff      = $method($grouped->last()) - $method($grouped->reverse()->skip(1)->take(1)->first());
        $situation = $diff === 0 ? 'equal' : ($diff < 0 ? 'decrease' : 'increase');

        return [
            'charts'    => $charts,
            'value'     => Number::format($method($grouped->last()) ?? 0, 2),
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
