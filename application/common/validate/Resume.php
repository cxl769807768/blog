<?php
    namespace app\common\validate;
    use think\Validate;
    class Resume extends Validate{
        protected $rule = [
	        'title'        => 'require',
	        'name'         => 'require',
	        'sex'          => 'require',
	        'address'      => 'require',
	        'introduce'    => 'require',
	        'experience'   => 'require',
	        'phone'        =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
    	];

    	protected $message  =   [
	        'title.require'          => '简历名称不能为空',
	        'name.require'           => '姓名不能为空',
	        'sex.require'            => '性别不能为空',
	        'address.require'        => '居住地址不能为空',
	        'introduce.require'      => '个人介绍不能为空',
	        'experience.require'     => '工作经历不能为空',
	        'phone.max'              => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
 		];
 		protected $scene = [
        	'save'   =>  ['title','name','sex','address','introduce','experience','phone'],
        	'update'  =>  ['title','name','sex','address','introduce','experience','phone'],
    	];

    }