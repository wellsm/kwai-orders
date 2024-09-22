<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Responses\FeedResponse;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfileSyncCommand extends Command
{
    protected $signature = 'app:profile:sync {profile} {time?}';

    protected $description = 'Sync Kwai Profile';

    private const OPTION_PROFILE = 'profile';
    private const OPTION_TIME    = 'time';

    private const NO_MORE  = 'no_more';
    private const ENDPOINT = 'https://www.kwai.com/rest/o/w/pc/feed/profile';
    private const PAYLOAD  = [
        "fromPage"   => "PC_PROFILE",
        "beforePage" => "",
        "count"      => 50
    ];

    private const CACHE_TTL = 60 * 60 * 1;
    private const CACHE_KEY = '%s-%s';

    public function handle()
    {
        $start   = microtime(true);
        $profile = $this->argument(self::OPTION_PROFILE);

        if (empty($profile)) {
            return 0;
        }

        $time   = $this->argument(self::OPTION_TIME) ?: null;
        $minute = now()->startOfMinute();

        if (
            isset($time)
            && Carbon::parse($time)->notEqualTo($minute)
        ) {
            return 0;
        }

        $team = Team::query()->where('username', $profile)->firstOrFail();
        $time = $time ?? $team->getSyncAt();

        if (Carbon::parse($time)->notEqualTo($minute)) {
            return 0;
        }

        if ($team->getSyncedAt()?->isToday()) {
            return 0;
        }

        $this->info(date('Y-m-d H:i:s') . ' - Start Sync Profile: @' . $team->getUsername());

        $feeds = $this->getFeeds($team->getUsername());

        DB::table('posts')
            ->where('team_id', $team->getId())
            ->delete();

        $products = collect($feeds)
            ->pluck('product')
            ->filter(fn (array|null $product) => $product['id'])
            ->keyBy('id')
            ->values()
            ->toArray();

        DB::table('products')
            ->upsert($products, ['id']);

        $feeds = array_map(static function (array $feed) {
            return array_merge($feed, [
                'product_id' => $feed['product']['id']
            ]);
        }, $feeds);

        data_set($feeds, '*.team_id', $team->getId());
        data_forget($feeds, '*.product');

        foreach (array_chunk($feeds, 1) as $items) {
            DB::table('posts')
                ->upsert($items, ['id']);
        }

        $team->setSyncedAt(new DateTime());
        $team->save();

        $this->info(date('Y-m-d H:i:s') . ' - Posts: ' . count($feeds));
        $this->info(date('Y-m-d H:i:s') . ' - Elapsed Time: ' . round(microtime(true) - $start, 2));
    }

    private function getFeeds(string $profile, array $feeds = [], string|null $cursor = null): array
    {
        $response = $this->getFeedResponse($profile, $cursor);
        $feeds    = array_merge($feeds, $response->getFeeds());

        if ($response->getCursor() === self::NO_MORE) {
            return $feeds;
        }

        usleep((int) (1000000 * .25));

        $this->line(date('Y-m-d H:i:s') . ' - Cursor: ' .  $cursor);

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
        $key  = sprintf(self::CACHE_KEY, $payload['userId'], (string) ($payload['pcursor'] ?? null));
        $data = Cache::remember($key, self::CACHE_TTL, fn () => $this->getHttpResponse($payload));

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

        curl_close($curl);

        return json_decode($response, true);
    }
}
