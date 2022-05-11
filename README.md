Laravel 8.75
==============
> 运行环境要求PHP 7.3+

##部署
~~~
composer install --no-dev

执行成功后运行以下命令

php artisan deploy
~~~
##定时任务相关
~~~
真实环境则在cron添加如下命令

* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

本地开发则直接运行

php artisan schedule:work

~~~

##命令相关
~~~
新建数据库迁移文件
php artisan make:migration

新建模型类
php artisan make:model {ModelName}
~~~

##联系
~~~
Email:709862717@qq.com
~~~
