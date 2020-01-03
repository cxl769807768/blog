<?php
namespace aliyun\sms;
/**
 * @Author: Super
 * @Date:   2018-07-17 12:24:55
 * @Email:  chaodoing@live.com
 * @Last Modified by:   Super
 * @Last Modified time: 2018-07-17 14:13:24
 */
include_once 'SignatureHelper.php';
class Sms {
	/**
	 * @param string $PhoneNumbers 电话号码
	 * @param string $SignName     签名
	 * @param string $TemplateCode 短信模板ID
	 * @param array $TemplateParam 发送的参数
	 * @return bool|\stdClass
	 */
	public function send(string $PhoneNumbers, string $SignName, string $TemplateCode, array $TemplateParam) {
		$accessKeyId     = "LTAIO9n99KkyJmNq";
		$accessKeySecret = "3oBLUsZHlO4kfi31uaU9sKpVOCkIO2";
		// fixme 必填: 短信接收号码
		$params["PhoneNumbers"] = $PhoneNumbers;
		// fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
		$params["SignName"] = $SignName;
		// fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
		$params["TemplateCode"] = $TemplateCode;
		// fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
		$params['TemplateParam'] = array_merge($TemplateParam, ["product" => "Dysmsapi"]);
		// fixme 可选: 设置发送短信流水号
		$params['OutId'] = "";
		// fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
		$params['SmsUpExtendCode'] = "";
		// *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
		if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
			$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
		}
		// 初始化SignatureHelper实例用于设置参数，签名以及发送请求
		$helper = new SignatureHelper();
		// 此处可能会抛出异常，注意catch
		$content = $helper->request(
			$accessKeyId,
			$accessKeySecret,
			"dysmsapi.aliyuncs.com",
			array_merge($params, array(
					"RegionId" => "cn-hangzhou",
					"Action"   => "SendSms",
					"Version"  => "2017-05-25",
				))
			// fixme 选填: 启用https
			// ,true
		);
		return $content;
	}
}