<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PayModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('order_pay', function (Blueprint $table) {
            $table->id();
            $table->string('order_ids')->comment('支付订单ID集');
            $table->integer('payment_amount')->comment('支付金额');
            $table->string('pay_sn', 50)->comment('支付编号')->nullable();
            $table->tinyInteger('pay_method')->comment('支付类型')->nullable();
            $table->tinyInteger('status');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
