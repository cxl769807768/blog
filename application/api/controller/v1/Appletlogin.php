<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use wechat\wxdev\WXBizDataCrypt;
use wechat\wxdev\ErrorCode;
/**
 * 小程序登录逻辑
 */
class Appletlogin extends Common
{


    public function wxLogin() {
        /**
         * 3.小程序调用server获取token接口, 传入code, rawData, signature, encryptData.
         */
       
        $code = $this->request->param("code", '', 'htmlspecialchars_decode');
        $rawData =  $this->request->param("rawData", '', 'htmlspecialchars_decode');
        $signature =  $this->request->param("signature", '', 'htmlspecialchars_decode');
        $encryptedData =  $this->request->param("encryptedData", '', 'htmlspecialchars_decode');
        $iv =  $this->request->param("iv", '', 'htmlspecialchars_decode');
        //$phone =  $this->request->param("phone", '', 'htmlspecialchars_decode');

        /**
         * 4.server调用微信提供的jsoncode2session接口获取openid, session_key, 调用失败应给予客户端反馈
         * , 微信侧返回错误则可判断为恶意请求, 可以不返回. 微信文档链接
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */
        $params = [
            'appid' => config('applet.appid'),
            'secret' => config('applet.secret'),
            'js_code' => $code,
            'grant_type' => config('applet.grant_type'),
        ];
        $res = makeRequest(config('applet.url'), $params);
        if ($res['code'] !== 200 || !isset($res['result'])) {
            $this->return_msg(ErrorCode::$RequestTokenFailed,'请求Token失败');
        }
        $reqData = json_decode($res['result'], true);
        if (!isset($reqData['session_key'])) {
            $this->return_msg(ErrorCode::$RequestTokenFailed,'请求Token失败');
        }
        $sessionKey = $reqData['session_key'];

        /**
         * 5.server计算signature, 并与小程序传入的signature比较, 校验signature的合法性, 不匹配则返回signature不匹配的错误. 不匹配的场景可判断为恶意请求, 可以不返回.
         * 通过调用接口（如 wx.getUserInfo）获取敏感数据时，接口会同时返回 rawData、signature，其中 signature = sha1( rawData + session_key )
         *
         * 将 signature、rawData、以及用户登录态发送给开发者服务器，开发者在数据库中找到该用户对应的 session-key
         * ，使用相同的算法计算出签名 signature2 ，比对 signature 与 signature2 即可校验数据的可信度。
         */
        $signature2 = sha1($rawData . $sessionKey);

        if ($signature2 !== $signature){
            $this->return_msg(ErrorCode::$SignNotMatch,'签名不匹配');
        }

        /**
         *
         * 6.使用第4步返回的session_key解密encryptData, 将解得的信息与rawData中信息进行比较, 需要完全匹配,
         * 解得的信息中也包括openid, 也需要与第4步返回的openid匹配. 解密失败或不匹配应该返回客户相应错误.
         * （使用官方提供的方法即可）
         */
        $pc = new WXBizDataCrypt(config('applet.appid'), $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode !== 0) {
            $this->return_msg(ErrorCode::$EncryptDataNotMatch,'解密信息错误');
        }


        /**
         * 7.生成第三方3rd_session，用于第三方服务器和小程序之间做登录态校验。为了保证安全性，3rd_session应该满足：
         * a.长度足够长。建议有2^128种组合，即长度为16B
         * b.避免使用srand（当前时间）然后rand()的方法，而是采用操作系统提供的真正随机数机制，比如Linux下面读取/dev/urandom设备
         * c.设置一定有效时间，对于过期的3rd_session视为不合法
         *
         * 以 $session3rd 为key，sessionKey+openId为value，写入memcached
         */
        $result = $data;//引用值赋值给变量
        $result = json_decode($result, true);
        $res = model('User')->where('openid',$result['openId'])->find();
        if(empty($res)){
            $this->return_msg(200,'获取用户微信数据成功但没绑定手机号',$result);
        }else{

            model('User')->where('openid',$result['openId'])->update(['exceed_time'=>time()+3*24*3600,'token'=>create_token($res['phone'],$res['create_time'])]);
            $return  = model('User')->where('openid',$result['openId'])->find()->toArray();
            $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
            unset($return['password']);
            $result['userInfo'] = $return;
            $this->return_msg(200,'获取用户微信数据成功并登录',$result);
        }
        

        /************************************************
        $session3rd = md5(uniqid(microtime(true),true));
        cache($session3rd,$result['openid'].$sessionKey);
        /********存储cache是为了验证小程序传过来的openid是否合法；****/
        /**
        $res = model('User')->where('openid',$result['openId'])->find();
        if(empty($res)){
            if(isset($phone) && !empty($phone)){
                // 如果不为空，则说明是登录过的，就从数据库中找到手机号，然后绑定openid
                // 登录后,手机号不为空，则根据手机号更新openid
                $user = model('User')->where('phone', $phone)->find();
                $time_out = time() + 24*3600; //过期时间
                $token = create_token($phone, $user['create_time']);
                $update = model('User')->where('phone', $phone)->update([
                    'openid'  => $result['openId'],                        
                    'unionid' => $result['unionId'],
                    'token' => $token,
                    'exceed_time'  => $time_out,     
                    'sex'=>$result['gender']==1?'男':'女',
                    ]);                
                if($update){
                    $return = model('User')->where('phone', $phone)->value('phone','openid','unionid','token');                    
                    $this->return_msg(200,'登录并绑定成功',$return);
                }else{
                    $this->return_msg(400,'更新用户信息失败');
                }
            }else{
                $this->return_msg(400,'请先登录');
            }
        }else{
            $update = model('User')->where('openid', $result['openId'])->update([   
               'exceed_time'  => time() + 24*3600,      
            ]);
            if($update){
                $return = model('User')->where('openid', $result['openId'])->value('phone','openid','unionid','token');                    
                $this->return_msg(200,'登录成功',$return);
            }else{
                $this->return_msg(400,'更新用户信息失败');
            }        
        }
        **/
    }
    public function bind() {
        $phone = $this->request->param('phone');
        if(empty($phone)){
           $this->return_msg(0, '手机号不能为空');
        }
        if(empty($this->request->param('code'))){
           $this->return_msg(0, '验证码不能为空');
        }
        if(empty($this->request->param('openid'))){
            $this->return_msg(0, 'openid不能为空');
        }
        $checkCode = $this->check_code($phone,$this->request->param('code'));
        if($checkCode['status']){
            $res = model('User')->where('phone',$phone)->find();
            if(empty($res)){
                $data = $this->request->param(); 
                $re = model('User')->allowField(true)->save($data);
                    if(empty($re)){
                        $this->return_msg(0, '注册绑定失败');
                    }else{
                        $return  = model('User')->where('id',model('User')->id)->find()->toArray();
                        model('User')->where('id',$return['id'])->update([
                            'token' => create_token($phone,$return['create_time']),
                            'exceed_time' => time() + 3*24*3600, //过期时间   
                        ]);
                        // $couponData = array(
                        //     'uid' => $return['id'],
                        //     'type'=> 1,
                        //     'price'=>config('coupon.price'),
                        //     //是否长期有效
                        //     'is_exceed'=>1,
                        //     'type'=>1
                        // );
                        //model('Coupon')->allowField(true)->save($couponData);
                        $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                        unset($return['password']);
                        $this->return_msg(200, '注册并绑定成功',$return);
                    }
            }else{
                $update = model('User')->where('phone',$phone)->update([
                    'token'=>create_token($phone,$res['create_time']),
                    'exceed_time'=>time()+3*24*3600,
                    'openid'=>$this->request->param('openid'),
                    'unionid'=>$this->request->param('unionid'),
                ]);
                if(empty($update)){
                    $this->return_msg(0, '更新用户信息失败');
                }else{
                    $return  = model('User')->where('phone',$phone)->find()->toArray();
                    $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                    unset($return['password']);
                    $this->return_msg(200, '绑定并登录成功',$return);
                }
            }
        }else{
            $this->return_msg(0, $checkCode['info']);
        }
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
