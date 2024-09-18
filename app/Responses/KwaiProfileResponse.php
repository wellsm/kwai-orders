<?php

namespace App\Responses;

use Illuminate\Support\Collection;

class KwaiProfileResponse
{
    public function __construct(
        private string $username,
        private string $name,
        private string $url,
        private ?string $avatar = null,
        private ?array $feeds = []
    ) { }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getFeeds(): ?Collection
    {
        return collect($this->feeds);
    }
}