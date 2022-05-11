<?php

namespace App\Logic\Admin;

use App\Services\OrderService;

class OrderLogic
{
    protected $service;

    public function index($where, $page, $limit)
    {
        $service = new OrderService();

        $result = $service->getList($where, ['*'], $page, $limit);
        return $result;
    }
}
