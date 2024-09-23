<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Team;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileVerify implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private const BATCH_MESSAGE = '%s - Success: %d - Left: %d';

    private const BASE_URL_TO_VERIFY = 'https://m-shop.kwai.com/rest/o/ecom/customer/item/info?itemId=%s';
    private const CACHE_RESPONSE_KEY = 'product-%s';
    private const CACHE_RESPONSE_TTL = 60 * 60 * 1; // 1hr

    public function __construct(
        public Team $team
    ) {}

    public function handle(): void
    {
        /** @var Collection */
        $posts    = $this->team->posts()->get();
        $products = $posts->filter(fn(Post $post) => !empty($post->product_id))
            ->pluck('product_id')
            ->unique()
            ->values();

        $results = $this->getResponses($products);

        DB::table('products')
            ->upsert($results, ['id'], ['quantity']);

        $this->team->setVerifiedAt(new DateTime());
        $this->team->save();
    }

    private function getResponses(Collection $products): array
    {
        $responses = $this->getCachedResponses($products);
        $products  = $products->filter(fn (int $id) => !isset($responses[$id]));
        $responses = array_values(array_filter($responses));

        foreach ($products->chunk(10) as $items) {
            $responses = array_merge($responses, $this->getNewResponses($items));
            $success   = count(array_filter($responses));

            Log::channel('console')
                ->info(sprintf(self::BATCH_MESSAGE, date('Y-m-d H:i:s'), $success, $products->count() - $success));
        }

        return $responses;
    }

    private function getCachedResponses(Collection $products): array
    {
        return $products
            ->mapWithKeys(fn (int $id) => [
                $id => Cache::get(sprintf(self::CACHE_RESPONSE_KEY, $id))
            ])
            ->toArray();
    }

    private function getNewResponses(Collection $products): array
    {
        $promises = [];
        $client   = new Client();

        foreach ($products as $id) {
            $promises[$id] = $client->getAsync(sprintf(self::BASE_URL_TO_VERIFY, $id));
        }

        $responses = Utils::unwrap($promises);
        $results   = [];

        foreach ($responses as $id => $response) {
            $results[] = $this->getResult($id, $response);
        }

        foreach ($results as $result) {
            Cache::put(sprintf(self::CACHE_RESPONSE_KEY, $result['id']), $result, self::CACHE_RESPONSE_TTL);
        }

        return $results;
    }

    private function getResult(string|int $id, Response $response): array
    {
        $response = json_decode($response->getBody()->getContents(), true);

        if (empty($response['data'])) {
            return [];
        }

        return [
            'id'       => $id,
            'name'     => $response['data']['title'],
            'quantity' => $response['data']['totalQuantity'],
        ];
    }
}
