<?php

namespace App\Services;

use App\Models\Customer\Customer;

class CustomerService extends BaseService
{
    public function __construct()
    {
        $this->model = new Customer();
    }
}
