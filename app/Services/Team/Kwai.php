<?php

namespace App\Services\Team;

use App\Responses\KwaiProfileResponse;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Kwai
{
    private const CACHE_KEY   = 'kwai-profile-%s';
    private const PROFILE_URL = 'https://www.kwai.com/@%s';

    private bool $withAvatar = false;

    public function get(string $url): ?KwaiProfileResponse
    {
        preg_match('/(?<value>@[^\/]+)/', $url, $matches);

        $profile = $matches['value'] ?? null;

        if (empty($profile)) {
            return null;
        }

        $data = Cache::remember(
            key: sprintf(self::CACHE_KEY, ltrim($profile, '@')),
            ttl: 60,
            callback: fn() => Http::post('https://www.kwai.com/rest/o/w/pc/feed/profile', [
                'userId'     => $profile,
                'fromPage'   => 'PC_PROFILE',
                'beforePage' => '',
                'pcursor'    => '',
                'count'      => 50
            ])->json()
        );

        if (!isset($data['feeds'])) {
            return null;
        }

        $feeds    = $data['feeds'];
        $username = $feeds[0]['kwai_id'];
        $avatar   = $this->downloadAvatar($feeds[0]['headurls'], $username);

        return new KwaiProfileResponse(
            username: $username,
            name: $feeds[0]['user_name'],
            url: sprintf(self::PROFILE_URL, $username),
            avatar: $avatar
        );
    }

    public function withAvatar(bool $withAvatar = true): self
    {
        $this->withAvatar = $withAvatar;

        return $this;
    }

    private function downloadAvatar(array $urls, string $username): ?string
    {
        if ($this->withAvatar === false) {
            return null;
        }

        $avatar = $urls[0]['url'] ?? $urls[1]['url'];
        $ext    = str($avatar)->afterLast('.');
        $path   = storage_path("app/public/{$username}");

        File::ensureDirectoryExists($path);

        Http::withOptions([
            RequestOptions::SINK => fopen($path . "/avatar.{$ext}", 'w')
        ])->get($avatar);

        return "{$username}/avatar.{$ext}";
    }
}
