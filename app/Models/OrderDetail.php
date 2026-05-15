<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'layanan_id',
        'qty',
        'harga',
        'subtotal'
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
