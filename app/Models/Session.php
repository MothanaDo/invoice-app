<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'activated',
        'appointment',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->belongsTo(User::class);
    }
}
