<?php

namespace App\Models\Good;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $table = "goods_sku";

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'sku_id')->where('goods_id', $this['goods_id']);
    }

    public function goods()
    {
        return $this->belongsTo(Info::class, 'goods_id');
    }
}
