<?php

namespace App\Logic\User;

use App\Services\CustomerService;

class UserLogic
{
    protected $service;

    public function __construct()
    {
        $this->service = new CustomerService();
    }

    public function login($params)
    {
        $method = new \ReflectionMethod(get_called_class(), "oAuth" . ucfirst(""));
        $method->setAccessible(true);
        $result = $method->invoke($this, $params);
        if (!$result['succeed']) {
            return $result;
        }
        $data = $result['data'];
        return succeed([]);
    }

    public function oAuthByPass($params)
    {
        $customer = $this->service->getOneForSearch([
            ['phone', '=', $params['mobile']],
            ['password', '=', $params['password']]
        ]);
        if (!$customer) {
            return error("账户信息不存在");
        }
        return [
            'customer_id' => $customer['id']
        ];
    }

    public function editInfo($customer_id, $params)
    {

    }
}
