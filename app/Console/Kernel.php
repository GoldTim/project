<?php

namespace App\Console;

use App\Console\Commands\Order;
use App\Console\Commands\Swoole;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Swoole::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(Order::class)->everyMinute()
            ->withoutOverlapping()
            ->description('处理订单定时任务');
        $schedule->exec('echo "" >' . storage_path('logs/laravel.log'))->everyMinute()
            ->environments(['local'])
            ->withoutOverlapping()
            ->description('清除项目日志内容');

        $schedule->exec('echo "" >' . storage_path('logs/laravel.log'))->weekly()
            ->environments(['production'])
            ->withoutOverlapping()
            ->description('清除项目日志内容');
        $schedule->exec("rm -rf " . storage_path('logs/schedule-*.log'))->everyMinute()
            ->withoutOverlapping()
            ->description('删除定时任务执行日志');
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
