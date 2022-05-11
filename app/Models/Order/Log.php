<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $table="order_log";


    public function order()
    {
        return $this->belongsTo(Info::class, 'id', 'order_id');
    }
}
