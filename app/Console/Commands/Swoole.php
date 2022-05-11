<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class Swoole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole {action=start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '这是关于swoole的一个测试demo';
    private static $server = null;

    private static $connectionArray = [];

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
        $server = self::getWebSocketServer();
        $server->on('open', [$this, 'onOpen']);
        $server->on('message', [$this, 'onMessage']);
        $server->on('close', [$this, 'onClose']);
        $server->on('request', [$this, 'onRequest']);
        $server->start();
    }

    // 获取服务
    public static function getWebSocketServer()
    {
        if (!(self::$server instanceof \swoole_websocket_server)) {
            self::setWebSocketServer();
        }
        return self::$server;
    }

    // 服务处始设置
    protected static function setWebSocketServer(): void
    {
        self::$server = new \swoole_websocket_server("0.0.0.0", 9600);
        self::$server->set([
            'worker_num' => 1,
            'heartbeat_check_interval' => 60, // 60秒检测一次
            'heartbeat_idle_time' => 121, // 121秒没活动的
        ]);
    }

    // 打开swoole websocket服务回调代码
    public function onOpen($server, $request)
    {
        if ($this->checkAccess($server, $request)) {
            self::$server->push($request->fd, json_encode('验证成功'));

        } else {
            self::$server->push($request->fd, json_encode("用户未登录或登录信息失效，请尝试重新登录"));
            self::$server->close($request->fd);
        }
    }

    // 给swoole websocket 发送消息回调代码
    public function onMessage($server, $frame)
    {
        $uid = self::$connectionArray[$frame->fd];
//        Artisan::command('swoole request')
        $server->push($frame->fd, '第几个用户给我发送' . $frame->fd);

    }

    public function onRequest($server, $frame)
    {

    }

    // websocket 关闭回调代码
    public function onClose($serv, $fd)
    {
        $this->line("客户端 {$fd} 关闭");
    }

    // 校验客户端连接的合法性,无效的连接不允许连接
    public function checkAccess($server, $request): bool
    {
        $bRes = true;
        if (!isset($request->get) || !isset($request->get['token'])) {
            self::$server->close($request->fd);
            $this->line("接口验证字段不全");
            $bRes = false;
        } else {
            $key = $request->get['token'];
            $uid = Redis::get($key);
            if (empty($uid)) {
                $this->line("验证失败");
                $bRes = false;
            }
        }
        if ($bRes) {
            self::$connectionArray[$request->fd] = $uid;
        }
        return $bRes;
    }

    // 启动websocket服务
    public function start()
    {
        self::$server->start();
    }
}
