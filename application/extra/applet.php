<?php
/**
 * 小程序获取opened配置
 */
return [
 	'url' => "https://api.weixin.qq.com/sns/jscode2session", //微信获取session_key接口url
    'appid' => 'wxc1d81871ab73a013', // APPId
    'secret' => 'ad3ab02de523c8fcf5e5a49e42a3ba5b', // 秘钥
    'grant_type' => 'authorization_code', // grant_type，一般情况下固定的
];