<?php

namespace extend\lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;

class PayPal
{
    public static function pay($info)
    {
        $url = "v2/checkout/orders";
        $client = new Client(["base_uri" => "https://api-m.sandbox.paypal.com"]);
        $token = self::getToken();
        if (!$token['succeed']) {
            return $token;
        }
        $token = $token['data'];
//        dd("Bearer " . $token);
        try {
            $res = $client->post($url, [
                "headers" => [
                    "Authorization" => "Bearer " . $token,
                    "Content-Type" => "application/json"
                ], "json" => [
                    "intent" => "CAPTURE",
                    "purchase_units" => [
                        [
                            "amount" => [
                                "currency_code" => "USD",
                                "value" => 0.01
                            ]
                        ]
                    ]
                ]
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return succeed($body);
        } catch (ClientException $exception) {
            $body = json_decode($exception->getResponse()->getBody()->getContents(), true);
            $body = collect($body['details'])->first();
            return error($body['description'] ?? "发起请求失败");
        } catch (GuzzleException $e) {
            return error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 查询订单支付状态
     * @return array
     */
    public static function query():array
    {
        return [];
    }

    /**
     * 获取令牌
     * @return array
     */
    public static function getToken(): array
    {
        $token = Redis::get('paypal_token');
        if ($token) {
            return succeed($token);
        }
        $config = config('constant.paypal');
        $url = "v1/oauth2/token";
        $client = new Client(['base_uri' => 'https://api-m.sandbox.paypal.com']);
        try {
            $res = $client->post($url, [
                'form_params' => ['grant_type' => 'client_credentials'],
                'headers' => [
                    "Content-Type" => "application/x-www-form-urlencoded",
                    'Authorization' => "Basic " . base64_encode($config['clientId'] . ':' . $config['secret'])
                ]
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            Redis::set('paypal_token', $body['access_token']);
            return succeed($body['access_token']);
        } catch (ClientException $exception) {
            $body = json_decode($exception->getResponse()->getBody()->getContents(), true);
            $body = collect($body)->first();
            return error($body['sub_msg'] ?? "发起请求失败");
        } catch (GuzzleException $e) {
            return error($e->getMessage(), $e->getCode());
        }
    }
}
