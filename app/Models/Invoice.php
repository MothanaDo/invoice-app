<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Invoice extends Model
{
    use HasFactory;

    protected $fillable=[
        'price',
        'customer_id'
    ];


    public function customer()    
    {
        return $this->belongsTo(Customer::class);
    }

    public function events()
    {
        return $this->hasMany(InvoiceEvent::class);
    }
}
