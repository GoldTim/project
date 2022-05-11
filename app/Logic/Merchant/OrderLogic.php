<?php

namespace App\Logic\Merchant;

use App\Services\OrderService;
use App\Services\RefundService;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderLogic
{
    protected $service;
    protected $shop_id;
    protected $refundService;

    public function __construct()
    {
//        $this->service = new OrderService();
        $this->shop_id = 1;
    }

    protected $statusArray = [
        '待支付', '待发货', '待签收', ''
    ];

    /**
     * 商户订单列表
     * @param $where
     * @param $page
     * @param $limit
     * @param $sort
     * @param $order
     * @return array
     */
    public function getList($where, $page, $limit, $sort, $order): array
    {
        $this->service = new OrderService();
        $where[] = ['shop_id', '=', $this->shop_id];
        $result = $this->service->getList($where, ["*"], $page, $limit, $sort, $order);
        foreach ($result['list'] as $item) {
            $item['status_text'] = $this->statusArray[$item['status']];
        }
        return $result;
    }

    /**
     * 发货
     * @param $params
     * @return array
     * @throws Exception
     */
    public function deliveryOrder($params): array
    {
        $where = [
            'shop_id', '=', $this->shop_id,
            'order_id' => $params['order_id']
        ];
        $this->service = new OrderService();
        $order = $this->service->getOneForSearch($where);
        if (!$order) {
            return error("获取订单信息失败");
        }
        try {
            DB::beginTransaction();
            $res = $order->update([
                'confirm_time' => strtotime('+10 day'),
                'status' => 2,
                'shipping_status' => 1
            ]);
            if (!$res) {
                throw new Exception("发货失败");
            }
            $res = $this->service->delivery([
                'order_goods_ids' => [],
                'shipping_no' => $params['shipping_no'],
                'express_no' => $params['express_no']
            ]);
            if (!$res) {
                throw new Exception("填写物流信息失败");
            }
            $res = $this->service->addLog($params['order_id'], '商家发货');
            if (!$res) {
                throw new Exception('添加操作记录失败');
            }
            DB::commit();
            return succeed([]);
        } catch (Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    /**
     * 修改订单信息
     * @param $params
     * @return array
     * @throws Exception
     */
    public function updateOrder($params): array
    {
        $this->service = new OrderService();
        DB::beginTransaction();
        try {
            $res = $this->service->renewal($params['order_id'], []);
            if (!$res) {
                throw new Exception("修改信息失败");
            }
            $res = $this->service->addLog($params['order_id'], '商户修改信息');
            if (!$res) {
                throw new Exception("添加操作日志失败");
            }
            DB::commit();
            return succeed([]);
        } catch (Exception $exception) {
            DB::rollback();
            return error($exception->getMessage());
        }
    }

    /**
     * 同意退货
     * @param $refund_id
     * @param $params
     * @return array
     * @throws Exception
     */
    public function agreeRefund($refund_id, $params): array
    {
        $this->service = new RefundService();
        $res = $this->checkRefund($refund_id);
        if (!$res['succeed']) {
            return $res;
        }
        DB::beginTransaction();
        try {
            $res = $this->service->renewal($refund_id, [
                'status' => 1,
                'actual_amount' => 0,
                'refund_amount' => 0
            ]);
            if (!$res) {
                throw new \Exception("同意售后失败");
            }
            $res = $this->service->addLog($refund_id, '同意售后', '商家同意售后');
            if (!$res) {
                throw new \Exception("同意售后失败");
            }
            DB::commit();
            return succeed([]);
        } catch (\Exception$exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    /**
     * 拒绝退货
     * @param $refund_id
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function rejectRefund($refund_id, string $reason = ''): array
    {
        $this->service = new RefundService();
        $res = $this->checkRefund($refund_id);
        if (!$res['succeed']) {
            return $res;
        }
        DB::beginTransaction();
        try {
            $res = $this->service->renewal($refund_id, [
                'status' => 3,
                'reason' => $reason
            ]);
            if (!$res) {
                throw new \Exception("拒绝售后失败");
            }
            $res = $this->service->addLog($refund_id, '拒绝售后', $reason);
            if (!$res) {
                throw new \Exception("拒绝售后失败");
            }
            DB::commit();
            return succeed([]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return error($exception->getMessage());
        }
    }

    /**
     * 检查订单数据
     * @param $id
     * @return array
     */
    private function checkRefund($id): array
    {
        $refund = $this->service->getOneForSearch([
            ['id', '=', $id],
            ['shop_id', '=', $this->shop_id]
        ]);
        if (!$refund) {
            return error("售后订单不存在");
        }
        if ($refund['status'] != 0) {
            return error("订单状态异常");
        }
        return succeed([]);
    }
}
