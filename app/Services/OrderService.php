<?php

namespace App\Services;

use App\Models\Order\Express;
use App\Models\Order\Goods;
use App\Models\Order\Info as Order;
use App\Models\Order\Pay;
use App\Models\Order\Shipping;
use App\Models\Refund\Info as Refund;
use App\Models\Refund\Goods as RefundGoods;
use App\Models\Order\Log;
use App\Models\Refund\Log as RefundLog;
use Exception;
use Illuminate\Support\Facades\Auth;

class OrderService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Order();
    }

    /**
     * 提交订单
     * @param $data
     * @return int|mixed
     * @throws Exception
     */
    public function addOne($data)
    {
        $order = array_merge($data['order'], [
            'order_sn' => getSn('', 'order', 'order_sn'),
            'customer_id' => Auth::id(),
        ]);
        $id = parent::addOne($order);
        if (!$id) {
            return 0;
        }
        $model = new Goods();
        foreach ($data['order_goods'] as $item) {
            $res = $model->create($item);
            if (!$res) {
                return 0;
            }
        }
        $model = new Shipping();
        $res = $model->create([
            "shipping_name" => "",
            "shipping_phone" => "",
            "region" => "",
            "address" => "",
        ]);
        if (!$res) {
            return 0;
        }
        return $id;
    }

    public function addPay($ids, $amount)
    {
        $this->model = new Pay();
        return $this->model->create([
            'paySn' => getSn("P", "order_pay", "pay_sn"),
            'order_ids' => implode(",", $ids),
            "payment_amount" => bcmul($amount, 100),
            'status' => 0
        ]);
    }

    public function getPay($where)
    {
        $this->model = new Pay();
        return $this->model->where($where)->first();
    }

    /**
     * 添加操作日志
     * @param $order_id
     * @param $title
     * @param string $content
     * @param int $user_id
     * @return int|mixed
     * @throws Exception
     */
    public function addLog($order_id, $title, string $content = '', int $user_id = 0)
    {
        $this->model = new Log();
        return $this->addOne([
            'order_id' => $order_id,
            'title' => $title,
            'content' => !empty($content) ? $content : $title,
            'user_id' => $user_id ?? 1,
            'user_name' => Auth::user()['name'] ?? "系统管理员",
            'created_at' => time()
        ]);
    }

    /**
     * 批量添加操作日志
     * @param $insertArray
     * @return int|mixed
     */
    public function addBatchLog($insertArray)
    {
        $this->model = new Log();
        foreach ($insertArray as &$item) {
            $item = array_merge($item, [
                'content' => $item['content'] ?? $item['title'],
                'user_id' => Auth::id() ?? 1,
                'user_name' => Auth::user()['name'] ?? "系统管理员",
                'created_at' => time()
            ]);
        }
        return $this->addBatch($insertArray);
    }

    public function delivery($params)
    {
        $this->model = new Express();
        return parent::addOne([
            'order_good_ids' => implode(",", $params['goods_id']),
            "shipping_no" => $params['shipping_no'],
            "express_no" => $params['express_no']
        ]);
    }

    public function payOrder($ids)
    {
        $res = $this->renewalByWhere([
            'id' => $ids
        ], [
            'status' => 1,
            'pay_time' => time()
        ]);
        if (!$res) {
            return error("修改失败");
        }
        foreach ($ids as $id) {
            $res = $this->addLog($id, '支付成功');
            if (!$res) {
                return error("支付成功但修改订单失败");
            }
        }
        return [];
    }

    public function getRefund($id)
    {
        $this->model = new Refund();
        return parent::getOne($id);
    }

    /**
     * 售后
     * @param $params
     * @return int|mixed
     * @throws Exception
     */
    public function refund($params)
    {
        $this->model = new Refund();
        $refund_id = parent::addOne([
            "order_id" => $params['order_id'],
            "refund_sn" => getSn('R', 'refund', 'refund_sn'),
            'status' => 0,
            'customer_id' => Auth::id() ?? 0
        ]);
        if (!$refund_id) {
            throw new Exception("发起退款退货失败");
        }
        $this->model = new RefundGoods();
        foreach ($params['goods'] as $item) {
            $res = parent::addOne([
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
                throw new Exception("发起退款退货失败");
            }
        }
        $this->model = new RefundLog();
        $res = parent::addOne([
            'refund_id' => $refund_id,
            'title' => '用户发起售后',
            'content' => '用户发起售后',
            'customer_id' => Auth::id()
        ]);
        if (!$res) {
            throw new Exception("发起售后失败");
        }
        return $refund_id;
    }

    /**
     * 修改售后信息
     * @param $refund_id
     * @param $params
     * @return int|mixed
     * @throws Exception
     */
    public function editRefund($refund_id, $params)
    {
        $this->model = new Refund();
        $res = parent::renewal($refund_id, $params);
        if (!$res) {
            throw new \Exception("修改失败");
        }
        $this->model = new RefundLog();
        return parent::addOne([
            'refund_id' => $refund_id,
            'title' => '修改信息',
            'content' => '修改售后信息',
            'customer_id' => Auth::id(),
        ]);

    }


}
