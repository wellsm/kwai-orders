<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;

    public function getCreatedAt(): CarbonInterface
    {
        return $this->getAttribute('created_at');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
