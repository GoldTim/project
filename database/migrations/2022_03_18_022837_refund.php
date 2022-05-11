<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Refund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('refund', function (Blueprint $table) {
            $table->id();
            $table->string('refund_sn');
            $table->integer('order_id');
            $table->integer('actual_amount');
            $table->integer('refund_amount');
            $table->tinyInteger('status');
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('customer_id');
        });
        Schema::create('refund_goods', function (Blueprint $table) {
            $table->id();
            $table->integer('refund_id');
            $table->integer('goods_id');
            $table->string('goods_name');
            $table->string('sku_code');
            $table->string('sku_name');
            $table->integer('quantity');
            $table->integer('amount');
            $table->integer('total_amount');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('refund_log', function (Blueprint $table) {
            $table->id();
            $table->integer('refund_id');
            $table->string('title');
            $table->text('content');
            $table->integer('customer_id');
            $table->string('customer_name');
            $table->integer('created_at');
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
