<?php

namespace App\Models\Refund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = "refund_log";
    protected $fillable = [
        "refund_id", "title", "content", "customer_id", "customer_name"
    ];
}
