<?php

namespace App\Services\Order;

use App\Models\Order;
use ArrayIterator;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class OrderImport
{
    private const LIMIT_EXCEEDED_MESSAGES = [
        'Alguns pedidos não foram importados',
        'Ultimo Pedido: :order',
        'Pedido feito: :date'
    ];

    public function run(string $content): Order
    {
        $iterator = new \ArrayIterator(explode(PHP_EOL, $content));
        $count    = $iterator->count();
        $iterator = $this->removeExtraContent($iterator);
        $orders   = [];

        $this->proceed($iterator);

        while ($iterator->valid()) {
            try {
                $result = $this->orders($iterator);
                $orders = array_merge($orders, $result);
            } catch (\Exception) {}
        }

        if ($count !== $iterator->count()) {
            $last = end($orders);
            $body = strtr(implode('<br>', self::LIMIT_EXCEEDED_MESSAGES), [
                ':order' => $last['id'],
                ':date'  => $last['created_at']->format('Y/m/d H:i:s')
            ]);

            Notification::make()
                ->title('Limite de caracteres excedido')
                ->body($body)
                ->persistent()
                ->info()
                ->send();
        }

        data_fill($orders, '*.team_id', Filament::getTenant()->id);

        foreach (array_chunk($orders, 100) as $items) {
            DB::table('orders')->upsert($items, ['id']);
        }

        return Order::query()->latest()->take(1)->get()->last();
    }

    public function line(\ArrayIterator $iterator, int $lines = 1): ?string
    {
        $value = $iterator->current();

        if (is_null($value)) {
            throw new \Exception('EOF');
        }

        foreach (range(1, $lines) as $line) {
            $iterator->next();
        }

        return $value;
    }

    public function proceed(\ArrayIterator $iterator): void
    {
        while ($iterator->valid() && str_contains($iterator->current(), 'ID do pedido') === false) {
            $iterator->next();
        }
    }

    public function orders(\ArrayIterator $iterator): array
    {
        $orders = [];
        $id     = preg_replace('/[^0-9]/', '', $this->line($iterator));
        $status = $this->line($iterator);

        error_log($id);
        error_log($status);

        while (mb_substr($iterator->current(), 0, 1) != '*') {
            $name       = $this->line($iterator);
            $product    = preg_replace('/[^0-9]/', '', $this->line($iterator));
            $commission = str_replace('Taxa de comissão: ', '', preg_replace('/[^0-9,]/', '', $this->line($iterator, 2)));
            $price      = (float) str_replace(',', '.', preg_replace('/[^0-9,]/', '', $this->line($iterator, 2)));
            $quantity   = $this->line($iterator, 2);
            $revenue    = (float) str_replace(',', '.', preg_replace('/[^0-9,]/', '', $this->line($iterator)));

            $orders[] = compact('id', 'status', 'name', 'product', 'commission', 'price', 'quantity', 'revenue');
        }

        while (str_contains($iterator->current(), 'Pedido feito') === false) {
            $iterator->next();
        }

        $date = str_replace('Pedido feito: ', '', $this->line($iterator, 1));
        $date = Carbon::createFromFormat('Y/m/d H:i:s', $date);

        foreach ($orders as &$order) {
            $order['created_at'] = $date;
        }

        $this->proceed($iterator);

        return $orders;
    }

    private function removeExtraContent(ArrayIterator $iterator): ArrayIterator
    {
        $lines = iterator_to_array($iterator);
        $lines = array_reverse($lines);

        foreach ($lines as $i => $line) {
            if (str_contains($line, 'Liquidado')) {
                break;
            }

            unset($lines[$i]);
        }

        return new ArrayIterator(
            array: array_reverse($lines)
        );
    }
}
