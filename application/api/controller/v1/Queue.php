<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

use unit\redis\Redis;

/**
 * Class Queue
PHP使用Redis的List(列表)命令实现消息队列
使用Redis的List(列表)命令实现消息队列，生产者使用lPush命令发布消息，消费者使用rpoplpush命令获取消息，
同时将消息放入监听队列，如果处理超时，监听者将把消息弹回消息队列
1.用到的List(列表)命令
命令	作用
lPush	将一个或多个值插入到列表头部
rpoplpush	弹出列表最后一个值，同时插入到另一个列表头部，并返回该值
lRem	删除列表内的给定值
lIndex	按索引获取列表内的值

 *
2.队列的组成
名称	职责
生产者	发布消息
消费者	获取并处理消息
监听者	监听超时的消息，弹回原消息队列，确保消费者挂掉后或处理失败后消息能被其他消费者处理
 */
class Queue extends Common
{
    protected $beforeActionList = [
        'initRedis',

    ];
    static private $redis;
    public function initRedis()
    {
        echo 124;

    }
    /**
        2.商品库存队列（List），goods_list
        3.订单信息（Hash集合）,$seckillOrderListkey
        4.抢购成功用户（Set集合）,$seckillBuyListkey
     **/
    /**
     * @param $gid 商品id
     * @param $stock 库存
     * 添加库存到redis（采用list）
     */
    public function addStock(){
        $stock = $this->request->param('stock');
        $gid = $this->request->param('gid');
        self::$redis = Redis::getInstance(array("host"=>"127.0.0.1","port"=>6379,"auth"=>"123.com"));
        $key ="goodsStork_".$gid;
        for($i = 1;$i<=$stock;$i++){
            self::$redis->lPush($key,$i);
        }

        print_r(self::$redis->lRange($key,0,-1));
    }

    /**
     * @param $uid 用户id
     * @param $gid 商品id
     * @param $num 购买数量
     * @return mixed 秒杀接口
     */
    // ab -n 2000 -c 200 -k "http://www.blog.com:80/api/v1/Queue/seckill?num=1&gid=1"
    public function  seckill(){

//        $uid = $this->request->param('uid');
        $uid = rand(1,200);
        $gid = $this->request->param('gid');
        $num = $this->request->param('num');
        self::$redis = Redis::getInstance(array("host"=>"127.0.0.1","port"=>6379,"auth"=>"123.com"));
        $key ="goodsStork_".$gid;
        $seckillBuyListkey ="goodsBuyList_".$gid;
        $seckillOrderListkey ="goodsOrderList_".$gid;
        $length = self::$redis->lLen($key);

        if($length== 0){
            $this->return_msg(500,'商品已售完');
        }elseif($length<$num){
            $this->return_msg(500,'商品只有'.$length."个库存了");
        }

        if(self::$redis->sIsMember($seckillBuyListkey,$uid))
            $this->return_msg(500,"你已经抢购成功了~");
        try {

            self::$redis->watch($key);
            self::$redis->watch($seckillBuyListkey);
            self::$redis->watch($seckillOrderListkey);
            self::$redis->multi();
            self::$redis->rPop($key);
            self::$redis->sAdd($seckillBuyListkey,$uid);
            $value = array(
                'uid'   =>  $uid,
                'goods_id'   =>  $gid,
                'time'  =>  time(),
            );
            self::$redis->hSet($seckillOrderListkey,$uid,json_encode($value));
                //执行事务块内的所有命令
                $status = self::$redis->exec();
                //失败则取消事务
                if (!$status) {
                    self::$redis->discard();
                }
            $this->return_msg(200,'redis执行成功',$status);

        } catch (Exception $e){
            $this->return_msg(500,$e->getMessage());
        }
//        $this->return_msg(200,"你已经抢购成功");
    }
    /**
     * 生产者模式
     */

    public function Producer(){
        self::$redis = Redis::getInstance(array("host"=>"127.0.0.1","port"=>6379,"auth"=>"123.com"));
        try {
            //声明消息队列-list的键名
            $queueKey = 'miaoshaQueueKey';
            //向列表中push10条消息
            for ($i = 0;$i < 10;$i++){
                //为消息生成唯一标识
                $uniqid = uniqid(mt_rand(10000, 99999).getmypid().memory_get_usage(), true);
                $ret = self::$redis->lPush($queueKey, json_encode(array('uniqid' => $uniqid, 'key' => 'key-'.$i, 'value' => 'data')));
                var_dump($ret);
            }

        } catch (Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * 消费者
     */
    public function Consumer(){

    }

    /**
     * 监听者
     */
    public function Watcher(){

    }
}
