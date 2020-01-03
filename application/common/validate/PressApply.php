<?php
    namespace app\common\validate;
    use think\Validate;
    class PressApply extends Validate{
        protected $rule = [
	        'uid'         => 'require',
	        'name'          => 'require',
	        'pressid'          => 'require',
	        'sex'     => 'require|number',
	        'school' => 'require',
	        'grade' => 'require',
	        'guardian' => 'require',
	        'guardian_phone' =>'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
	        'photograph'          => 'require',
	        'claim_type'  =>  'require|number',
	        
    	];

    	protected $message  =   [
	        'uid.require'         => '用户ID不能为空',
	        'name.require'          => '名字不能为空',
	        'pressid.require'          => '商品(小记者)id不能为空',
	        'guardian_phone.require'   => '监护人电话不能为空',
	        'guardian_phone.max'   => '监护人电话不能超出11位',
	        'guardian_phone./^1[3-8]{1}[0-9]{9}$/'   => '监护人电话格式错误', 
	        'sex.require'     => '活动天数不能为空',
	        'sex.number'     => '性别必须为数字(1男2女)',
	        'school.require'          => '学校不能为空',
	        'grade.require'           => '班级不能为空',
 			'guardian.require'          => '监护人不能为空',
 			'claim_type.require'       => '取货方式不能为空',
	        'claim_type.number'      => '取货方式须为数字(1自取2送货上门)',
 			'photograph.require'       => '照片不能为空',
 			
 			
 		];
 		protected $scene = [
        	
    	];
    }