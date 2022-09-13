<?php

namespace App\Logic\Admin;

use App\Services\OrderService;

class OrderLogic
{
    protected $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function index($where, $page, $limit)
    {

        $result = $this->service->getList($where, ['*'], $page, $limit);
        foreach ($result['data'] as &$item) {

        }
        return $result;
    }
}
