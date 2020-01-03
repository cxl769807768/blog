<?php
/**
 * 第三方登录配置(目前可用的为：weixin,qq,weibo,alipay,facebook,twitter,line,google)
 */
return [
	'weixin'=>[
		'app_id'     => 'wxb938cd8354bdda48',
		'app_secret' => 'badb6464dc35d5a18eea7e6a7097d1b9',
		'scope'      => 'snsapi_base',//如果需要静默授权，这里改成snsapi_base，扫码登录系统会自动改为snsapi_login
	],
 	'qq'=>[
		'app_id'     => '1106208705',
		'app_secret' => '6n1MrqK6NFxBvDCx',
		'scope'      => 'get_user_info',//如果需要静默授权，这里改成snsapi_base，扫码登录系统会自动改为snsapi_login
	]
];