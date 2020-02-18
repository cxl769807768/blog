<?php
    namespace app\common\validate;
    use think\Validate;
    class Mood extends Validate{
        protected $rule = [

	        'name'        => 'require',
	        'title'        => 'require',
	        'school'       =>  'require',
	        'class'       =>  'require',
	        'desc'          => 'require',
    	];

    	protected $message  =   [
	        'name.require'          => '名称不能为空',
	        'title.require'          => '标题不能为空',
	        'school.require'          => '学校不能为空',
	        'class.require'          => '班级不能为空',
	        'desc.require'          => '描述不能为空',
 		];
 		protected $scene = [
        	'save'  =>  ['title','school','name','class','desc'],
        	'update'  =>  ['title','school','name','class','desc'],
    	];

    }