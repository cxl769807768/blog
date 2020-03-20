<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
    if(version_compare(PHP_VERSION,'5.4.0','<')) die('require PHP > 5.4.0 !');
    header('Access-Control-Allow-Origin:*');  // 响应类型
    header('Access-Control-Allow-Headers:x-requested-with,content-type,X-Token,scene,token,authorized');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Credentials: true');
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit();
    }

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('PUBLIC_PATH', __DIR__ );

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
