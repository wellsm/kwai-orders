<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'team_id',
        'name',
        'value',
        'hash',
        'created_at'
    ];

    public function getCreatedAt(): CarbonInterface
    {
        return $this->getAttribute('created_at');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
