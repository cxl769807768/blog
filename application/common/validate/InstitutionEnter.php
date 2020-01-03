<?php
    namespace app\common\validate;
    use think\Validate;
    class InstitutionEnter extends Validate{
        protected $rule = [
	        'uid'         => 'require',
	        'name'          => 'require',
	        'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
	        'telephone'     => 'require',
	        'wechat'          => 'require',
	        'email'=> 'email',
	        'contact'          => 'require',
	        'duty'          => 'require',
	        'institution_name'          => 'require',
	        'introduce'  =>  'require',
	        'district'       => 'require',
	        'detailed_address'       => 'require',
    	];

    	protected $message  =   [
	        'uid.require'         => '用户ID不能为空',
	        'name.require'          => '机构法人不能为空',
	        'institution_name.require'          => '机构名称不能为空',
	        'telephone.require'          => '机构电话不能为空',
	        'phone.require'   => '手机号不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
	        'wechat.require'     => '微信不能为空',
	        'email'        => '邮箱格式错误',   
	        'duty.require'          => '职务不能为空',
 			'introduce.require'       => '一句话介绍不能为空',
 			'contact.require'       => '联系人不能为空',
 			'district.require'       => '所在地区不能为空',
 			'detailed_address' =>'详细地址不能为空'
 		];
 		protected $scene = [
        	
    	];
    }