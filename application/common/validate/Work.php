<?php
    namespace app\common\validate;
    use think\Validate;
    class Work extends Validate{
        protected $rule = [
	        'gid'          => 'require',
	        'cover'        => 'require',
	        'title'        => 'require',
	        'num'          => 'require',
	        'sex'          => 'require',
	        'interview_address'     => 'require',
	        'work_address'          => 'require',
	        'end_time'              => 'require',
	        'price'                 => 'require',
	        'settlement'            => 'require',
	        'work_detail'           => 'require',
	        'intorduce'             => 'require',
	        'certification'         => 'require|egt:3',
	        'institutions'          => 'require',
	        'phone'   =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
    	];

    	protected $message  =   [
	        'gid.require'            => '兼职类型不能为空',
	        'cover.require'          => '封面图不能为空',
	        'title.require'          => '招聘主题不能为空',
	        'num.require'            => '招聘人数不能为空',
	        'sex.require'            => '性别要求不能为空',
	        'interview_address.require'          => '面试地址不能为空',
	        'work_address.require'               => '工作地址不能为空',
	        'end_time.require'          => '报名截至时间不能为空',
	        'price.require'             => '薪资待遇不能为空',
	        'settlement.require'        => '结算方式不能为空',
	        'work_detail.require'       => '工作详情不能为空',
	        'intorduce.require'         => '公司介绍不能为空',
	        'certification.require'     => '资质认证不能为空',
	        'certification.egt'         => '资质认证图片不得小于三张',
	        'institutions.require'          => '发布机构名称不能为空',
	        'phone.max'   => '手机号不能超出11位',
	        'phone./^1[3-8]{1}[0-9]{9}$/'   => '手机号格式错误', 
 		];
 		protected $scene = [
        	'save'  =>  ['gid','cover','title','num','sex','interview_address','work_address','end_time','price','settlement','work_detail','intorduce','certification','institutions','phone'],
        	'update'  =>  ['gid','cover','title','num','sex','interview_address','work_address','end_time','price','settlement','work_detail','intorduce','certification','institutions','phone'],
    	];

    }