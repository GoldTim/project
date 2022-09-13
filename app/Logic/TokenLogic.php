<?php

namespace App\Logic;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Redis;

class TokenLogic
{
    const Token_CACHE_PR = 'token_';
    const REFRESH_CACHE_PR = 'refresh_token_';

    public function generateAccessToken($data):string
    {
        $time = time();
        $token = [
            'iat' => $time,
            'exp' => $time + config(''),
            'data' => $data
        ];
//        $jwt = JW
        $jwt = JWT::encode($token, '');
        $key = self::Token_CACHE_PR . '_' . $data['customer_id'];
        app('redis')->setex($key, '', $jwt);
        return $jwt;
    }

    public function verifyToken($token): array
    {
        try {
            $result = JWT::decode($token, '');
            if (!$result) {
                throw new \Exception("令牌无效，请重新登录");
            }
            $key = self::Token_CACHE_PR . '_' . $result['data']['customer_id'];
            $redisToken = app('redis')->get($key);
            if ($redisToken != $token) {
                throw new \Exception("令牌无效，请重新登录");
            }
            return succeed($result['data']);
        } catch (\Exception$exception) {
            return error($exception->getMessage());
        }
    }

    public function refreshToken($customer_id)
    {

    }
}
