<?php

namespace App\Http\Controllers;

use App\Logic\User\OrderLogic;
use Exception;
use Illuminate\Support\Facades\Validator;
use ReflectionException;

class OrderController extends Controller
{
    protected $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = new OrderLogic();
    }

    public function getList(): array
    {
        $where = [];
        $page = $this->getData('page', false, 1);
        $limit = $this->getData('limit', false, 10);
        $soft = $this->getData('soft', false, 'id');
        $order = $this->getData('order', false, 'desc');
        return $this->logic->getList($where, $page, $limit, $soft, $order);
    }

    /**
     * 提交订单
     * @return array|\Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function submitOrder()
    {
        $params = $this->getData('data');
        if (!$params['succeed']) {
            return $params;
        }
        $params = $params['data'];
        $validator = Validator::make($params, [
            ''
        ]);
        if ($validator->fails()) {
            return error($validator->errors()->first());
        }
        $result = $this->logic->addOrder($params);
        return ajaxDataHandle($result);
    }

    /**
     * 发起支付
     * @return array|\Illuminate\Http\JsonResponse
     * @throws ReflectionException
     */
    public function payOrder()
    {
        $params = $this->getData('data');
        if (!$params['succeed']) {
            return $params;
        }
        $params = $params['data'];
        $validator = Validator::make($params, [
            'order_sn' => 'required',
            'pay_method' => 'required|gt:0'
        ], [
            'order_sn.required' => '订单不存在',
            'pay_method.required' => '支付方式不存在',
            'pay_method.gt' => '支付方式不存在'
        ]);
        if ($validator->fails()) {
            return error($validator->errors()->first());
        }
        $result = $this->logic->payOrder($params);
        return ajaxDataHandle($result);
    }

    /**
     * 取消
     * @return array|\Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function cancelOrder()
    {
        $params = $this->getData('data');
        if (!$params['succeed']) {
            return $params;
        }
        $params = $params['data'];
        $order_id = $params['order_id'] ?? "";
        if (empty($order_id)) {
            return error("订单不存在");
        }
        $result = $this->logic->cancelOrder($order_id);
        return ajaxDataHandle($result);
    }

    /**
     * 微信支付回调
     * @return array
     * @throws Exception
     */
    public function notifyPayByWeChat(): array
    {
        $res = $this->logic->notifyOrder([
            "pay_method" => "",
            "order_sn" => "",
        ]);
        if (!$res['succeed']) {

        }
        return [];
    }

    /**
     * 支付宝回调
     * @return array
     * @throws Exception
     */
    public function notifyPayByAliPay(): array
    {
        $res = $this->logic->notifyOrder([
            "pay_method" => 2,
            "order_sn" => "",
        ]);
        if (!$res['succeed']) {

        }
        return [];
    }
}
