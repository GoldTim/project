<?php

namespace App\Services;

use App\Models\Good\Info as Goods;
use App\Models\Good\Sku;

class GoodsService extends BaseService
{
    public function __construct()
    {
        $this->model = new Goods();
    }

    public function getSkuList($where)
    {
        $this->model = new Sku();
        $list = $this->getAllList($where);
        return $list->map(function ($item) {
            return [
                'shop_id' => $item['goods']['shop_id'],
                'origin_price' => $item['origin_price'],
                'actual_price' => $item['actual_price'],
                'inventory' => $item['inventory']['stock'],
                'goods_id' => $item['goods_id'],
                'sku_code' => $item['sku_code'],
                'sku_name' => $item['sku_name'],
                'goods_name' => $item['goods']['goods_name']
            ];
        });
    }
}
