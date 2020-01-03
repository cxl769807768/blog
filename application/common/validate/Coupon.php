<?php
    namespace app\common\validate;
    use think\Validate;
    class Coupon extends Validate{
        protected $rule = [
        	'uid'   => 'require|number',
	        'price'  =>  'require',
	        'type' =>  'require',
	        'end_time' =>  'require',
    	];
    	protected $message  =   [
	        'uid.require'     => '用户id不能为空',
	        'uid.number'      => '用户id必须为数字',
	        'price.require'    => '面值不能为空',
	        'type.require'    => '优惠卷类型不能为空',
	        'end_time.require'    => '过期时间不能为空',
    	];
    }