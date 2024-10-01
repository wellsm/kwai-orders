<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Team;
use App\Responses\FeedResponse;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use DateTime;
use Exception;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;

class ProfileSync implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private const SYNC_MESSAGE   = '%s - %s - Syncing';
    private const CURSOR_MESSAGE = '%s - %s - Cursor: %s';
    private const POSTS_MESSAGE  = '%s - %s - Posts: %s';

    private const NO_MORE  = 'no_more';
    private const ENDPOINT = 'https://www.kwai.com/rest/o/w/pc/feed/profile';
    private const PAYLOAD  = [
        "fromPage"   => "PC_PROFILE",
        "beforePage" => "",
        "count"      => 50
    ];

    private const CACHE_POSTS_TTL = 60 * 60 * 1; // 1h
    private const CACHE_POSTS_KEY = '%s-sync-%s';

    public function __construct(
        public Team $team
    ) {}

    public function handle(): void
    {
        Log::channel('console')
            ->info(sprintf(self::SYNC_MESSAGE, date('Y-m-d H:i:s'), $this->team->getUsername()));

        $feeds = $this->getFeeds($this->team->getUsername());

        Post::query()
            ->where('team_id', $this->team->getId())
            ->delete();

        $products = collect($feeds)
            ->pluck('product')
            ->filter(fn (array|null $product) => $product['id'])
            ->keyBy('id')
            ->values()
            ->toArray();

        DB::table('products')
            ->upsert($products, ['id'], ['name']);

        $feeds = array_map(static function (array $feed) {
            return array_merge($feed, [
                'product_id' => $feed['product']['id']
            ]);
        }, $feeds);

        data_set($feeds, '*.team_id', $this->team->getId());
        data_set($feeds, '*.updated_at', date('Y-m-d H:i:s'));
        data_set($feeds, '*.deleted_at', null);
        data_forget($feeds, '*.product');

        foreach (array_chunk($feeds, 500) as $items) {
            DB::table('posts')
                ->upsert($items, ['id'], ['product_id', 'title', 'views', 'deleted_at']);
        }

        $this->team->setSyncedAt(new DateTime());
        $this->team->save();

        Log::channel('console')
            ->info(sprintf(self::POSTS_MESSAGE, date('Y-m-d H:i:s'), $this->team->getUsername(), count($feeds)));

        ProfileVerify::dispatch($this->team);
    }

    private function getFeeds(string $profile, array $feeds = [], string|null $cursor = null): array
    {
        $response = $this->getFeedResponse($profile, $cursor);
        $feeds    = array_merge($feeds, $response->getFeeds());

        if ($response->getCursor() === self::NO_MORE) {
            return $feeds;
        }

        usleep((int) (1000000 * .5));

        Log::channel('console')
            ->info(sprintf(self::CURSOR_MESSAGE, date('Y-m-d H:i:s'), $this->team->getUsername(), $cursor));

        return $this->getFeeds($profile, $feeds, $response->getCursor());
    }

    private function getFeedResponse(string $profile, string|null $cursor = null): FeedResponse
    {
        $payload = self::PAYLOAD;
        $payload['userId'] = "@{$profile}";

        if ($cursor !== null) {
            $payload['pcursor'] = $cursor;
        }

        $data   = $this->getCachedHttpResponse($payload);
        $cursor = $data['cursor'] ?? null;

        if (empty($cursor)) {
            throw new Exception('Cursor is Empty');
        }

        return new FeedResponse($data['feeds'], $cursor);
    }

    private function getCachedHttpResponse(array $payload): array
    {
        $key  = sprintf(self::CACHE_POSTS_KEY, $payload['userId'], (string) ($payload['pcursor'] ?? null));
        $data = Cache::remember(
            key: $key, 
            ttl: self::CACHE_POSTS_TTL,
            callback: fn () => retry(3, fn () => $this->getHttpResponse($payload), 500)
        );

        return $data;
    }

    private function getHttpResponse(array $payload): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => self::ENDPOINT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $response = json_decode($response, true);

        curl_close($curl);

        if (!isset($response['feeds'])) {
            throw new UnexpectedResponseException();
        }

        return $response;
    }
}
