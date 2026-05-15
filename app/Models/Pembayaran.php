<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';

    protected $fillable = [
        'order_id',
        'metode_pembayaran',
        'status',
        'jumlah_bayar',
        'bukti_pembayaran',
        'tanggal_bayar',
    ];

    protected $appends = [
        'bukti_pembayaran_url',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function getBuktiPembayaranUrlAttribute()
    {
        if (! $this->bukti_pembayaran) {
            return null;
        }

        return Storage::disk('public')->url($this->bukti_pembayaran);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
