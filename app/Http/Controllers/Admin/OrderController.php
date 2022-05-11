<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Logic\Admin\OrderLogic;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = new OrderLogic();
    }

    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $where = [];
            $status = $this->getData('status');

            if ($status['succeed']) {
                $where[] = ['status', '=', $status['data']];
            }
            $data = $this->logic->index($where, $this->page, $this->limit);
            return $this->ajaxPager($data);
        }
        return view('admin.order.index');
    }
}
