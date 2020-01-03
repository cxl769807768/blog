<?php
    namespace app\common\validate;
    use think\Validate;
    class CourseJourney extends Validate{
        protected $rule = [
        	'cid'   => 'require|number',
        	'num'   => 'require|number',
	        'name'  =>  'require',
	        'abort' =>  'require',
	        'starttime' =>  'require',
	        'endtime' =>  'require',
    	];
    	protected $message  =   [
	        'cid.require'     => '课程id不能为空',
	        'cid.number'      => '课程id必须为数字',
	        'name.require'    => '名称不能为空',
	        'abort.require'    => '报名截止时间不能为空',
	        'starttime.require'    => '开始时间不能为空',
	        'endtime.require'    => '结束时间不能为空',
    	];
    }