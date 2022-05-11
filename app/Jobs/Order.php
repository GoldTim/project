<?php

namespace App\Jobs;

use App\Services\OrderService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $ids = [];
    protected $update = [];
    protected $log = [];

    public function __construct($ids, $update, $log)
    {
        //
        $this->ids = $ids;
        $this->update = $update;
        $this->log = $log;
    }

    /**
     * Execute the job.
     *
     * @param OrderService $orderService
     * @return void
     * @throws Exception
     */
    public function handle(OrderService $orderService)
    {
        DB::beginTransaction();
        try {
            $res = $orderService->renewalByWhere(['id' => $this->ids], $this->update);
            if (!$res) {
                throw new Exception("批量修改数据失败");
            }
            $res = $orderService->addBatchLog($this->log);
            if (!$res) {
                throw new Exception("批量插入操作日志失败");
            }
            DB::commit();
            Log::channel('order')->info('队列任务执行成功');
        } catch (Exception$exception) {
            DB::rollBack();
            Log::channel('order')->error('队列任务执行失败');
        }
    }
}
