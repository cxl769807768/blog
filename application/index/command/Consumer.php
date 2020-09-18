<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;


use unit\redis\Redis;
//nohup & nohup  php  think  QueueConsumer  >/dev/null  2>&1  & setsid php think QueueConsumer > /webroot/blog/miaosha.log &

class Consumer extends Command
{
    static private $redis;
    protected function configure(){
		$this->setName('QueueConsumer')->setDescription('这是一个消费者队列');
	}

    protected function execute(Input $input, Output $output)
    {

        try {
            //声明消息队列-list的键名
            $queueKey = 'miaoshaQueueKey';
            //声明监听者队列-list的键名
            $watchQueueKey = 'watchMiaoshaQueueKey';
            self::$redis = Redis::getInstance(array("host"=>"127.0.0.1","port"=>6379,"auth"=>"123.com"));
            //队列先进先出，弹出最先加入的消息，同时放入监听队列
            while (true){
                $ret = self::$redis->rpoplpush($queueKey, $watchQueueKey);
                if ($ret === false){
                    sleep(1);
                } else {
                    $retArray = json_decode($ret, true);
                    //将唯一id写入缓存设置有效期
                    self::$redis->setex($retArray['uniqid'], 1, 0);
                    //生成订单信息，假如生成订单失败则写入日志（日志内容包括那个用户创建订单失败）
                    //模拟失败
                    $rand = mt_rand(0,9);
                    if ($rand < 3){
                        echo "failure:".$ret."\n";
                    } else {
                        //todo
                        //处理成功移除监听消息
                        self::$redis->lRem($watchQueueKey,0,$ret);
                        echo "success:".$ret."\n";
                    }
                }
            }

        } catch (Exception $e){
            echo $e->getMessage();
        }

	}
}
