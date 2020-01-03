<?php

namespace app\api\controller\v1;

use app\api\Controller\Common;
use think\Request;
use getspell\GetSpell;

class Test extends Common
{
    /**
     * [client description]
     * @Author   xiaolong
     * @DateTime 2019-08-20T15:15:57+0800
     * @return   [type]                   swoole客户端测试
     */
    public function client()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $ret = $client->connect("127.0.0.1", 9502);
        if(empty($ret)){
            echo 'error!connect to swoole_server failed';
        } else {
            $client->send('blue');//这里只是简单的实现了发送的内容
        }
    }
    /**
     * [service description]
     * @Author   xiaolong
     * @DateTime 2019-08-20T15:16:14+0800
     * @return   [type]                   swoole服务端测试
     */
    public function service(){
        try{

            $serv = new \swoole_server("127.0.0.1", 9502); 
            $serv->on('Start', function($serv){
                echo "Start\n";
            });
            //监听连接进入事件
            $serv->on('connect', function ($serv, $fd) {  
                echo "Client{$fd}: Connect.\n";
    
            });

            //监听数据发送事件
            $serv->on('receive', function ($serv, $fd, $from_id, $data) {
                
                $serv->send($fd, "Server: ".$data);
            });

            //监听连接关闭事件
            $serv->on('close', function ($serv, $fd) {
                echo "Client {$fd} close connection\n";
            });
            $serv->start(); 
        }catch (Exception $e){
            echo $e->getMessage();
        }
        
    }
    public function websoket_service(){
        $ws = new \swoole_websocket_server("0.0.0.0", 9502);

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            // var_dump($request->fd, $request->get, $request->server);
            // var_dump($ws);
            $ws->push($request->fd, "hello, welcome{$request->fd}\n");
        });

        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            var_dump($frame);
            // echo "Message: {$frame->data}\n";
            $ws->push($frame->fd, "server: {$frame->data}");
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });
        $ws->start();
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        try{

            // $a = null;
            // var_dump(!$a);exit;
            // var_dump(empty($a));exit;
            phpinfo();
            // $getSpell = new GetSpell();
            // print_r($getSpell->getFirstPY('小陈的机构'));exit();
            // $journeyInfo = model('CourseJourney')->where('cid',1362)->select()->toArray();
            // $journeyInfo1 = model('CourseJourney')->where('cid',1356)->select()->toArray();
            // print_r($journeyInfo);
            // print_r("-----");
            // print_r($journeyInfo1);
            // $redis  = new \unit\redis\Client();
            // for ($i=0; $i < 3; $i++) { 
            //     $r = $redis->left_push('public_1',1);
            // }
            //$redis->clean();
            // print_r($redis->getListLen('public_1'));
            // $pop = $redis->left_pop('test2');
            // $test6 = $redis->addNoRepetition('public_1',"hello");
            // print_r($redis->getListLen('public_1'));
            // print_r($redis->getListValue('public_1'));
            // print_r($test6);exit;
            //print_r($s);exit;
        }catch (Exception $e){
            echo $e->getMessage();
        }
        
       
        

        
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
