<?php

declare(strict_types=1);

namespace App\Responses;

readonly class FeedResponse
{
    public function __construct(
        private array $feeds,
        private string $cursor
    ) {}

    public function getFeeds(): array
    {
        return array_map(static function (array $feed) {
            $info = json_decode($feed['plcFeatureInfo'] ?? '', true);

            return [
                'id'         => $feed['photo_id'],
                'title'      => $feed['coverCaption'],
                'created_at' => date('Y-m-d H:i:s', (int) round($feed['timestamp'] / 1000)),
                'views'      => $feed['view_count'],
                'product'    => [
                    'id'   => ($info['resourceId'] ?? null) ?: null,
                    'name' => $info['style']['typeThree']['title'] ?? $info['style']['strongTypeOne']['title'] ?? null,
                ],
            ];
        }, $this->feeds);
    }

    public function getCursor(): string
    {
        return $this->cursor;
    }
}
