<?php

namespace App\Services;

use App\Models\Refund\Goods as RefundGoods;
use App\Models\Refund\Info as Refund;
use App\Models\Refund\Log;
use App\Models\Refund\Log as RefundLog;
use Exception;
use Illuminate\Support\Facades\Auth;

class RefundService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Refund();
    }

    /**
     * @param $data
     * @return int|mixed
     * @throws Exception
     */
    public function addOne($data)
    {
        $refund_id = parent::addOne([
            "order_id" => $data['order_id'],
            "refund_sn" => getSn('R', 'refund', 'refund_sn'),
            'status' => 0,
            'customer_id' => Auth::id() ?? 0
        ]);
        if (!$refund_id) {
            throw new Exception("发起售后失败");
        }
        $this->model = new RefundGoods();
        foreach ($data['goods'] as $item) {
            $res = $this->model->create([
                'refund_id' => $refund_id,
                'goods_id' => $item['goods_id'],
                'goods_name' => $item['goods_name'],
                'quantity' => $item['quantity'],
                'sku_code' => $item['sku_code'],
                'sku_name' => $item['sku_name'],
                'amount' => $item['amount'],
                'total_amount' => bcmul($item['amount'], $item['quantity']),
            ]);
            if (!$res) {
                throw new Exception("发起售后失败");
            }
        }
        return $refund_id;
    }

    /**
     * 添加操作日志
     * @param $refund_id
     * @param $title
     * @param string $content
     * @param int $user_id
     * @return int|mixed
     */
    public function addLog($refund_id, $title, string $content = '', int $user_id = 0)
    {
        $this->model = new Log();
        return parent::addOne([
            'refund_id' => $refund_id,
            'title' => $title,
            'content' => !empty($content) ? $content : $title,
            'user_id' => $user_id ?? 1,
            'user_name' => Auth::user()['name'] ?? "系统管理员",
            'created_at' => time()
        ]);
    }

}
