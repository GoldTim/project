<?php

namespace App\Logic\User;

use App\Services\CartService;

class CartLogic
{
    protected $service;

    public function __construct()
    {
        $this->service = new CartService();
    }

    public function save()
    {

    }

    private function checkData($params)
    {

    }
}
