<?php
    namespace app\common\validate;
    use think\Validate;
    class TravelPerson extends Validate{
        protected $rule = [
        	'uid'   => 'require|number',
	        'name'  =>  'require',
	        'card' =>  'require',
	        //'sex'   => 'require|number',
	        //'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/'
    	];
    	protected $message  =   [
	        'uid.require'     => '用户id不能为空',
	        'uid.number'      => '用户id必须为数字',
	        'name.require'    => '用户姓名不能为空',
	        'card.require'    => '身份证号不能为空',
	        //'sex.require'    => '性别不能为空',
	        //'phone.require'   => '手机号不能为空',
	        //'phone.max'   => '手机号不能超出11位',
	        //'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
    	];
    }