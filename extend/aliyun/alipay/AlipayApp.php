<?php
/*此为支付类*/
namespace aliyun\alipay;
use think\Controller;
class AlipayApp extends Controller
{
    protected $appId = '2017071207728945';//支付宝AppId
    protected $rsaPrivateKey = 'MIIEpQIBAAKCAQEA2mxU4b3TqTf1nxP5FyFT8V9plty01lc9p1ic2BFt8CeZiodmZVeLiM6ZS5nEryKc+L//zZSjkAgxljFIYFFiehFCZsqhAkf4XGuvrWSP+zVXlYDp4QNjKdsYllG7VMZ0j6EVqBoMNyGI4/1NwQDPvA3PJG44I00NpwdGPZDD9HMGMYrdwt/o/2iFnt7txEHwyYIUJncTeb0wXH86WTuMNMf/yhUJmw3DGspNVQUX5FndTRUxf4O6FXRap3EaMQlvQaSRQmoVhAMxYNjuIqUUqsX2sCXnzHiWDh768njIhCj0Mc9mte7288bludtVyPnIbFV7Q6ReLGcXg8JSBoJQgwIDAQABAoIBAEEksiQpk2kSsYTiVhYZ8Ik6palC5gRPPKoeeZjPifRoOxjfzSBIfestgvbTQ5/gOOTPjqFnxWh9qRxcUnO3kiFJ6H3zzRV7FY2q6FhUd8S6YgbKzfY4JmkjWS/r7G0aS0VTC7x5GE+RBtzIfVokvdAeZjs9TCzrHDlGqCJQfMD4yD0SG5jErL7LuQwNGBZcsCuAaiTeOuagRh7bZwlpgioHTWXEBskiLNl6Oqd1XwqUCRl4VG90eWnLIrNsO2g9QKtTAv9almSMRN3dK6CKDvYR+eGAfqEuC2aGb7a7eEVj3PizdMK4H5OHHd++fKEcz9lnBbB6iGopZsdbpIhTFSECgYEA7/Wp1iJ9CmZmK7Ng7pJGfY+L/y+K7OZzSwKSqiArM8SsVsLWEQB9sre4ARKkm+Gu5Px4hLFGkKC5i5AQAud8Cms6MjNKujnknoZTbIOg+Vz9VQwSuEimSrQVhiQqSdpql3eRqmtSPP5H7DfyqGXouFg3Pchb4Z/khNS6baEnFMkCgYEA6QYfhXrsoiW/KgNNHaqtR0T0kTcKv8AQ6a9Mb+NZETiHscNo0qikkxpJ6EdNGAa2wwPr8PDNzBhIH1gldg2i92OOaTEMybYPDEOPupknDoud71CWeKeRezOp+UhvxmYberaEEzql6rYzYoKp77DYhbCKfPven1ODUf/PPBHaXOsCgYEAu+EcPiY4wxjT7GmBfqoW+R6YnZAq9Tumj9eO4BdZ3CmkSjY5B4zb3j+MmYJwUgDNQEsRTcvDlV/Shxyf/LYkX7//C+kvHEpR33ELBo61TilpzNosGg//68O+io3scnDKPpgG/GkVNd5ej+xqWUGZiiS/8+bZUfowIXSyFz4zbEkCgYEArFDB2IQZHqLJJTlBunFvJ6e/Lu9D/J38I8JkxMybHZfvCC4XgsgAw2MKSkEHqn+0gKuSux5nIMjH9J3LPexBma0+L119NmBx6kC/tV3xutjV1pWCYACSHUgMJnJBbbYuFeWH72xMKy/G+c7j+YKN6vaswlXCv5ETJ/0B80rEbjcCgYEAhAigiphmEPVWDFvNyDqatlgfaRGi3omgFkXFkqMPTC8J6gqfAMEGdgWcgfQOgyJ+RbDOtlbef25jnysWByeBnVmsp+gi9jzp4nZ0FQbi0acZBdsvBmE0Bs4iqRgK9YAPbc3OD+CtbfkltIh5QrBkKVdT+bStqQlS0WGGRW4swC8=';//支付宝私钥
    protected $aliPayRsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsfk9JyXE5hkWeAGxw5hLOqIoueNVwR2xbnPK/CwsT2KZJQhmhHOe/ItBcLRKxwj1owQqsmR3stapDHa3aU5tvdZqpK2ZAL22p9V1B+2yLIbb8JCormWGIU0vU71bU7hjf5HiBAb29GGx4WZoLvXiqTRE2uy8EQIh2rz1kURJf1JD35Aj2VVzDzrTue9wOsmFW8apsSXhC1LU+v5pe8udK8m9YHZ+rb07P44tsjzQvexD/nZon8kQj2Jxp0VHSRfY+gJh//sFQ6klZjRyu+UwQuDyYzEikOKDFsl8+qqMfeMQBJaddaHjTCLW2CFxIFy2v7NN9b+n+9RI3n+5KXw8zwIDAQAB';//支付宝公钥
    protected $notify_url = 'http://api.ynyxlx.com/api/v1/getpayresult/alipay_notify'; //异步回调地址
    public $seller = '2088721391122233';
    /**
     * [aliPay description]
     * @Author   xiaolong
     * @DateTime 2019-01-03T11:18:56+0800
     * @param    [type]                   $body         [description]
     * @param    [type]                   $total_amount [description]
     * @param    [type]                   $product_code [description]
     * @return   [type]                                 [description]
     */
    public function aliPay($body, $total_amount, $product_code)
    {
        /**
         * 调用支付宝接口。
         */
        require_once EXTEND_PATH.'aliyun/alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'aliyun/alipay/aop/request/AlipayTradeAppPayRequest.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->aliPayRsaPublicKey;
        $request = new \AlipayTradeAppPayRequest();
        $arr['body'] = $body;
        $arr['subject'] = $body;
        $arr['out_trade_no'] = $product_code;
        $arr['timeout_express'] = '30m';
        $arr['total_amount'] = floatval($total_amount);
        $arr['product_code'] = 'QUICK_MSECURITY_PAY';
        $json = json_encode($arr);
        $request->setNotifyUrl($this->notify_url);
        $request->setBizContent($json);
        $response = $aop->sdkExecute($request);
        return $response;
    }
    /**
     * [createLinkstring description]
     * @Author   xiaolong
     * @DateTime 2019-01-03T11:57:38+0800
     * @param    [type]                   $para [description]
     * @return   [type]                         验签
     */
    public function verifySign($data){
        require_once EXTEND_PATH.'aliyun/alipay/aop/AopClient.php';
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $this->aliPayRsaPublicKey;
        $result = $aop->rsaCheckV1($data, NULL, "RSA2");
        return $result;
    }
    /**
     * [orderQuery description]
     * @Author   xiaolong
     * @DateTime 2019-01-15T09:51:50+0800
     * @param    [type]                   $out_trade_no [description]
     * @return   [type]                    阿里订单查询             [description]
     */
    public function orderQuery($out_trade_no){
        require_once EXTEND_PATH.'aliyun/alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'aliyun/alipay/aop/request/AlipayTradeQueryRequest.php';
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;;
        $aop->alipayrsaPublicKey= $this->aliPayRsaPublicKey;;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = "UTF-8";
        $aop->format='json';
        $request = new \AlipayTradeQueryRequest ();
        $arr['out_trade_no'] = $out_trade_no;
        $json = json_encode($arr);
        $request->setBizContent($json);
        $result = $aop->execute ($request); 
        return $result;
    }
    /**
     * [refund description]
     * @Author   xiaolong
     * @DateTime 2019-01-16T14:09:05+0800
    * @param    [type]                   $out_trade_no  商户订单号
    * @param    [type]                   $out_request_no 商户退单号
    * @param    [type]                   $refund_amount    退款金额
    * @return   [type]                                  退款功能
    */
    public function refund($out_trade_no,$out_request_no,$refund_amount){
        require_once EXTEND_PATH.'aliyun/alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'aliyun/alipay/aop/request/AlipayTradeRefundRequest.php';
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;;
        $aop->alipayrsaPublicKey= $this->aliPayRsaPublicKey;;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = "UTF-8";
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest ();
        $arr['out_trade_no'] = $out_trade_no;
        $arr['out_request_no'] = $out_request_no;
        $arr['refund_amount'] = $refund_amount;
        $json = json_encode($arr);
        $request->setBizContent($json);
        $result = $aop->execute ($request); 
        return $result;
    }
    public function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }
    function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
}