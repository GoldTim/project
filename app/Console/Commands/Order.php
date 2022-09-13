<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class Order extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order {action?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new OrderService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $operation = $this->argument('action');
        switch ($operation) {
            case 'overTime':
                [$ids, $update, $logArray] = $this->overTimeCheck();
                break;
            case 'listen':
            case 'cancel':
            default:
                [$ids, $update, $logArray] = $this->overTimePay();
                break;
        }
        \App\Jobs\Order::dispatch($ids, $update, $logArray);
        echo "任务部署成功";
        return 0;
    }


    /**
     * 超时未支付
     * @return array
     */
    public function overTimePay(): array
    {
        $where = [
            ['status', '=', 0],
            ['pay_time', '=', 0],
            ['create_time', '<', strtotime('-15 minute')]
        ];
        $ids = $this->service->getAll($where, ['id']);
        $logArray = [];
        foreach ($ids as $id) {
            $logArray [] = [
                'order_id' => $id,
                'title' => '超时未支付,系统自动取消订单'
            ];
        }
        $update = ['status' => 8, 'update_time' => time()];
        return !empty($ids) ? [$ids, $update, $logArray] : [[], [], []];
    }

    /**
     * 超时未收货
     * @return array
     */
    public function overTimeCheck(): array
    {
        $where = [
            ['status', '=', 2],
            ['confirm_time', '<', time()]
        ];
        $ids = $this->service->getAll($where, ['id']);
        $logArray = [];
        foreach ($ids as $id) {
            $logArray[] = [
                'order_id' => $id,
                'title' => '超时未确认收货，系统自动确认收货'
            ];
        }
        $update = ['status' => 3, 'update_at' => time()];
        return !empty($ids) ? [$ids, $update, $logArray] : [[], [], []];
    }
}
