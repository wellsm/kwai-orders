<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrdersSeederCommand extends Command implements Isolatable
{
    protected $signature = 'app:orders:seed
        {--min=10}
        {--max=300}
        {--products=20}
        {--days=30}
        {--team=}';

    protected $description = 'Seed orders with some options';

    private ?Generator $faker = null;

    private ?Collection $products = null;
    private ?Collection $revenues = null;

    public function handle()
    {
        $this->initFaker();
        $this->getProducts();
        $this->getBaseRevenues();

        DB::table('orders')->truncate();
        DB::table('revenues')->truncate();

        $period = $this->getPeriod();
        $orders = $this->getOrders($period);

        foreach (array_chunk($orders, 500) as $items) {
            DB::table('orders')
                ->upsert($items, ['id']);
        }

        $revenues = $this->getRevenues($period);

        DB::table('revenues')
            ->upsert($revenues, ['hash']);

    }

    private function initFaker(): void
    {
        $this->faker = Faker::create('pt_BR');
    }

    private function getPeriod(): DatePeriod
    {
        $days = (int) $this->option('days');

        return new DatePeriod(
            now()->subDays($days)->startOfDay()->toDateTime(),
            new DateInterval('P1D'),
            now()->endOfDay()->toDateTime()
        );
    }

    private function getOrders(DatePeriod $period): array
    {
        $min = (int) $this->option('min');
        $max = (int) $this->option('max');

        $orders = collect();
        $count  = 0;

        foreach ($period as $date) {
            foreach (range(1, $this->faker->numberBetween($min, $max)) as $i) {
                $orders->push(array_merge($this->getRandomProduct(), [
                    'id'         => ++$count,
                    'status'     => 'Pago',
                    'team_id'    => 1,
                    'quantity'   => $this->getRandomQuantity(),
                    'created_at' => $this->getRandomDateTime($date)
                ]));
            }
        }

        return $orders->toArray();
    }

    private function getRevenues(DatePeriod $period): array
    {
        $revenues = collect();
        $count    = 0;

        foreach ($period as $date) {
            $data = $this->getRandomRevenue();

            $data['id']         = ++$count;
            $data['value']      = $this->getRandomValue($data['value']);
            $data['created_at'] = $this->getRandomDateTime($date);
            $data['hash']       = md5($data['name'] . $data['value'] . $data['created_at']->getTimestamp());
            $data['team_id']    = 1;
            
            $revenues->push($data);
        }

        return $revenues->toArray();
    }

    private function getRandomProduct(): array
    {
        return $this->faker->randomElement($this->products);
    }

    private function getRandomRevenue(): array
    {
        return $this->faker->randomElement($this->revenues);
    }

    private function getRandomValue(array $value): float
    {
        return $this->faker->randomFloat(2, $value[0], $value[1]);
    }

    private function getRandomQuantity(): int
    {
        return $this->faker->boolean(90) ? 1 : $this->faker->numberBetween(1, 3);
    }

    private function getRandomDateTime(DateTime $date): DateTime
    {
        return $this->faker->dateTimeBetween(
            startDate: Carbon::parse($date)->startOfDay()->toDateTime(),
            endDate: Carbon::parse($date)->endOfDay()->toDateTime()
        );
    }

    private function getProducts(): void
    {
        $this->products = collect(require_once base_path('examples/products.php'))
            ->random($this->option('products'));
    }

    private function getBaseRevenues(): void
    {
        $this->revenues = collect(require_once base_path('examples/revenues.php'));
    }
}
