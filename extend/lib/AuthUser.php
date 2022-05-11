<?php

class AuthUser
{

    public static function userId($token = null)
    {
        if (empty($token)) {
            $token = "login_user_id";
        }
        return Cache::get($token) ?? 0;
    }

    public static function userName($token = null)
    {
        return Cache::get('login_user_name') ?? "";
    }

    public static function userInfo($token = null)
    {
        $login_user = Cache::get('login_user');
        if (empty($login_user)) {
            $login_user = null;
        }
        return $login_user;
    }

    /**
     * 设置登录信息
     * @param $user
     * @param $token
     * @return void
     * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public static function cacheLoginUser($user, $token = null)
    {

        if (!empty($user)) {
            Cache::set('login_user', $user);
            Cache::set('login_user_id', $user['id']);
            Cache::set('login_user_name', $user['name']);
        }
    }
}
