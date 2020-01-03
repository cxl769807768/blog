<?php
    namespace app\common\validate;
    use think\Validate;
    class Buy extends Validate{
        protected $rule = [
        	'uid'   => 'require|number',
	        'xid'  =>  'require|number',
	        'type'  =>  'require|number',
	        'contact' =>  'require',
	        'source' =>  'require|number',	        
	        'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/'
    	];
    	protected $message  =   [
	        'uid.require'     => '用户id不能为空',
	        'uid.number'      => '用户id必须为数字',
	        'xid.require'     => '模块id不能为空',
	        'xid.number'      => '模块id必须为数字',
	        'type.require'     => '订单类型不能为空',
	        'type.number'      => '订单类型必须为数字',
	        'contact.require'    => '联系人不能为空',
	        'source.require'    => '来源不能为空',
	        'source.number'    => '来源必须为数字',
	        'phone.require'   => '手机号不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
    	];
    }