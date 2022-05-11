<?php

namespace App\Models\Refund;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;

    protected $table = "refund";

    protected function ActualAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    protected function RefundAmount(): Attribute
    {
        return new Attribute(function ($value) {
            return bcdiv($value, 100, 2);
        }, function ($value) {
            return bcmul($value, 100);
        });
    }

    public function goods()
    {
        return $this->hasMany(Goods::class, 'refund_id');
    }

    public function log()
    {
        return $this->hasMany(Log::class, 'refund_id')->orderByDesc('created_at');
    }
}
