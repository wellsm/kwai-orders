<?php

namespace App\Services\Order;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class OrderImport
{
    public function run(string $content): Order
    {
        $iterator = new \ArrayIterator(explode(PHP_EOL, $content));
        $orders   = [];

        $this->proceed($iterator);

        while ($iterator->valid()) {
            try {
                $result = $this->orders($iterator);
                $orders = array_merge($orders, $result);
            } catch (\Exception) {}
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
}
