<?php
require_once 'Snoopy.class.php';

class Util {
	static public $snoopy = NULL;
	
	/**
	 * 实例化Snoopy
	 */
	static public function instanceSnoopy() {
		self::$snoopy = new Snoopy();
	}
	
	/**
	 * 生成签名
	 * @param $paramArr：api参数数组
	 * @return $sign
	 */
	static public function createSign ($paramArr,$secret = APP_SECRET) {
		$sign = $secret;
		ksort($paramArr);
		foreach ($paramArr as $key => $val) {
			if ($key !='' && !is_null($val)) {
				$sign .= $key.$val;
			}
		}
		$sign .= $secret;
		$sign = strtoupper(md5($sign));
		return $sign;
	}
	
	/**
	 * 生成字符串参数 
	 * @param $paramArr：api参数数组
	 * @return $strParam
	 */
	static public function createStrParam ($paramArr) {
		$strParam = '';
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val !='') {
				$strParam .= $key.'='.urlencode($val).'&';
			}
		}
		return $strParam;
	}
	
	/**
	 * 以GET方式访问api服务
	 * @param $paramArr：api参数数组
	 * @return $result
	 */
	static public function getResult($paramArr) {
		self::instanceSnoopy();
		//组织参数
		$sign = self::createSign($paramArr);
		$strParam = self::createStrParam($paramArr);
		$strParam .= 'sign='.$sign;
		//访问服务
		self::$snoopy->fetch(self::getUri(paramArr));
		$result = self::$snoopy->results;
		//返回结果
		return $result;
	}
	
	/**
	 * 返回GET方式的完整URL
	 */
	static public function getUri($paramArr, $apiurl) {
		//组织参数
		$sign = self::createSign($paramArr);
		$strParam = self::createStrParam($paramArr);
		$strParam .= 'sign='.$sign;
		if ($apiurl === false)
			return API_URL.'?'.$strParam;
		else
			return $apiurl.'?'.$strParam;
	}

	/**
	 * 以POST方式访问api服务
	 * @param $paramArr：api参数数组
	 * @return $result
	 */
	static public function postResult($paramArr) {
		self::instanceSnoopy();
		//组织参数，Snoopy类在执行submit函数时，它自动会将参数做urlencode编码，所以这里没有像以get方式访问服务那样对参数数组做urlencode编码
		$sign = self::createSign($paramArr);
		$paramArr['sign'] = $sign;
		//访问服务
		$return = self::$snoopy->submit(API_URL, $paramArr);
		if(false === $return) {
			$result = new stdClass();
			$result->code = 1000;
			$result->message = self::$snoopy->host.'|'.self::$snoopy->error;
			$result = json_encode($result);
		} else {
			$result = self::$snoopy->results;
		}
		
		//返回结果
		return $result;
	}
	
	/**
	 * 以POST方式访问api服务，带图片
	 * @param $paramArr：api参数数组
	 * @param $imageArr：图片的服务器端地址，如array('image' => '/tmp/cs.jpg')形式
	 * @return $result
	 */
	static public function postImageResult($paramArr, $imageArr) {
		self::instanceSnoopy();
		//组织参数
		$sign = self::createSign($paramArr);
		$paramArr['sign'] = $sign;
		//访问服务
		self::$snoopy->_submit_type = "multipart/form-data";
		self::$snoopy->submit(API_URL, $paramArr, $imageArr);
		$result = self::$snoopy->results;
		//返回结果
		return $result;
	}
	
	/**
	 * 解析xml
	 */
	static public function getXmlData ($strXml) {
		$pos = strpos($strXml, 'xml');
		if ($pos) {
			$xmlCode=simplexml_load_string($strXml,'SimpleXMLElement', LIBXML_NOCDATA);
			$arrayCode=self::get_object_vars_final($xmlCode);
			return $arrayCode ;
		} else {
			return '';
		}
	}
	
	static private function get_object_vars_final($obj){
		if(is_object($obj)){
			$obj=get_object_vars($obj);
		}
		
		if(is_array($obj)){
			foreach ($obj as $key=>$value){
				$obj[$key]=self::get_object_vars_final($value);
			}
		}
		return $obj;
	}
}
?>