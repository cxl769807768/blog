<?php
    namespace app\common\validate;
    use think\Validate;
    class Course extends Validate{
        protected $rule = [
	        'cn_id'         => 'require',
	        'ct_id'         => 'require',
	        'name'          => 'require',
	        'cover'         => 'require',
	        'slideshow'     => 'require',
	        'glzx'          => 'require',
	        'day'           => 'require',
	        'syrq'          => 'require',
	        'glxk'          => 'require',
	        'label'         => 'require',
	        'introduce'     => 'require',
	        'attention'     => 'require',
	        'address'       => 'require',

    	];

    	protected $message  =   [
	        'cn_id.require'         => '一级分类不能为空',
	        'ct_id.require'         => '二级分类不能为空',
	        'name.require'          => '课程名称不能为空',
	        'corver.require'        => '课程封面不能为空',
	        'slideshow.require'     => '课程轮播图不能为空',
	        'glzx.require'          => '管理老师+电话不能为空',
	        'day.require'           => '去几天几晚不能为空',
 			'syrq.require'          => '适应人群不能为空',
 			'glxk.require'          => '关联学科不能为空',
 			'label.require'         => '课程标签不能为空',
 			'introduce.require'     => '课程介绍不能为空',
 			'attention.require'     => '注意事项不能为空',
 			'address.require'       => '地址不能为空',
 		];
    }