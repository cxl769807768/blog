<?php
    namespace app\common\validate;
    use think\Validate;
    class Room extends Validate{
        protected $rule = [
	        'cover'        => 'require',
	        'name'        => 'require',
	        'slideshow'        => 'require',
	        'introduce'          => 'require'
    	];

    	protected $message  =   [
	        'cover.require'          => '封面图不能为空',
	        'name.require'          => '名称不能为空',
	        'slideshow.require'            => '轮播图不能为空',
	        'introduce.require'               => '详情介绍不能为空',
 		];
 		protected $scene = [
        	'save'  =>  ['cover','name','slideshow','introduce'],
        	'update'  =>  ['cover','name','slideshow','introduce'],
    	];

    }