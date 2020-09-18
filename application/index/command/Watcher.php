<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

use unit\redis\Redis;


class Watcher extends Command
{
    static private $redis;
    protected function configure(){
		$this->setName('QueueWatcher')->setDescription('这是一个监听者队列');
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
                $ret = self::$redis->lIndex($watchQueueKey, -1);
                if ($ret === false){
                    sleep(1);
                } else {
                    $retArray = json_decode($ret, true);
                    $idCache = self::$redis->get($retArray['uniqid']);
                    if ($idCache === false){
                        //如果已过期，表示任务超时，弹回原队列
                        self::$redis->rpoplpush($watchQueueKey, $queueKey);
                        echo "rpoplpush:".$ret."\n";
                    } else {
                        //处理中，继续等待
                        sleep(1);
                    }
                }
            }

        } catch (Exception $e){
            echo $e->getMessage();
        }


        try {
            //声明消息队列-list的键名
            $queueKey = 'miaoshaQueueKey';
            //声明监听者队列-list的键名
            $watchQueueKey = 'watchMiaoshaQueueKey';
            self::$redis = Redis::getInstance(array("host"=>"127.0.0.1","port"=>6379,"auth"=>"123.com"));

            while (true){
                //取出列表尾部的一个值
                $ret = self::$redis->lIndex($watchQueueKey, -1);
                //如果不存在则休眠1秒
                if ($ret === false){
                    sleep(1);
                } else {
                    $retArray = json_decode($ret, true);
                    $idCache = self::$redis->get($retArray['uniqid']);
                    if ($idCache === false){
                        //如果已过期，表示任务超时，弹回原队列
                        self::$redis->rpoplpush($watchQueueKey, $queueKey);
                        echo "rpoplpush:".$ret."\n";
                    } else {
                        //处理中，继续等待
                        sleep(1);
                    }
                }
            }

        } catch (Exception $e){
            echo $e->getMessage();
        }

	}
}
