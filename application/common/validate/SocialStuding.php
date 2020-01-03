<?php
    namespace app\common\validate;
    use think\Validate;
    class SocialStuding extends Validate{
        protected $rule = [
	        'uid'         => 'require',
	        'address'         => 'require',
	        'name'          => 'require',
	        'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
	        'day'     => 'require',
	        'destination'           => 'require',
	        'title'          => 'require',
	        'introduce'          => 'require',
	        'situation'         => 'require',
	        'show'     => 'require',
	        'type'  =>  'require|number',
	        'number'       => 'require|number',
	        'conditions'       => 'require',
    	];

    	protected $message  =   [
	        'uid.require'         => '用户ID不能为空',
	        'address.require'         => '目的地不能为空',
	        'name.require'          => '姓名不能为空',
	        'phone.require'   => '手机号不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
	        'day.require'     => '资助天数不能为空',
	        'destination.require'           => '资助目的地不能为空',
 			'title.require'          => '标题不能为空',
 			'introduce.require'          => '介绍不能为空',
 			'situation.require'         => '情况描述不能为空',
 			'show.require'     => '封面图不能为空',
 			'type.require'       => '资助类型不能为空',
	        'type.number'      => '资助类型必须为数字',
 			'number.require'       => '资助人数不能为空',
 			'number.number'       => '资助人数必须为数字',
 			'conditions.require'       => '帮扶条件不能为空',
 		];
 		protected $scene = [
        	'edit'  =>  ['address','name','phone','day','price','destination','title','introduce','situation','show','type','number','conditions'],
        	'add'  =>  ['uid','address','name','phone','day','price','destination','title','introduce','situation','show','type','number','conditions'],
    	];
    }