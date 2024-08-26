<?php

namespace App\Services\Revenue;

use App\Models\Revenue;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class RevenueImport
{
    public function run(string $content): Revenue
    {
        $iterator = new \ArrayIterator(explode(PHP_EOL, $content));
        $revenues = [];

        $this->proceed($iterator);

        try {
            $result   = $this->revenues($iterator);
            $revenues = array_merge($revenues, $result);
        } catch (\Exception) {}

        foreach (array_chunk($revenues, 100) as $items) {
            DB::table('revenues')->upsert($items, ['hash']);
        }

        return Revenue::query()->latest()->take(1)->get()->last();
    }

    public function line(\ArrayIterator $iterator, int $lines = 1): ?string
    {
        $value = $iterator->current();

        if (is_null($value)) {
            return null;
        }

        foreach (range(1, $lines) as $_) {
            $iterator->next();
        }

        return $value;
    }

    public function proceed(\ArrayIterator $iterator): void
    {
        while ($iterator->valid() && str_contains($iterator->current(), 'R$') === false) {
            $iterator->next();
        }

        $iterator->seek($iterator->key() - 2);
    }

    public function revenues(\ArrayIterator $iterator): array
    {
        $revenues = [];

        while ($iterator->valid()) {
            $name       = $this->line($iterator);
            $operator   = $this->line($iterator);
            $value      = (float) preg_replace('/[^0-9.]/', '', $this->line($iterator));
            $date       = str_replace(',', '', $this->line($iterator));

            $revenues[] = compact('name', 'operator', 'value', 'date');
        }

        $revenues = array_values(
            array_filter($revenues, fn (array $revenue) => $revenue['operator'] !== '-' && count(array_filter($revenue)) === 4)
        );
        
        foreach ($revenues as &$revenue) {
            $revenue['created_at'] = Carbon::createFromFormat('d/m/Y H:i:s', $revenue['date']);
            $revenue['hash'] = md5($revenue['name'] . $revenue['value'] . $revenue['date']);
        }

        data_forget($revenues, '*.date');
        data_forget($revenues, '*.operator');
        data_fill($revenues, '*.team_id', Filament::getTenant()->id);

        return $revenues;
    }
}
