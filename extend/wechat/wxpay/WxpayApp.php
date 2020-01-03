<?php
namespace wechat\wxpay;
use think\Controller;
class WxpayApp extends Controller{
    /*
    配置参数
    */
    private $config = array(
        'appid' => "wxb938cd8354bdda48",   /*微信开放平台上的应用id*/
        'mch_id' => "1486357352",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "ujOFcCmtvrBUcyhKCkusoyEIyOd57Pw0",    /*在微信商户平台上自己设定的api密钥 32位*/
        'notify_url' => 'https://api.ynyxlx.com/api/v1/getpayresult/wxpayapp_notify', /*自定义的回调程序地址*/
        'SSLCERT_PATH' => 'appCert/apiclient_cert.pem',//证书路径
        'SSLKEY_PATH' => 'appCert/apiclient_key.pem',//证书路径
    );

    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:48:05+0800
     * @param    [type]
     * @param    [type]
     * @param    [type]
     * @return   下单
     */
    public function getPrePayOrder($body, $out_trade_no, $total_fee){
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = $this->config["notify_url"];
 
        $onoce_str = $this->createNoncestr();
 
        $data["appid"] = $this->config["appid"];
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = "APP";
        $sign = $this->getSign($data);
        $data["sign"] = $sign;
 
        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        
        //将微信返回的结果xml转成数组
        $response = $this->xmlToArray($response);
        //返回数据
        return $response;
    }
    /**
     * [orderQuery description]
     * @Author   xiaolong
     * @DateTime 2019-01-09T14:54:53+0800
     * @param    [type]                   $out_trade_no 商户订单号
     * @return   [type]                                 [description]
     */
    public function orderQuery($out_trade_no){
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $onoce_str = $this->createNoncestr();
 
        $data["appid"] = $this->config["appid"];
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["out_trade_no"] = $out_trade_no;
        $sign = $this->getSign($data);
        $data["sign"] = $sign;
        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        $response = $this->xmlToArray($response);
        //返回数据
        return $response;
    }
    /**
     * [refund description]
     * @Author   xiaolong
     * @DateTime 2019-01-10T17:40:25+0800
     * @param    [type]                   $out_trade_no  商户订单号
     * @param    [type]                   $out_refund_no 商户退单号
     * @param    [type]                   $total_fee     订单总价格
     * @param    [type]                   $refund_fee    退款金额
     * @return   [type]                                  退款功能
     */
    function refund($out_trade_no,$out_refund_no,$total_fee,$refund_fee){
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $onoce_str = $this->createNoncestr();
 
        $data["appid"] = $this->config["appid"];
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["out_trade_no"] = $out_trade_no;
        $data["out_refund_no"] = $out_refund_no;
        $data["total_fee"] = $total_fee;
        $data["refund_fee"] = $refund_fee;
        $sign = $this->getSign($data);
        $data["sign"] = $sign;

        $xml = $this->arrayToXml($data);
        $response = $this->postXmlSSLCurl($xml, $url);

        //将微信返回的结果xml转成数组
        $response = $this->xmlToArray($response);
        //返回数据
        return $response;
    }
    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:49:26+0800
     * @param    [type]
     * @return   执行第二次签名，才能返回给客户端使用
     */
    public function getOrder($prepayId){
        $data["appid"] = $this->config["appid"];
        $data["noncestr"] = $this->createNoncestr();
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = $this->config['mch_id'];
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = time();
        $s = $this->getSign($data, false);
        $data["sign"] = $s;

        return $data;
    }
    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:49:50+0800
     * @param    [type]
     * @return   生成签名
     */
    public function getSign($Obj){
        foreach ($Obj as $k => $v){
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->config['api_key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
   /**
    * @Author   xiaolong
    * @DateTime 2018-12-28T17:50:15+0800
    * @param    integer
    * @return   作用：产生随机字符串，不长于32位
    */
    public function createNoncestr( $length = 32 ){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789"; 
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  { 
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1); 
        } 
        return $str;
    }

    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:50:36+0800
     * @param    [type]
     * @return   数组转xml
     */
    public function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">"; 
            }
        }
        $xml.="</xml>";
        return $xml;
    }

   /**
    * @Author   xiaolong
    * @DateTime 2018-12-28T17:50:50+0800
    * @param    [type]
    * @return   作用：将xml转为array
    */
    public function xmlToArray($xml){  
        //将XML转为array       
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);   
        return $array_data;
    }
 
   /**
    * @Author   xiaolong
    * @DateTime 2018-12-28T17:51:06+0800
    * @param    [type]
    * @param    [type]
    * @param    integer
    * @return   作用：以post方式提交xml到对应的接口url
    */
    public function postXmlCurl($xml,$url,$second=30){  
        //初始化curl       
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
 
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            curl_close($ch);
            return false;
        }
    }
    /**
     * [postXmlSSLCurl description]
     * @Author   xiaolong
     * @DateTime 2019-01-10T15:50:45+0800
     * @param    [type]                   $xml    [description]
     * @param    [type]                   $url    [description]
     * @param    integer                  $second [description]
     * @return   [type]                           需要使用证书的请求
     */
    function postXmlSSLCurl($xml,$url,$second=30){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, dirname(__FILE__).DIRECTORY_SEPARATOR.$this->config['SSLCERT_PATH']);
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, dirname(__FILE__).DIRECTORY_SEPARATOR.$this->config['SSLKEY_PATH']);
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        }else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error"."<br>";
            curl_close($ch);
            return false;
        }
    }
    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:51:26+0800
     * @return   获取当前服务器的IP
     */
    public function get_client_ip(){
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
     
   /**
    * @Author   xiaolong
    * @DateTime 2018-12-28T17:51:44+0800
    * @param    [type]
    * @param    [type]
    * @return   作用：格式化参数，签名过程需要使用
    */
    public function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode){
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0){
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
}