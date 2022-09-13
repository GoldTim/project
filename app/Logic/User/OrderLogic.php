<?php

namespace App\Logic\User;

use App\Services\GoodsService;
use App\Services\OrderService;
use App\Services\RefundService;
use Exception;
use extend\lib\AliPay;
use extend\lib\PayPal;
use extend\lib\WeChat;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ReflectionMethod;
use function error;
use function succeed;

class OrderLogic
{
    protected $service;
    protected $statusArray = [
        '未支付', ''
    ];

    protected $payArray = [
        1 => "PayPal",
        2 => "WeChatNative",#微信扫码支付
        3 => "WeChatApp",#微信APP支付
        4 => "WeChatH5",#微信H5
        5 => "WeChatJsApi",#
        6 => "WeChatApplet",#微信小程序
        7 => "AliPayPaymentCode",#付款码
    ];

    public function __construct()
    {
        $this->service = new OrderService();
    }

    /**
     * @param $where
     * @param $page
     * @param $limit
     * @param $sort
     * @param $order
     * @return array
     */
    public function getList($where, $page, $limit, $sort, $order): array
    {
        $field = [
            'id', 'order_sn', 'created_at', 'status', 'order_title', 'order_amount', 'actual_amount', 'order_remark'
        ];
        $data = $this->service->getList($where, $field, $page, $limit, $sort, $order);
        $data['list'] = collect($data['list'])->map(function ($item) {
            return array_merge($item, [
                'status_text' => $this->statusArray[$item['status']]
            ]);
        })->toArray();
        return $data;
    }

