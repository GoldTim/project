<?php

namespace extend\lib;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use SodiumException;
use function openssl_decrypt;
use function openssl_get_cipher_methods;
use function Sodium\crypto_aead_aes256gcm_decrypt;
use function Sodium\crypto_aead_aes256gcm_is_available;
use function sodium_crypto_aead_aes256gcm_decrypt;
use function sodium_crypto_aead_aes256gcm_is_available;

class WeChat
{
    protected static $apiCert = "extend/lib/cert/apiclient_cert.pem";
    protected static $apiKey = "extend/lib/cert/apiclient_key.pem";
    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;

    /**
     * 发起支付
     * @param $params
     * @return array
     */
    public static function pay($params): array
    {
        $out_trade_no = $params['out_trade_no'] ?? "";
        if (empty($out_trade_no)) {
            return error("订单编号不可为空");
        }
        $config = config('constant.wechat', []);
        if (empty($config)) {
            return error('获取配置失败');
        }

        $time = time();
        $date = date("Y-m-d", $time + 1800) . 'T' . date("H:i:s+08:00", $time + 1800);
        $info = [
            'appid' => $config['appId'],
            'mchid' => $config['mchId'],
            'out_trade_no' => $params['out_trade_no'],
            'description' => $params['body'],
            'notify_url' => env('APP_URL') . '/order/notifyWechatByPay',
            'amount' => [
                'total' => $params['total_fee'],
                'currency' => 'CNY'
            ],
            'time_expire' => $date,
        ];
        if ($params['payMethod'] == "JSAPI") {
            $openid = $params['openid'] ?? "";
            if (empty($openid)) {
                return error("openId不可为空");
            }
            $params['payer'] = ["openid" => $openid];
        }
        $url = "/v3/pay/transactions/" . $params['payMethod'];
        return self::makeRequest($url, 'post', $info, $config);
    }


    /**
     * 查询订单支付状态
     * @param $order_no
     * @return array
     */
    public static function query($order_no): array
    {
        if (empty($order_no)) {
            return error("订单编号不可为空");
        }
        $config = config("constant.wechat", []);
        $url = "v3/pay/transaction/out-trade-no/" . $order_no . "?mch_id=" . $config['mchId'];
        $res = self::makeRequest($url, 'get');
        if (!$res['succeed']) {
            return $res;
        }
        $data = $res['data'];
        if (in_array($data['trade_state'], ["REFUND", "NOTPAY", "REVOKED", "PAYERROR", "CLOSED"])) {
            $status = "false";
        } else {
            $status = $data['trade_state'] == "SUCCEED" ? "true" : "wait";
        }
        return succeed($status);
    }

    /**
     * 发起退款
     * @param $params
     * @return array
     */
    public static function refund($params): array
    {
        $out_trade_no = $params['out_trade_no'] ?? "";
        if (empty($out_trade_no)) {
            return error("订单编号不可为空");
        }
        $refund_no = $params['refund_no'] ?? "";
        if (empty($refund_no)) {
            return error("退款单号不可为空");
        }
        $order_amount = $params['order_amount'] ?? 0;
        if (empty($order_amount)) {
            return error("订单金额不可为0");
        }
        $refund_amount = $params['refund_amount'] ?? 0;
        if (empty($refund_amount)) {
            return error("退款金额不可为0");
        }
        $info = [
            "out_trade_no" => $out_trade_no,
            "out_refund_no" => $refund_no,
            "reason" => $params['reason'],
            "notify_url" => "",
            "amount" => [
                "refund" => bcmul($refund_amount, 100),
                "total" => bcmul($order_amount, 100),
                "currency" => "CNY"
            ]
        ];
        $url = "v3/refund/domestic/refunds";
        return self::makeRequest($url, 'post', $info);
    }

    /**
     * 查询订单退款状态
     * @param $refund_no
     * @return array
     */
    public static function queryRefund($refund_no): array
    {
        if (empty($refund_no)) {
            return error("退款单号不可为空");
        }
        $url = "v3/refund/domestic/refunds/" . $refund_no;
        return self::makeRequest($url, 'get');
    }

    /**
     * 回调报文解密
     * @param $associatedData
     * @param $nonceStr
     * @param $ciphertext
     * @return array
     */
    public static function decrypt($associatedData, $nonceStr, $ciphertext): array
    {
        $aseKey = config('constant.wechat.apiKey');
        if (strlen($aseKey) != self::KEY_LENGTH_BYTE) {
            return error("无效的APIKEY，长度应为32个字节");
        }
        $ciphertext = base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return error("解密失败");
        }

        if (function_exists('sodium_crypto_aead_aes256gcm_is_available') && sodium_crypto_aead_aes256gcm_is_available()) {
            try {
                $str = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aseKey);
            } catch (SodiumException $e) {
                return error($e->getMessage());
            }
            return succeed($str);
        }
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && crypto_aead_aes256gcm_is_available()) {
            $str = crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aseKey);
            return succeed($str);
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

            $str = openssl_decrypt($ctext, 'aes-256-gcm', $aseKey, OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
            return succeed($str);
        }
        return error("解密失败");
    }

    /**
     * 验证签名
     * @param $sign
     * @return void
     */
    public static function checkSign($sign)
    {

    }

    /**
     * 发起请求
     * @param string $url
     * @param string $method
     * @param array $info
     * @param array $config
     * @return array
     */
    private static function makeRequest(string $url, string $method, array $info = [], array $config = []): array
    {
        if (empty($config)) {
            $config = config('constant.wechat', []);
        }
        $token = self::makeToken($config['mchId'], $method, $url, json_encode($info));
        $client = new Client([
            "base_uri" => "https://api.mch.weixin.qq.com",
            "headers" => [
                "Content-Type" => "application/json;charset=UTF-8",
                "Accept" => "application/json",
                "Authorization" => "WECHATPAY2-SHA256-RSA2048 " . $token
            ]
        ]);
        try {
            $res = $client->request($method, $url, [
                'json' => $info
            ]);
            $body = $res->getBody()->getContents();
            $body = json_decode($body, true);
            return succeed($body);
        } catch (ClientException $exception) {
            $body = $exception->getResponse()->getBody()->getContents();
            $body = json_decode($body, true);
            return error($body['message'] ?? "请求失败");
        } catch (GuzzleException $exception) {
            return error($exception->getMessage(), $exception->getCode());
        } catch (Exception$e) {
            return error("请求失败");
        }
    }

    /**
     * 组装验证令牌
     * @param string $data
     * @param string $mchId
     * @param string $method
     * @param string $url
     * @return string
     */
    private static function makeToken(string $mchId, string $method, string $url, string $data = ''): string
    {
        $nonce_str = Str::random();
        $time = time();

        $str = strtoupper($method) . "\n" . $url . "\n" . $time . "\n" . $nonce_str . "\n" . $data . "\n";

        $key = file_get_contents(base_path(self::$apiKey));
        $raw_sign = "";
        openssl_sign($str, $raw_sign, $key, "SHA256");
        $sign = base64_encode($raw_sign);
        $str = file_get_contents(base_path(self::$apiCert));
        $str = openssl_x509_parse($str);
        $serialNo = $str['serialNumberHex'];
        return sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $mchId, $nonce_str, $time, $serialNo, $sign);
    }

}
