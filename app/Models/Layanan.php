<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanans';

    protected $fillable = [
        'nama_layanan',
        'harga',
        'deskripsi',
    ];

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
