<?php

namespace App\Models\Order;

use App\Services\OrderService;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Pay extends Model
{
    protected $table = "order_pay";
    protected $fillable = [
        "order_ids", "pay_sn", "pay_method", "payment_amount", "status"
    ];

    protected function PaymentAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function OrderIds(): Attribute
    {
        return new Attribute(function ($value) {
            return explode(",", $value);
        }, function ($value) {
            return implode(",", $value);
        });
    }

    protected static function booted()
    {
        static::updated(function ($pay) {
            if ($pay['status'] == 1) {
                $res = (new OrderService())->payOrder($pay['order_ids']);
                if (!$res['succeed']) {
                    \Illuminate\Support\Facades\Log::channel('pay')->error('支付成功但修改订单状态失败', ['data' => $res]);
                } else {
                    \Illuminate\Support\Facades\Log::channel('pay')->info('支付回调成功');
                }
            }
        });
    }
}
