<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GoodsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('good', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('goods_name');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('goods_sku', function (Blueprint $table) {
            $table->id();
            $table->integer('goods_id');
            $table->string('sku_code');
            $table->string('sku_name');
            $table->integer('origin_price');
            $table->integer('actual_price');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('goods_inventory', function (Blueprint $table) {
            $table->id();
            $table->integer('goods_id');
            $table->integer('sku_id');
            $table->integer('stock');
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
