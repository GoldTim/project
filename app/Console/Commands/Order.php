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
                [$ids, $update, $logArray] = $this->overTime();
                break;
            case 'cancel':
                [$ids, $update, $logArray] = $this->cancel();
                break;
            case 'listen':
            default:
                [$ids, $update, $logArray] = $this->cancel();
                $result = $this->overTime();
                $ids = array_merge($ids, $result[0]);
                $update = array_merge($update, $result[1]);
                $logArray = array_merge($logArray, $result[2]);
                break;
        }
        \App\Jobs\Order::dispatch($ids, $update, $logArray);
        echo "任务部署成功";
        return 0;
    }

    public function cancel(): array
    {
        $time = strtotime("-15 minute");
        $ids = $this->service->getAllList([
            ['status', '=', 0],
            ['pay_time', '!=', 0],
            ['created_at', '<', $time]
        ], ['id'])->toArray();
        $logArray = [];
        foreach ($ids as $id) {
            $logArray[] = [
                'order_id' => $id,
                'title' => '超出时间仍未支付.系统自动取消订单'
            ];
        }
        $update = [
            'status' => 8,
            'updated_at' => time(),
            'close_type' => 0,
            'close_reason' => '超时未支付'
        ];
        return [$ids, $update, $logArray];
    }

    public function overTime(): array
    {
        $ids = $this->service->getAllList([['status', '=', 2], ['confirm_time', '<', time()]], ['id'])->toArray();
        $logArray = [];
        foreach ($ids as $id) {
            $logArray[] = [
                'order_id' => $id,
                'title' => '超出时间仍未支付.系统自动取消订单'
            ];
        }
        $update = ['status' => 3, 'updated_at' => time()];
        return [$ids, $update, $logArray];
    }
}