    /**
     * 添加订单
     * @param $params
     * @return array
     * @throws Exception
     */
    public function addOrder($params): array
    {
        $params = $this->checkData($params);
        if (!$params['succeed']) {
            return $params;
        }
        $params = $params['data'];
        DB::beginTransaction();
        try {
            $ids = [];
            $payment_amount = 0;
            foreach ($params as $item) {
                $order_id = $this->service->addOne($item);
                if (!$order_id) {
                    throw new Exception("提交订单失败");
                }
                $ids[] = $order_id;
                $res = $this->service->addLog($order_id, '提交订单');
                if (!$res) {
                    throw new Exception("提交订单失败");
                }
                $payment_amount = bcadd($payment_amount, $item['order']['actual_amount'] ?? 0);
            }
            $res = $this->service->addPay($ids, $payment_amount);
            if (!$res) {
                throw new Exception("提交订单失败");
            }
            DB::commit();
            return succeed(["pay_sn" => $res->pay_sn]);
        } catch (Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    private function checkData($params): array
    {
        $service = new GoodsService();
        $goodList = $service->getSkuList([
            ['goods_id', 'in', collect($params['goods'])->pluck('goods_id')],
            ['sku_code', 'in', collect($params['goods'])->pluck('sku_code')]
        ]);
        $result = [];
        $goodResult = [];
        foreach ($params['goods'] as $item) {
            $good = $goodList->where('id', $item['goods_id'])->where('sku_code', $item['sku_code'])->first();
            if (!$good) {
                return error('商品不存在');
            }
            if ($item['quantity'] <= 0) {
                return error("数量必须大于0");
            }
            if ($good['inventory'] < $item['quantity']) {
                return error("库存不足");
            }
            $goodResult[$good['shop_id']][] = $good;
        }
        if (empty($goodResult)) {
            return error("商品不可为空");
        }
        foreach ($goodResult as $key => $item) {
            $result[] = [
                "order" => [
                    'shop_id' => $key,
                    'order_title' => collect($item)->pluck('goods_name')->implode(","),
                    "order_amount" => collect($item)->sum("origin_price"),
                    "actual_amount" => collect($item)->sum("origin_price"),
                    "ship_amount" => 0,
                    "dis_amount" => 0,
                    "dis_point" => 0,
                    "order_remark" => ""
                ],
                "order_goods" => $item
            ];
        }
        return succeed($result);
    }

    /**
     * 发起支付
     * @param $params
     * @return array
     * @throws ReflectionException
     */
    public function payOrder($params): array
    {
        $order = $this->service->getOneForSearch(['order_sn' => $params['order_sn']]);
        if (!$order) {
            return error("订单不存在");
        }
        if ($this->statusArray[$order['status']] != "未支付") {
            return error("该订单不可支付");
        }
        $methodName = $this->payArray[$params['pay_method']] ?? "";
        if (empty($methodName)) {
            return error("支付方式不存在");
        }
        $method = new ReflectionMethod(get_called_class(), 'payBy' . ucfirst($methodName));
        $method->setAccessible(true);
        return $method->invoke($this, $order);
    }

    /**
     * 发起退款
     * @param $params
     * @return array
     * @throws Exception
     */
    public function refundOrder($params): array
    {
        $order = $this->service->getOne($params['order_id']);
        if (!$order) {
            return error("获取订单信息失败");
        }
        if ($this->statusArray[$order['status']] == '未支付') {
            return error("订单未支付，无法申请售后");
        }
        $service = new RefundService();
        try {
            $res = $this->service->renewal($params['order_id'], [

            ]);
            if (!$res) {
                throw new Exception("");
            }
            $refund_id = $service->addOne($params);
            if (!$refund_id) {
                throw new Exception("售后失败");
            }
            $res = $service->addLog($refund_id, "用户发起售后");
            if (!$res) {
                throw new Exception("发起售后失败");
            }
            DB::commit();
            return succeed();
        } catch (Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    /**
     * 获取订单支付状态
     * @param $order_sn
     * @return array
     * @throws Exception
     */
    public function queryOrder($order_sn): array
    {
        $order = $this->service->getOneForSearch(['order_sn' => $order_sn]);
        if (!$order) {
            return error("订单不存在");
        }
        $pay_method = $this->payArray[$order['pay_method']];

        $bool = false;
        if ($pay_method == 'PayPal') {
            $result = PayPal::query();
            if (!$result['succeed']) {
                return $result;
            }
            $bool = true;
        } elseif (strpos($pay_method, "AliPay") !== false) {
            $result = AliPay::query();
            if (!$result['succeed']) {
                return $result;
            }
            $bool = true;
        } elseif (strpos($pay_method, "WeChat") !== false) {
            $result = WeChat::query($order['pay_sn']);
            if (!$result['succeed']) {
                return $result;
            }
            $bool = true;
        } else {
            return error("查询失败");
        }
        return succeed();
    }

    /**
     * 订单通知
     * @param $params
     * @return array
     * @throws Exception
     */
    public function notifyOrder($params): array
    {
        $pay_sn = $params['order_sn'] ?? "";
        if (empty($pay_sn)) {
            return error("获取订单失败");
        }
        $order = $this->service->getOneForSearch(['pay_sn' => $pay_sn]);
        if (!$order) {
            return error("获取订单失败");
        }

        DB::beginTransaction();
        try {
            $res = $order->update([
                'pay_time' => time(),
                'status' => 1
            ]);
            if (!$res) {
                throw new Exception("修改订单状态失败");
            }
            $res = $this->service->addLog($order['id'], '订单支付成功');
            if (!$res) {
                throw new Exception("添加订单变更流水失败");
            }
            DB::commit();
        } catch (Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
        return succeed();
    }

    /**
     * 取消订单
     * @param $params
     * @return array
     * @throws Exception
     */
    public function cancelOrder($params): array
    {
        $order = $this->service->getOne($params['order_id']);
        if (!$order) {
            return error("订单不存在");
        }
        DB::beginTransaction();
        try {
            $res = $this->service->renewal($order['id'], []);
            if (!$res) {
                throw new Exception("修改订单状态失败");
            }
            $res = $this->service->addLog($order['id'], '取消订单', $params['content']);
            if (!$res) {
                throw new Exception("取消订单失败");
            }
            DB::commit();
            return succeed();
        } catch (Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    private function payByPayPal($order_info): array
    {
        $params = [
            'detail' => [
                'subtotal' => $order_info['order_amount'],
                'shipping' => $order_info['shipping_amount'],
                'shipping_discount' => $order_info['dis_amount'],
            ],
            'actual_amount' => $order_info['actual_amount']
        ];
        return PayPal::pay($params);
    }

    private function payByWeChatNative($order_info): array
    {
        $params = [];
        return WeChat::pay($params);
    }

    private function payByAliPay($order_info): array
    {
        $params = [];
        return AliPay::pay($params);
    }
}
