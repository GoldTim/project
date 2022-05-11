<?php

namespace App\Models\Good;

use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    protected $table = "goods";

    public function sku()
    {
        return $this->hasMany(Sku::class, 'goods_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'goods_id');
    }
}
