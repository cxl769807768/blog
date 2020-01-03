<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
/**
 * websoket by xiaolong on 2019/08/22
 */
class Websoket extends Command
{
    protected $server;
    protected function configure(){
		 $this->setName('websocket:start')->setDescription('Start Web Socket Server!');
	}

    protected function execute(Input $input, Output $output)
    {
        $this->server = new \swoole_websocket_server("0.0.0.0", 9502);
        $this->server->set([
            'max_conn' => 50,
            'max_request' => 50,
            'daemonize' => false
        ]);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Open',[$this,'onOpen']);
        $this->server->on('close', function (\swoole_websocket_server $server,$fd) {
            $res = Db::table("fd")->where("fd",$fd)->delete();
            if(empty($res)){
                $this->return_msg(0,"解绑fd与用户关系失败");
            }
            echo "client {$fd} closed\n";
        });
        /************************
        $this->server->on('request', function (\swoole_http_request $request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        *********************/
        $this->server->start();
    }
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request){
        echo "server: handshake success with fd{$request->fd}\n";
        $token =  $request->get['token'];
        $uid = $request->get['uid'];
        /**********验证token**********/

    }
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame){
        $data = json_decode($frame->data,true);
            $fdFrom = Db::table("fd")->where("uid",$data['from'])->find();
            if(empty($fdFrom)){
                $re = Db::table("fd")->insertGetId([
                    "fd"=>$frame->fd,
                    "uid"=>$data['from'],
                    "create_time"=> date("Y-m-d H:i:s",time())

                ]);
                if(empty($re)){
                    $this->return_msg(0,"插入用户与fd关系失败");
                }
            }
            //接收者是否在线
            $fdTo = Db::table("fd")->where("uid",$data['to'])->find();
            if(!empty($fdTo)){
                $data['is_read'] = 1;
                $server->push($fdTo['fd'], $data['message']);
                 
            }else{
                $data['is_read'] = 0;
            }
            $data['create_time'] = date("Y-m-d H:i:s",time());
            print_r($data);
            $res = Db::table("chat")->insertGetId($data);
            if(empty($res)){
                $this->return_msg(0,"聊天信息存储失败");
            }
    }
    public function return_msg($code, $msg = '', $data = []) {
        /*********** 组合数据  ***********/
        $return_data['code'] = $code;
        $return_data['msg']  = $msg;
        $return_data['data'] = $data;
        /*********** 返回信息并终止脚本  ***********/
        echo json_encode($return_data);die;
    }

}
