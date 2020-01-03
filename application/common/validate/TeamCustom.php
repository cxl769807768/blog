<?php
    namespace app\common\validate;
    use think\Validate;
    class TeamCustom extends Validate{
        protected $rule = [
	        'uid'         => 'require',
	        'name'          => 'require',
	        'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
	        'days'     => 'require|number',
	        'cost'          => 'require',
	        'destination'           => 'require',
	        'contact'          => 'require',
	        'introduce'          => 'require',
	        'num'  =>  'require|number',
	        'district'       => 'require',
	        'begin_time'       => 'require',
    	];

    	protected $message  =   [
	        'uid.require'         => '用户ID不能为空',
	        'name.require'          => '团体名称不能为空',
	        'phone.require'   => '手机号不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
	        'days.require'     => '活动天数不能为空',
	        'days.number'     => '活动天数必须为数字',
	        'cost.require'          => '预算费用不能为空',
	        'destination.require'           => '活动地点不能为空',
 			'introduce.require'          => '介绍不能为空',
 			'num.require'       => '团体人数不能为空',
	        'num.number'      => '团体人数必须为数字',
 			'contact.require'       => '联系人不能为空',
 			'district.require'       => '所在地区不能为空',
 			'begin_time' =>'开始时间不能为空'
 		];
 		protected $scene = [
        	
    	];
    }