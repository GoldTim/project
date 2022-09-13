<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Goods extends Model
{
    use HasFactory;

    protected $table = "order_goods";

    protected function amount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function totalAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    public function order()
    {
        return $this->belongsTo(Info::class,'order_id');
    }

}
