<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id')->comment('店铺ID');
            $table->tinyInteger('order_type')->comment('订单类型');
            $table->string('order_title')->comment('订单标题');
            $table->integer('customer_id')->comment('用户ID');

            $table->string('order_sn', 50)->comment('订单编号');
            $table->integer('pay_time')->comment('支付时间')->nullable();
            $table->integer('confirm_time')->comment('预计收货时间')->nullable();
            $table->integer('order_amount', '', '')->comment('订单金额');
            $table->integer('actual_amount')->comment('支付金额');
            $table->integer('ship_amount')->comment('运费');
            $table->integer('dis_amount')->comment('优惠金额');
            $table->integer('dis_point')->comment('使用积分');
            $table->string('order_remark')->comment('备注信息')->nullable();
            $table->string('close_reason')->comment('关闭原因')->nullable();
            $table->tinyInteger('shipping_status')->comment('寄出状态');
            $table->tinyInteger('close_type')->comment('关闭类型')->default(0);
            $table->tinyInteger('status')->comment('订单状态')->default(0);
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('order_goods', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('订单ID');
            $table->integer('goods_id')->comment('产品ID');
            $table->string('goods_name', 100)->comment('产品名称');
            $table->string('sku_code', 50)->comment('规格编号');
            $table->string('sku_name', 100)->comment('规格名');
            $table->integer('amount')->comment('单价');
            $table->integer('quantity')->comment('数量');
            $table->integer('total_amount')->comment('总价');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('order_shipping', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('订单ID');
            $table->integer('express_id')->comment('物流信息编号');
            $table->string('shipping_name', 255)->comment('收件人');
            $table->string('shipping_phone')->comment('收件联系方式');
            $table->string('region')->comment('国省市区');
            $table->string('address')->comment('详细地址');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
        Schema::create('order_express', function (Blueprint $table) {
            $table->id();
            $table->string('order_goods_ids');
            $table->string('shipping_no')->comment('物流编号');
            $table->string('express_no')->comment('快递公司编号');
            $table->text('express_content')->comment('物流信息');
            $table->tinyInteger('express_status')->comment('物流状态');
            $table->integer('delivery_time')->comment('发货时间');
            $table->integer('created_at');
            $table->integer('updated_at');
        });

        Schema::create('order_log', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->comment('订单ID');
            $table->string('title', 255)->comment('标题');
            $table->text('content')->comment('详情');
            $table->integer('user_id')->comment('操作人ID');
            $table->integer('user_name')->comment('操作人名称');
            $table->integer('created_at')->comment('操作时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
}
