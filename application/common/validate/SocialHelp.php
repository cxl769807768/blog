<?php
    namespace app\common\validate;
    use think\Validate;
    class SocialHelp extends Validate{
        protected $rule = [
	        'uid'         => 'require',
	        'contact'         => 'require',
	        'name'          => 'require',
	        'phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
	        'day'     => 'require',
	        'age'          => 'require|number',
	        'address'           => 'require',
	        'destination'           => 'require',
	        'title'          => 'require',
	        'situation'         => 'require',
	        'show'     => 'require',
	        'school'  =>  'require',
	        'number'       => 'require|number',
	        'conditions'       => 'require',
    	];

    	protected $message  =   [
	        'uid.require'         => '用户ID不能为空',
	        'contact.require'         => '联系人不能为空',
	        'name.require'          => '姓名不能为空',
	        'phone.require'   => '手机号不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
	        'day.require'     => '心愿天数不能为空',
	        'age.require'          => '年龄不能为空',
	        'age.number'          => '年龄必须为数字',
	        'address.require'           => '家庭住址不能为空',
	        'destination.require'           => '心愿目的地不能为空',
 			'title.require'          => '标题不能为空',
 			'situation.require'         => '情况描述不能为空',
 			'show.require'     => '封面图不能为空',
	        'school.number'      => '就读学校必须为数字',
 			'conditions.require'       => '帮扶条件不能为空',
 			'number.require'       => '人数不能为空',
 			'number.number'       => '人数必须为数字',
 		];
 		protected $scene = [
        	'edit'  =>  ['contact','name','phone','day','age','address','destination','title','situation','show','school','conditions','number'],
        	'add'  =>  ['uid','contact','name','phone','day','age','address','destination','title','situation','show','school','conditions','number'],
    	];

    }