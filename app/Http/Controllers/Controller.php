<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $_requestData;

    protected $page = 1;
    protected $limit = 10;
    protected $soft = "id";
    protected $order = "desc";

    public function __construct()
    {
        $this->parseRequest();
    }

    private function parseRequest()
    {
        $this->_requestData = getPostData();
        $this->page = $this->getData('page', true, 1)['data'];
        $this->limit = $this->getData('limit', true, 10)['data'];
    }

    /**
     * @param $key
     * @param $optional
     * @param $default
     * @return array
     */
    protected function getData($key = '', $optional = false, $default = ''): array
    {
        if ($key !== null && empty($key))
            return succeed($this->_requestData);
        if (@array_key_exists($key, $this->_requestData))
            return succeed($this->_requestData[$key]);

        if (!@array_key_exists($key, $this->_requestData) && $optional) return succeed($default); //可选参数且接收不到此参数
        return error('获取参数不存在或缺少参数:' . $key);
    }


}
