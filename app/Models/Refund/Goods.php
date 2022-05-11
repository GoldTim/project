<?php

namespace App\Models\Refund;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use HasFactory;

    protected $table = "refund_goods";

    protected function Amount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function TotalAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    public function goods()
    {
        return $this->belongsTo(\App\Models\Good\Info::class, 'goods_id');
    }
}
