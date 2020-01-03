<?php
// 应用公共文件
/**
 * @Author   xiaolong
 * @DateTime 2018-12-28T17:19:30+0800
 * @param    [type]
 * @return   生成密码
 */
function create_password($password){
    return md5($password.'yanxuetao');
}    
/**
 * @Author   xiaolong
 * @DateTime 2018-12-28T17:19:05+0800
 * @return   生成token
 */
function create_token($username,$create_time){
    return md5(md5($username).$create_time);
}
/**
 * [func_substr_replace description]
 * @Author   xiaolong
 * @DateTime 2019-07-12T16:08:45+0800
 * @param    [type]                   $str         [description]
 * @param    string                   $replacement [description]
 * @param    integer                  $start       [description]
 * @param    integer                  $length      [description]
 * @return   [type]                                隐藏部分字符串
 */
function func_substr_replace($str, $replacement = '*', $start = 1, $length = 3){
    $len = mb_strlen($str,'utf-8');
    if($len == 2){
        $str1 = mb_substr($str,0,$start,'utf-8');
        return $str1.$replacement;
    }elseif ($len > intval($start+$length)) {
        $str1 = mb_substr($str,0,$start,'utf-8');
        $str2 = mb_substr($str,intval($start+$length),NULL,'utf-8');
    } else {
        $str1 = mb_substr($str,0,1,'utf-8');
        $str2 = mb_substr($str,$len-1,1,'utf-8');    
        $length = $len - 2;        

    }
    $new_str = $str1;
    for ($i = 0; $i < $length; $i++) { 
        $new_str .= $replacement;
    }
    $new_str .= $str2;
    return $new_str;
}
function generate_username( $length = 6 ) {
    // 密码字符集，可任意添加你需要的字符 
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $username = '';
    for ( $i = 0; $i < $length; $i++ )
    {
        $username .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $username;
}
/**
 * @Author   xiaolong
 * @DateTime 2018-12-28T17:19:36+0800
 * @param    [type]
 * @return   创建多级目录
 */
function create_folders($dir) {
    return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
}
/**
 * @Author   xiaolong
 * @DateTime 2018-12-28T17:19:42+0800
 * @param    [type]
 * @return   [type]
 */
function idcard_verify_number($idcard_base){
    if (strlen($idcard_base) != 17){ return false; }
    // 加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

    // 校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++){
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }

    $mod = strtoupper($checksum % 11);
    $verify_number = $verify_number_list[$mod];

    return $verify_number;
}
/**
 * 将15位身份证升级到18位
 */
function idcard_15to18($idcard){
    if (strlen($idcard) != 15){
        return false;
    }else{
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
            $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
        }else{
            $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
        }
    }

    $idcard = $idcard.idcard_verify_number($idcard);

    return $idcard;
}
/**
 * @Author   xiaolong
 * @DateTime 2018-12-28T17:20:20+0800
 * @param    18位身份证校验码有效性检查
 * @return   [type]
 */
function idcard_checksum18($idcard){
    if (strlen($idcard) != 18){ return false; }
    $aCity = array(11 => "北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",
    21=>"辽宁",22=>"吉林",23=>"黑龙江",
    31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",
    41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",
    50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",
    61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",
    71=>"台湾",81=>"香港",82=>"澳门",
    91=>"国外");
    //非法地区
    if (!array_key_exists(substr($idcard,0,2),$aCity)) {
        return false;
    }
    //验证生日
    if (!checkdate(substr($idcard,10,2),substr($idcard,12,2),substr($idcard,6,4))) {
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
        return false;
    }else{
        return true;
    }
}
/**
 * [createOrderNum description]
 * @Author   xiaolong
 * @DateTime 2019-01-04T14:59:51+0800
 * @return   [type]                   生成唯一订单号
 */
