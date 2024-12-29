<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class InvoiceEvent extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'invoice_id',
        'event_type',
        'price',
        'event_date'

    ];


    public function invoice(): HasOne
    {
        return $this->belongsTo(Invoice::class);
    }
}
