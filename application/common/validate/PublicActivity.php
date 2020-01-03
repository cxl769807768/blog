<?php
namespace app\common\validate;
use think\Validate;
class PublicActivity extends Validate
{
    protected $rule = [
        'cover'         => 'require',
        'carousel'      => 'require',
        'title'         => 'require',
        'conditions'    => 'require',
        //'duration'      => 'require',
        'address'       => 'require',
        'num'           => 'require',
        'message'       => 'require',
        'cost'          => 'require',
        //'budget'        => 'require',
        'introduce'     => 'require'

	];

	protected $message  =   [
        'cover.require'         => '封面图片不能为空',
        'carousel.require'      => '轮播图片不能为空',
        'title.require'         => '公益活动标题不能为空',
        'conditions.require'    => '报名条件不能为空',
        //'duration.require'      => '活动时长不能为空',
        'address.require'       => '活动地点不能为空',
        'num.require'           => '活动名额不能为空',
		'message.require'       => '主办方信息不能为空',
		'cost.require'          => '活动成本(精确到个人)不能为空',
		//'budget.require'        => '总预算不能为空',
		'introduce.require'     => '活动详情不能为空'
	];
}