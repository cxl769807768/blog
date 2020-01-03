<?php
    namespace app\common\validate;
    use think\Validate;
    class Special extends Validate{
        protected $rule = [
	        'cover'        => 'require',
	        'title'        => 'require',
	        // 'type'          => 'require|number',
    	];

    	protected $message  =   [
	        
	        'cover.require'          => '封面图不能为空',
	        'title.require'          => '标题不能为空',
	        // 'type.require'            => '主题类型不能为空',
	        // 'type.number'            => '主题类型id只能是数字',
 		];
 		protected $scene = [
        	'save'  =>  ['cover','title'],
        	'update'  =>  ['cover','title'],
    	];
    }