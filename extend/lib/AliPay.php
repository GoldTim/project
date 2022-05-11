<?php

namespace extend\lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class AliPay
{
    private static $signType = "RSA2";

    public static function pay($params): array
    {
        $info = $params['info'];
        $url = self::Assemble($params['method'], $info, [
            "notify_url" => route('alipay.pay.notify')
        ]);
        return self::makeRequest($url);
    }

    public static function query(): array
    {
        return self::makeRequest("");
    }

    private static function makeRequest($query): array
    {
        $baseUri = env('APP_ENV', 'local') == 'local' ?
            'https://openapi.alipaydev.com' : "https://openapi.alipay.com";
        $client = new Client(['base_uri' => $baseUri]);
        try {
            $res = $client->get('gateway.do', ['query' => $query]);
            return succeed($res->getBody()->getContents());
        } catch (ClientException $exception) {
            $body = json_decode($exception->getResponse()->getBody()->getContents(), true);
            $body = collect($body)->first();
            return error($body['sub_msg'] ?? "发起请求失败");
        } catch (GuzzleException $e) {
            return error($e->getMessage(), $e->getCode());
        }
    }

    private static function Assemble($method, $params, $mainArray): string
    {
        $alipay = config('constant.alipay');
        $info = [
            'app_id' => $alipay['appId'],
            'method' => $method,
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => collect($params)->toJson()
        ];
        if (isset($mainArray['notify_url'])) {
            $info['notify_url'] = $mainArray['notify_url'];
        }
        ksort($info);
        $string = "";
        $i = 0;
        foreach ($info as $k => $v) {
            if (self::checkEmpty($v) === false && "@" != substr($v, 0, 1)) {
                $string .= $i == 0 ? "$k" . "=" . "$v" : "&" . "$k" . "=" . "$v";
                $i++;
            }
        }
        unset($k, $v);
        $rsaPrivateFile = "";
        $res = self::checkEmpty($rsaPrivateFile) ? "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($alipay['privateKey'], 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----"
            : openssl_get_privatekey(file_get_contents($rsaPrivateFile));
        ($res) or die("私钥格式错误，请检查RSA私钥配置");
        "RSA2" == self::$signType ? openssl_sign($string, $sign, $res, OPENSSL_ALGO_SHA256) : openssl_sign($string, $sign, $res);

        self::checkEmpty($rsaPrivateFile) ?: openssl_free_key($res);
        $info['sign'] = base64_encode($sign);
        foreach ($info as &$v) {
            $v = self::charset($v, "utf-8");
        }
        return http_build_query($info);
    }

    /**
     * 设置字符集
     * @param $data
     * @param $targetCharset
     * @return null|string|string[]
     */
    static function charset($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = "utf-8";
            if (strcasecmp($fileType, $targetCharset) != 0)
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
        }
        return $data;
    }

    /**
     * 检查是否为空
     * @param $value
     * @return bool
     */
    static function checkEmpty($value): bool
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }
}
