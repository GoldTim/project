<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;

    protected $table = "order";
    protected $fillable = [
        "shop_id",
        "order_type",
        "order_title",
        "customer_id",
        "order_sn",
        "pay_sn",
        "pay_method",
        "pay_time",
        "confirm_time",
        "order_amount",
        "actual_amount",
        "ship_amount",
        "dis_amount",
        "dis_point",
        "order_remark",
        "close_reason",
        "shipping_status",
        "close_type",
        "status", "created_at", "updated_at"
    ];
    protected $dateFormat = "U";
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";


    protected function actualAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function orderAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function shipAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function disAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function confirmTime(): Attribute
    {
        return new Attribute(function ($value) {
            return !empty($value) ? date("Y-m-d H:i:s", $value) : null;
        });
    }

    /**
     * 获取操作日志
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function log()
    {
        return $this->hasMany(Log::class, 'order_id')->orderByDesc('created_at');
    }

    public function goods()
    {
        return $this->hasMany(Goods::class, 'order_id');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class, 'order_id');
    }

    public function refund()
    {
        return $this->hasOne(\App\Models\Refund\Info::class, 'order_id')->orderByDesc('created_at');
    }
}
