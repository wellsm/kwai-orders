<?php

namespace App\Models;

use Carbon\CarbonInterface;
use DateTime;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Billable;

class Team extends Model implements HasAvatar
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'username',
        'avatar',
        'posts',
        'sync_at'
    ];

    protected $casts = [
        'synced_at'   => 'datetime',
        'verified_at' => 'datetime'
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    
    public function getFilamentAvatarUrl(): ?string
    {
        return Storage::url($this->avatar);
    }

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getUsername(): string
    {
        return $this->getAttribute('username');
    }

    public function getSyncedAt(): ?CarbonInterface
    {
        return $this->getAttribute('synced_at');
    }

    public function setSyncedAt(DateTime $syncedAt): self
    {
        $this->setAttribute('synced_at', $syncedAt);

        return $this;
    }

    public function getVerifiedAt(): ?CarbonInterface
    {
        return $this->getAttribute('verified_at');
    }

    public function setVerifiedAt(DateTime $verifiedAt): self
    {
        $this->setAttribute('verified_at', $verifiedAt);

        return $this;
    }

    public function getSyncAt(): ?string
    {
        return $this->getAttribute('sync_at');
    }

    public function setSyncAt(string $syncAt): self
    {
        $this->setAttribute('sync_at', $syncAt);

        return $this;
    }
}