function createOrderNum(){
    //return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid().mt_rand(1, 99999), 7, 13), 1))), 0, 8);
    $order_id_main = date('YmdHis') . rand(10000000,99999999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){
        $order_id_sum += (int)(substr($order_id_main,$i,1));
    }
    return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
}
/**
 * [makeRequest description]
 * @Author   xiaolong
 * @DateTime 2019-01-04T14:57:44+0800
 * @param    [type]                   $url    访问路径
 * @param    array                    $params 参数，该数组多于1个，表示为POST
 * @param    integer                  $expire 请求超时时间
 * @param    array                    $extend 请求伪造包头参数
 * @param    string                   $hostIp HOST的地址
 * @return   [type]                           返回的为一个请求状态，一个内容
 */
function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
    if (empty($url)) {
        return array('code' => '100');
    }

    $_curl = curl_init();
    $_header = array(
        'Accept-Language: zh-CN',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache'
    );
    // 方便直接访问要设置host的地址
    if (!empty($hostIp)) {
        $urlInfo = parse_url($url);
        if (empty($urlInfo['host'])) {
            $urlInfo['host'] = substr(DOMAIN, 7, -1);
            $url = "http://{$hostIp}{$url}";
        } else {
            $url = str_replace($urlInfo['host'], $hostIp, $url);
        }
        $_header[] = "Host: {$urlInfo['host']}";
    }

    // 只要第二个参数传了值之后，就是POST的
    if (!empty($params)) {
        curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($_curl, CURLOPT_POST, true);
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($_curl, CURLOPT_URL, $url);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
    curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

    if ($expire > 0) {
        curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
    }

    // 额外的配置
    if (!empty($extend)) {
        curl_setopt_array($_curl, $extend);
    }

    $result['result'] = curl_exec($_curl);
    $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
    $result['info'] = curl_getinfo($_curl);
    if ($result['result'] === false) {
        $result['result'] = curl_error($_curl);
        $result['code'] = -curl_errno($_curl);
    }

    curl_close($_curl);
    return $result;
}

/**
    * @Author   xiaolong
    * @DateTime 2018-12-28T17:50:15+0800
    * @param    integer
    * @return   作用：产生随机字符串，不长于32位
    */
function createNoncestr( $length = 32 ){
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz0123456789"; 
    $str ="";
    for ( $i = 0; $i < $length; $i++ )  { 
        $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1); 
    } 
    return $str;
}
/**
 * [createRandomCode description]
 * @Author   xiaolong
 * @DateTime 2019-04-23T12:47:55+0800
 * @param    [type]                   $length [description]
 * @param    string                   $chars  [description]
 * @return   [type]                           随机数
 */
function createRandomCode($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return rand(10000000,99999999).$hash;
}
/**
 * [make_code description]
 * @Author   xiaolong
 * @DateTime 2019-01-21T15:54:10+0800
 * @param    [type]                   $num [description]
 * @return   [type]    生成num位数字随机数 [description]
 */
function make_code($num) {
    $max = pow(10, $num) - 1;
    $min = pow(10, $num - 1);
    return rand($min, $max);
}

/**
 * [substr_cut description]
 * @Author   xiaolong
 * @DateTime 2019-06-24T10:52:43+0800
 * @param    [type]                   $user_name [description]
 * @return   [type]                              汉字加密
 */
function substr_cut($user_name){
    $strlen = mb_strlen($user_name, 'utf-8');
    $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}
function getAgeByID($id){ 
        
//过了这年的生日才算多了1周岁 
        if(empty($id)) return ''; 
        $date=strtotime(substr($id,6,8));
//获得出生年月日的时间戳 
        $today=strtotime('today');
//获得今日的时间戳 111cn.net
        $diff=floor(($today-$date)/86400/365);
//得到两个日期相差的大体年数 
        
//strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比 
        $age=strtotime(substr($id,6,8).' +'.$diff.'years')>$today?($diff+1):$diff; 
  
        return $age; 
    } 









