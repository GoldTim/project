<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $table = "order_shipping";


    public function order()
    {
        return $this->belongsTo(Info::class, 'id', 'order_id');
    }
}
