<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use extend\lib\WeChat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class NotifyController
{
    public function weChatPay()
    {
        $data = file_get_contents('php://input');
        $data = xmlToArray($data);
        $order_sn = $data['out_trade_no'] ?? "";
        $key = "notifyBy_" . $order_sn;
        $bool = Redis::get($key);
        if ($bool) {
            Log::channel('weChatPay')->error('订单回调中', [
                "out_trade_no" => $order_sn
            ]);
            echo "ERROR";
            exit();
        }
        Log::channel('weChat')->info('支付回调开始', [
            "data" => $data
        ]);
        Redis::set($key, $order_sn);
        $service = new OrderService();
        DB::beginTransaction();
        $result = "SUCCESS";
        try {
            $service = new OrderService();
            $res = $service->payNotify($order_sn);
            Log::channel('weChatPay')->info('支付回调成功', ['order_sn' => $order_sn]);
            DB::commit();
        } catch (\Exception$exception) {
            DB::rollBack();
            Log::channel('weChatPay')->error('订单支付回调失败,原因：' . $exception->getMessage(), ['data' => $data]);
            $result = "ERROR";
        }
        Redis::del($key);
        echo $result;
        exit();
    }

    public function weChatRefund()
    {
        $data = file_get_contents('php://input');
        $data = xmlToArray($data);
        $weChat = new WeChat();
        $data = $weChat::decrypt(base64_decode($data['req_info']));
    }
}
