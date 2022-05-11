<?php

namespace extend\lib;

use GuzzleHttp\Client;

class UnionPay
{
    private static $url = "";

    public static function pay($params)
    {
        $url = "gateway/api/frontTransReq.do";
        $info = [
            "bizType" => "",
            "txnTime" => date("YmdHis"),
            "backUrl" => "",
            "currencyCode" => "156",
            "txnAmt" => $params['actual_amount'],
            "txnType" => "01",
            "txnSubType" => "01",
            "accessType" => "0",
            ""
        ];
    }

    private static function makeRequest($url, $params)
    {
        $baseUri = env('APP_ENV', 'local') == 'local' ?
            "https://gateway.test.95516.com" : "https://gateway.95516.com";
        $client = new Client(['base_uri' => $baseUri]);
        $info = [
            'version' => "5.1.0",
            "encoding" => "UTF-8",
            "bizType" => "",
        ];
        try {
            $res = $client->post($url, [

            ]);
        } catch (\Exception$exception) {

        }
    }
}
