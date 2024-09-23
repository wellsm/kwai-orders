<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'id',
        'team_id',
        'title',
        'views',
        'notify',
    ];

    protected $casts = [
        'notify' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isNotify(): bool
    {
        return $this->getAttribute('notify');
    }

    public function getHasProductAttribute(): bool
    {
        return !empty($this->getAttribute('product_id'));
    }
}
