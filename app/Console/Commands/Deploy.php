<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '环境部署';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if (!file_exists(base_path('.env'))) {
            Artisan::command('cp', function () {
                file_exists(base_path('.env'));
                exec('cp .env.example .env');
            })->comment('复制环境文件');
        }
        Artisan::call('migrate');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('config:cache');
        Artisan::command('chmodPath', function () {
            exec('chmod 755 -R storage/logs');
        })->comment('赋予目录写入权限');
        Artisan::call('key:generate');
        echo "部署成功.\n";
        return 0;
    }
}
