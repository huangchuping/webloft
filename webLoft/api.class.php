<?php

/**
 * JAVA数据获取API接口
 */
require_once 'Util/Client.php';

class clientService extends Client {

    private $devicelist = null;

    public function __construct() {
        
    }

    /**
     * [data] => stdClass Object
      (
      [address] => 海淀
      [name] => 张军3
      [id] => 12
      [owner] => TXT
      [createTime] => 1176818040000
      [city] => 济南
      [mobile] => 13651169290
      [company] =>
      [phone] => 010-82150101
      [login] => admin
      [regType] => 1
      [pass] => admin
      [updateTime] => 1176818040000
      )
     * error
     * stdClass Object
      (
      [message] => 不存在的方法名
      [data] =>
      [params] => stdClass Object
      (
      [sign] => 9618C6D5C7BABFCECEB0E13F9C9A598C
      [timestamp] => 2010-11-30 18:38:00
      [username] => admin
      [method] => huoyunren.loginuser.get
      [app_key] => 1088
      [format] => json
      )

      [code] => 22
      )

     * @param unknown_type $value
     * @param unknown_type $key username or id
     */
    public function userInfoApi($value, $key = 'username') {
        $user_json = $this->loginuserGet(array($key => $value));
        $user = json_decode($user_json);
        return $user;
    }

    /**
     * 修改密码
     *
     * @param unknown_type $username
     * @param unknown_type $newpass
     * @return unknown
     */
    public function editPasswdApi($username, $newpass) {
        $user_json = $this->loginuserChkpwd(array('username' => $username, 'newpass' => $newpass));
        $user = json_decode($user_json);
        return $user;
    }

    /**
     * stdClass Object
      (
      [message] =>
      [data] => stdClass Object
      (
      [location] => stdClass Object
      (
      [time] => 1291691075000
      [speed] => 0
      [lat] => 40.085252
      [lng] => 116.2700615
      [course] => 0
      [distance] => 0
      [place] => stdClass Object
      (
      [address] => 中国北京市海淀区北清路
      [name] =>
      [type] =>
      [match] => 0
      [longitude] => 116.269956
      [latitude] => 40.0851971
      [city] => 北京市
      [province] => 北京市
      [county] => 海淀区
      [cityCode] =>
      [fullAddress] => 北京市海淀区 中国北京市海淀区北清路
      )

      )

      [first] =>
      [date] => 1291691203259
      [status] => 0
      [distance] => 5960758
      [gpsno] => 0
      )

      [params] =>
      [code] => 0
      )

     *
     * @param int $gpsno
     * @return unknown
     */
    public function geoAddress($gpsno) {
        $postarr = array('gpsno' => $gpsno, 'code' => 1);
        $result = $this->gpsCurrent($postarr);
        $obj = json_decode($result);
        if ($obj->code == 0) {
            $result = new stdClass();
            $result = $obj->data->location->place;
            $result->time = date('Y-m-d H:i:s', $obj->data->location->time / 1000);
            return $result;
        } else {
            helper::datalog($obj->message . '方法名：gpsCurrent,参数:' . var_export($postarr, true), 'syslog_');
            return;
        }
    }

    /**
     * 
     * 设备历史某点的状态信息
     * @param int $gpsno
     * @param datetime $time
     * @param string $code
     */
    public function gpsLocate($gpsno = 0, $time = '', $code = '0') {
        if (!$gpsno)
            return;
        if (!$time)
            $time = helper::nowTime();
        $locate = new stdClass();
        $setarr = array(
            'gpsno' => $gpsno,
            'code' => $code,
            'time' => $time
        );
        $acc = '';
        $result = parent::gpsLocate($setarr);
        $obj = json_decode($result);
        if ($obj->code) {
            $logstr = 'GPS定位gpsLocate|' . $obj->code . '|' . $obj->message . '|' . var_export($setarr, true);
            $this->apilog($logstr);
        } else {
            $objtime = $obj->data->time / 1000;
            $sec = strtotime($time);
            $diff = abs($sec - $objtime);

            $locate->speed2 = $obj->data->speed;
            $locate->distance = $obj->data->distance;
            /*if ($obj->data->speed > 5 && $obj->data->distance > 100) {
                $locate->speed = $obj->data->speed . 'Km/h';
                $locate->status = 1;
            } elseif ($diff >= 1200) {
                $pro = $this->apiLastPro(array('gpsno' => $gpsno, 'time' => $time));
                if ($pro->acc)
                    $locate->speed = '离线/' . $pro->acc;
                else
                    $locate->speed = '离线';
                $locate->status = -1;
            } else {
                $pro = $this->apiLastPro(array('gpsno' => $gpsno, 'time' => $time));
                if ($pro->acc)
                    $locate->speed = '静止/' . $pro->acc;
                else
                    $locate->speed = '静止';
                $locate->status = 0;
            }*/
            $locate->address = isset($obj->data->place) ? $obj->data->place->fullAddress : '';
            $locate->time = date('Y-m-d H:i:s', $objtime);
            $locate->lat = $obj->data->lat;
            $locate->lng = $obj->data->lng;
            $locate->course = $obj->data->course;
            $locate->acc = $acc;
            $this->apilog('gpsLocate指定时间内的状态:gpsno:' . $gpsno . ',$obj:' . var_export($obj, true));
        }
        return $locate;
    }

    /**
     * 格式化速度
     */
    public function formatSpeed($speed, $time) {
        $result = "静止";
        $diff = abs(time() - $time);
        if ($speed > 5) {
            $result = $speed . 'Km/h';
        } elseif ($diff >= 1200) {
            $result = '离线';
        }
        return $result;
    }
    
    /**
     * 
     * stdClass Object
      (
      [message] =>
      [data] => 330 hyuch_mapmaker 主键ID
      [params] =>
      [code] => 0
      )
     * @param unknown_type $op
     * @param unknown_type $setarr
     */
    public function remoteMarker($op = null, $setarr = null) {
        global $app;
        $userid = $app->user->id;
        $orgcode = $app->user->organ->orgroot ? $app->user->organ->orgroot : substr($app->user->organ->orgcode, 0, 6);
        if (!isset($setarr['orgcode'])) {
            $setarr['orgcode'] = $orgcode;
        }

        if (!isset($setarr['userid']))
            $setarr['userid'] = $userid;

        switch ($op) {
            case 'add':
                $result = $this->markerAdd($setarr);
                break;
            case 'update':
                $result = $this->markerSave($setarr);
                break;
            case 'delete':
                $result = $this->markerDelete($setarr);
                break;
            default:
                break;
        }
        $obj = json_decode($result);
        $logstr = '标点同步|' . $obj->code . '|' . $op . '|' . $obj->message . '|' . var_export($setarr, true);
        $this->apilog($logstr);
        return $obj->data;
    }

    /**
     * 同步GPS服务
     */
    public function syncGps($op = null, $setarr = null) {
        global $app, $config;
        $userid = isset($setarr['userid']) && $setarr['userid'] ? $setarr['userid'] : $app->user->id;
        $orgcode = isset($setarr['orgcode']) && $setarr['orgcode'] ? $setarr['orgcode'] : $app->user->organ->orgcode;
        $setarr['orgcode'] = $orgcode;
        $setarr['userid'] = $userid;
        $setarr['ip'] = helper::getIP();
        //update at 2014-01-10
        $_result = new stdClass();
        $_result->code = 300;
        $_result->message = '本环境下不允许执行此项操作';
        $result = json_encode($_result);

        switch ($op) {
            case 'useable':
                $result = $this->gpsUseable($setarr);
                break;
            case 'append':
//                 $setarr["system"] = 'G7s';
                $setarr["sysid"] = $config->sysid;
                $result = $this->gpsAppend($setarr);
                break;
            case 'unappend':
                $result = $this->gpsUnappend($setarr);
                break;
            case 'bind':
                $setarr["bindtime"] = date("Y-m-d H:i:s");
//                 $setarr["system"] = 'G7s';
                $setarr["sysid"] = $config->sysid;
                $setarr["truename"] =  $app->user->realname;
                $result = $this->gpsBind($setarr);
                break;
            case 'unbind':
                $setarr["sysid"] = $config->sysid;
                $result = $this->gpsUnbind($setarr);
                break;
            case 'updatetruckno':
                $result = $this->gpsUpdatetruckno($setarr);
                break;
            default:
                break;
        }
        $obj = json_decode($result);
        $logstr = 'GPS服务同步|' . $obj->code . '|' . $op . '|' . $obj->message . '|' . var_export($setarr, true);
        Log::info($logstr,true,true);
        return $obj;
    }

    /**
     * 
     * 更该设备信息
     * @param array $setarr
     */
    public function updateGps($setarr = array()) {
        global $app, $config;
        $gpsno = $setarr['gpsno'];
        if ($gpsno) {
            $userid = isset($setarr['userid']) && $setarr['userid'] ? $setarr['userid'] : $app->user->id;
            $setarr['userid'] = $userid;
            $setarr['ip'] = helper::getIP();
            //update at 2014-01-10
            $obj = new stdClass();
            $obj->code = 300;
            $obj->message = '本环境下不允许执行此项操作';
            if (!isset($config->environment) || $config->environment == 'product' || in_array($app->user->orgroot, array('100065'))) {
                $result = $this->gpsUpdate($setarr);
                $obj = json_decode($result);
            }

            if ($obj->code)
                $logstr = $obj->message;
            else
                $logstr = '成功,gpsno:' . $gpsno;
        } else {
            $logstr = 'gpsno为空';
        }
        $this->apilog('修改设备信息,gpsUpdate,' . $logstr);
        return $obj;
    }

    /**
     *  地址解析
     * stdClass Object
      (
      [data] => stdClass Object
      (
      [fullAddress] => 天津市塘沽区 滨海集团产业园
      [address] =>
      [province] => 天津市
      [city] => 天津市
      [county] => 塘沽区
      [longitude] => 117.657836
      [latitude] => 39.073295
      [name] => 滨海集团产业园
      [type] => 商务住宅;产业园区;产业园区
      [match] => 10
      [cityCode] => 022
      [accuracy] => 0
      )

      [code] => 0
      [message] =>
      [params] =>
      )
     * @param float $lng
     * @param float $lat
     */
    public function apiGeoCode($lng = '', $lat = '') {
        $setarr = array('lng' => $lng, 'lat' => $lat);
        $result = $this->geoCode($setarr);
        $obj = json_decode($result);
        if ($obj->code) {
            $logstr = '地址解析geoCode|' . $obj->code . '|' . $obj->message . '|' . var_export($setarr, true);
            $this->apilog($logstr);
        } else
            return $obj->data;
    }

    /**
     * 
     * stdClass Object
      (
      [data] => 135189862
      [code] => 0
      [message] =>
      [params] =>
      )
     * @param int $gpsno
     * @param datetime $from
     * @param datetime $to 
     * return cm
     */
    public function apiGpsDistance($gpsno = 0, $from = '', $to = '') {
        $nowtime = helper::nowTime();
        if (!$from)
            $from = $nowtime;
        if (!$to)
            $to = $nowtime;
        $apiarr = array('gpsno' => $gpsno, 'from' => $from, 'to' => $to);
        $result = parent::mileageGet($apiarr);
        $obj = json_decode($result);
        if ($obj->code == 0) {
//        	$this->apilog($obj->data);
            return $obj->data;
        } else {
            $logstr = '距离mileageGet|' . $obj->code . '|' . $obj->message . '|' . var_export($apiarr, true);
            $this->apilog($logstr);
        }
    }

    private function apilog($logstr = '') {
        tools::datalog($logstr,'ips2_apilog_'); 		
    }

    /**
     * 
     * 设备一组 当前信息 如 跟踪
     * @param string $gpsnos null
     * @param datetime $time
     * @param int $dis
     * @param int $cache
     */
    public function apiGetlocation($gpsnos = 0, $gpsids = '', $time = "", $dis = 0, $cache = 0) {

        if (!trim($gpsnos) && !trim($gpsids)) {
            $msg = '卡号和设备必须有一项不为空';
            helper::datalog('gpsGetcurrents,' . $msg, 'syslog_');
            return $msg;
        }
        $postarr = array('gpsnos' => $gpsnos, 'gpsids' => $gpsids);
        $tempvts = $this->getDeviceCache();
        $postarr = helper::array_remove_empty($postarr);
        $result = $this->gpsGetcurrents($postarr);
        $result = json_decode($result);
        $state = array();
        if ($result->code === 0) {
            if ($result->data) {
                foreach ($result->data as $value) {
                    $local = new stdClass();
                    $gpsno = $gpsnos ? $value->gpsno : trim($value->gpsid);

                    $location = $value->location;
                    $local->gpsno = $value->gpsno;
                    $local->gpsid = $value->gpsid;
                    if ($location) {//今天
                        $local->course = $value->location->course;
                        $local->lat = $value->location->lat;
                        $local->lng = $value->location->lng;
                        $local->speed2 = $value->location->speed;
                        $local->time = helper::nowTime('Y-m-d H:i:s', $value->location->time / 1000);
                        $local->status = $value->status; //-1离线 0静止  1 行驶						
                        $local->speed = $this->speedStr($value->location);
                        if ($tempvt = $tempvts[$value->gpsno]) {
                            if ($tempvt->istemperature == 2) {
                                $local->speed .= "/温度";
                                $local->speed .= $this->temparr($value->location->properties);
                            }
                            if ($tempvt->isvoltage == 2) {
                                $local->speed .= "/电压";
                                $local->speed .= $this->vtarr($value->location->properties);
                            }
                        }
                    } else {
                        $local->course = 0;
                        $local->time = date('Y-m-d H:i:s');
                        $local->speed = 0;
                        $local->lat = -1;
                        $local->status = -1;
                        $local->lng = -1;
                    }
                    $state[$gpsno] = $local;
                }
            }
        } else {
            helper::datalog('gpsGetcurrents,code=' . $result->code . ',message=' . $result->message, 'syslog_');
        }
        return $state;
    }

    /**
     * 
     *  批量查设备信息，如orgroot
     * @param array $postarr
     * 
     * $postarr = array(
     * 	'gpsnos' => '10015638,10015637,10015639',//GPS设备卡号,多个设备号码用","分割
     * 	'gpsids' => '353419033041950,353419033041588,353419033039970',//GPS设备IMEI号,多个设备号码用","分割
     * 	'code' => '0',//默认为0,等于1时,解析经纬度
     * 	'dis' => '0',//默认为0,等于1时,计算当天里程
     * 	'orgcode' => '100010',//设备所属组织 orgroot
     * 	'speed_ge' => '80',//大于指定速度(含),KM/h
     * 	'distance_ge' => '200',//大于指定 里程(含),2000cm
     * 	'lat_ge' => '34.3278131',//多大于指定纬度(含)
     * 	'lat_le' => '34.3278131',//小于指定纬度(含)
     * 	'lng_ge' => '118.541535',//大于指定经度(含)
     * 	'lng_le' => '118.541535',//小于指定经度(含)
     * 	'time_ge' => '2022-10-10 10:10:10',//大于指定时间,时间格式:Y-m-d h:m:s(含)
     * 	'time_le' => '2022-10-10 10:10:10',//小于指定时间 ,时间格式:Y-m-d h:m:s(含)
     * 	'movetime_ge' => '2022-10-10 10:10:10',//大于指定移动时间,时间格式:Y-m-d h:m:s(含)
     * 	'movetime_le' => '2022-10-10 10:10:10'//大于指定移动,时间格式:Y-m-d h:m:s(含)
     * )
     * 
     */
    public function apiCurrents($postarr = array()) {
    	$postarr = fixer::input($postarr)->getArray();
        if (empty($postarr['gpsnos']) && empty($postarr['gpsids']) && empty($postarr['orgcode'])) {
            $msg = '卡号、设备号、所属组织必须传一个';
            // helper::datalog('gpsCurrents,' . $msg . ',参数为：' . var_export($postarr, true), 'syslog_');
            return $msg;
        }
        $result = $this->gpsCurrents($postarr);
        $result = json_decode($result);
        $state = array();
        if ($result->code === 0) {
            if ($result->data) {
                $gpsnos = isset($postarr['gpsids']) && $postarr['gpsids'] ? 0 : 1;
                foreach ($result->data as $value) {
                    $local = new stdClass();
                    $gpsno = $gpsnos ? $value->gpsno : trim($value->imei);
                    $location = isset($value->location) ? $value->location : '';
                    $local->gpsno = $value->gpsno;
                    $local->course = $value->course;
                    $local->gpsid = $value->imei;
                    $local->lat = $value->lat;
                    $local->lng = $value->lng;
                    $local->place = $value->place;
                    $local->distance = $value->distance;
                    $local->sumdistance = $value->sumdistance;
                    $local->speed2 = $value->speed;
                    $local->time = helper::nowTime('Y-m-d H:i:s', $value->time / 1000);
                    $local->gpstime = helper::nowTime('Y-m-d H:i:s', $value->gpstime / 1000);
                    $local->movetime = helper::nowTime('Y-m-d H:i:s', $value->movetime / 1000);
                    $local->status = $value->status; //-1离线 0静止  1 行驶	
                    $local->properties = $value->properties;
                    $local->speed = $this->speedStr($value);
                    //task1577 mazhihui
                    $info = explode(',', $value->properties);
                    if ($info) {
                        foreach ($info as $key => $v) {
                            $list = explode(':', $v);
                            if ($list[0] == 't1') {
                                $local->t1 = $list[1];
                            }
                            if ($list[0] == 't2') {
                                $local->t2 = $list[1];
                            }
                            if ($list[0] == 't3') {
                                $local->t3 = $list[1];
                            }
                            if ($list[0] == 't4') {
                                $local->t4 = $list[1];
                            }
                            if ($list[0] == 'humi') {
                                $local->humi = $list[1];
                            }
                        }
                    }

                    // $local->speed='';
                    //$local->speed = $this->formatGpsStatus($local);
                    // $local->gstatus = $this->formatGpsStatus($local);
                    //http://pms.huoyunren.com/index.php?m=task&f=view&taskID=550
                    //离线或 在线（包括： 速度   acc gps battery gsm


                    $state[$gpsno] = $local;
                }
            }
        } else {
            //('gpsCurrents,code=' . $result->code . ',message=' . $result->message . ',参数为：' . var_export($postarr, true) . 'code:' . $result->code, 'syslog_');
        }
        return $state;
    }

    /**
     * 
     * 地址解析为经纬度
     * @param string $addr
     * @param int $page_size
     * 
     * $code 默认值改为 100  by dupeng 2013-8-2 恢复成99 by dupeng 2013/8/26
     */
    public function apiGeoSearch($addr = '', $page_size = 1, $code = 99, $orgroot = '') {
        global $app;
        $time = helper::nowTime();
        $orgroot = $orgroot ? $orgroot : $app->user->organ->orgroot;
        $result = new stdClass();
        if (!$addr)
            return $result;
        $value = $this->geoSearch(array('addr' => $addr, 'page_size' => $page_size, 'code' => $code, 'orgcode' => $orgroot));
        $tmpvalue = $value;
        $value = json_decode($value);
//        helper::datalog('地址解析：'.$addr.'==结果:'.var_export($value,true),'geoaddress_');
        if ($value->code === 0) {
            $data = $value->data;
            if ($data->totalCount > 0 && $data->result[0]->latitude > 0) {
                $data = $data->result[0];
                $result->name = $data->name ? $data->name : $data->address;
                $result->address = $data->address;
                $result->lng = $data->longitude;
                $result->lat = $data->latitude;
                $result->accuracy = $data->accuracy;
                $result->province = $data->province ? $data->province : '';
                $result->city = $data->city ? $data->city : '';
                $result->county = $data->county ? $data->county : '';
                $result->road = $data->road ? $data->road : '';
                $result->street_number = $data->street_number ? $data->street_number : '';
                $result->level = $data->level;
                $result->ilevel = $data->ilevel;
                if ($result->address == $addr)
                    $result->exact = 1;
            } else {
//	           $result = $this->bGeoSearch($addr);
            }
        } else {
            helper::datalog('地址解析失败地址：' . $addr . '==orgcode:' . $orgroot . '==开始时间:' . $time . '==结果:' . $tmpvalue, 'geoaddress_');
//	        $result = $this->bGeoSearch($addr);
        }

        return $result;
    }

    /**
     * 
     * 百度地址，解析 地址－>经纬度
     * { "status":"OK", "result":{ "location":{ "lng":119.863022, "lat":32.364579 }, "precise":0 } }
     * @param string $addr
     */
    public function bGeoSearch($addr = '') {
        $result = new stdClass();
        $code_addr = urlencode($addr);
        $url = 'http://api.map.baidu.com/geocoder?address=' . $code_addr . '&output=json&key=f91cf65e029aefa226d251c1990329a8';
        $return = helper::init_post($url, array());
        $obj = json_decode($return);
//		print_r($obj);exit;
        $data = $obj->result;

        if ($obj->status == 'OK' && $data) {
            $result->address = $addr;
            $result->lng = $data->location->lng - 0.0065;
            $result->lat = $data->location->lat - 0.0060;
            $result->precise = $data->precise;
            if ($data->precise == 1)
                $result->level = 'gl_street';
            else
                $result->level = 'gl_city';
        } else {
            helper::datalog('bGeoSearch百度解析失败,地址：' . $addr . '==结果:' . var_export($obj, true), 'geoaddress_');
        }
        return $result;
    }

    /**
     * 
     * 更改lct_gps_card中的信息
     * 参数名称	类型	是否必需	描述
      orgcode	String	G1	组织编码(IPS2系统)
      orgid	String	G1	组织id,(IPS系统)
      userid	Number	Y	货运人中的用户id,修改人id
      gpsid	Number	N	imei号
      gpsno	String	Y	GPS设备卡号,修改条件
      gpspwd	String	N	设备密码
      intuserid	String	N	货运人中的用户id,绑定人id
      truckid	String	N	车辆id
      used	String	N	设备使用标识
      system	String	N	系统标识(huoyunren/ips/ips2)
      truckno	String	N	绑定的车号
      truename	String	N	使用者姓名
      ip	String	Y	ip地址
     * @param array $setarr
     * 
     */
    public function apiGpsUpdate($setarr = array()) {
        global $app, $config;
        if (!$setarr['gpsno']) {
            $msg = 'client/model.php卡号为空';
            helper::datalog('gpsUpdate,' . $msg, 'syslog_');
            return $msg;
        }
        $uid = $setarr['userid'] > 0 ? $setarr['userid'] : $app->user->uid;
        $setarr['ip'] = helper::getIP();
        $setarr['userid'] = $uid;
        $setarr['system'] = 'ips2';
        //update at 2014-01-10
        $result = new stdClass();
        $result->code = 300;
        $result->message = '本环境下不允许执行此项操作';
        if (!isset($config->environment) || $config->environment == 'product' || in_array($app->user->orgroot, array('100065'))) {
            $obj = $this->gpsUpdate($setarr);
            $result = json_decode($obj);
        }
        if ($result->code === 0)
            $this->apilog('更新lct_gps_card信息' . var_export($setarr, true));
        else
            helper::datalog('更新lct_gps_card信息错误:gpsUpdate,' . $result->message . ',code:' . $result->code . ',' . var_export($setarr, true), 'syslog_');
    }

    /**
     * 格式化 状态字符串 
     * @author changxm 2013-2-5
     */
    public function formatGpsStatus($value) {
        //接口实现后，本段if可删除
        if (!isset($value->gstatus)) {
            $gstatus = new stdClass();
            $gstatus->gps = 1;
            $gstatus->gsm = -1;
            $gstatus->acc = 0;
            $gstatus->battery = -1;
            $gstatus->voltage = 0;
            $gstatus->powerAlarm = 0;
            $gstatus->gnnsAlarm = 0;
            $value->gstatus = $gstatus;
            /* array(
              "gps"=>0,	 // 是否定位：0/未定位、1/已定位
              "gsm"=>-1,	 // GSM信号：-1/无此数据、其他/信号强度
              "acc"=>-1,	 // ACC状态：-1/无此数据、0/ACC关、1/ACC开
              "battery"=>-1,	 // 电量：-1/无此数据
              "voltage"=>-1,	 // 电压*10：-1/无此数据
              "powerAlarm"=>-1,	// 断电报警：-1/无此数据、0/无报警、1/报警
              "gnnsAlarm"=>-1	 // 定位天线报警：-1/无此数据、0/无报警、1/报警

              ); */
        }
        helper::datalog('---gpsstat---', 'ccccc_');

        $gstatus = $value->gstatus;
        helper::datalog(var_export($gstatus, true), 'ccccc_');
        $str = '';
        if ($value->status == -1) {
            $str = '离线';
        } else {
            if ($value->status == 0) {
                $str = '静止';
            } else {
                $str = $value->speed;
            }

            //状态
            if ($gstatus->acc == '1') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type1-0.png" title="acc开" />';
            } elseif ($gstatus->acc == '0') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type1-1.png" title="acc关" />';
            } // endof acc

            if ($gstatus->gps == '1') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type2-0.png" title="定位成功" />';
            } elseif ($gstatus->gps == '0') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type2-1.png" title="定位失败" />';
            } //endof 定位

            if ($gstatus->battery == '1') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type3-0.png" title="电量" />';
            } elseif ($gstatus->battery == '0') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type3-1.png" title="电量失败" />';
            } //endof 电量

            if ($gstatus->gsm == '1') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type4-0.png" title="信号好" />';
            } elseif ($gstatus->gsm == '0') {
                $str .= '<img src="/css/skin/map_skin/gpscurrent/type4-1.png" title="信号不好" />';
            }
        }
        return $str;
    }

    /**
     * 
     * 车辆速度
     * @param unknown_type $locat
     */
    public function speedStr($locat = '') {

        $speedstr = '';
        $status = $locat->status;
        $pro = $locat->properties;
        if ($status == -1)
            $speedstr = '离线';
        elseif ($status == 0) {
            $speedstr = '静止';
            if (FALSE !== strpos($pro, 'gps')) {
                $len = strpos($pro, 'gps');
                $acc = substr($pro, $len + 4, 1);
                if ($acc == 0)
                    $speedstr = '未定位';
            }
        } else
            $speedstr = $locat->speed . 'Km/h';


        if ($pro && $status != 1) {
            $acc = -1;
            if (FALSE !== strpos($pro, 'acc')) {
                $len = strpos($pro, 'acc');
                $acc = substr($pro, $len + 4, 1);
                if ($status == 0 && $acc == 1)//静止时才有acc开
                    $speedstr.='/ACC开';
                elseif ($status != 1){
                    if($speedstr == '未定位'){
                        $speedstr = '静止';
                    }
                    $speedstr.='/ACC关';
                }
            }
        }
        return $speedstr;
    }

    /**
     * 
     * ACC截取
     * @param string $pro
     * @return -1没有ACC,1开0关
     */
    public function getAcc($pro = '') {
        $acc = -1;
        if (FALSE !== strpos($pro, 'acc')) {
            $len = strpos($pro, 'acc');
            $acc = substr($pro, $len + 4, 1);
        }
        return $acc;
    }

    /**
     * 
     * 设备提定时间内的属性值 
     * array('gpsno' =>'50011210','time'=>'2012-08-22 10:47:54','orderby'=>'time desc')
     * time不传为当前时间
     * @param object $arr
     */
    public function apiLastPro($arr = array()) {
        $return = new stdClass();
        $acc = '';
        if ($arr['gpsno']) {
            $resultobj = $this->gpsLaststatus($arr);
            $obj = json_decode($resultobj);
            if ($obj->code === 0) {
                $data = $obj->data;
                $return->time = date('Y-m-d H:i:s', $data->time / 1000);
                $return->macno = $data->macno;
                $pro = $this->getAcc($data->properties);
                if ($pro == 1)
                    $acc = 'ACC开';
                elseif ($pro == 0)
                    $acc = 'ACC关';
                $return->acc = $acc;
                $vt = $this->vtarr($data->properties);
                if ($vt)
                    $return->vt = $vt;
            }
            else {
                helper::datalog('gpsLaststatus 入参:' . var_export($arr, true) . ',返回值:' . var_export($obj, true), 'syslog_');
            }
        } else {
            helper::datalog('gpsLaststatus最属性gpsno为空,入参：' . var_export($arr, true), 'syslog_');
        }
        return $return;
    }

    /**
     * 直接通过mapabc解析地址
     * @param string $lat
     * @param string $lng
     */
    public function geoAddr_mapabc($lat, $lng, $type = 0) {
        global $app;
        helper::import($app->getAppRoot() . "lib/mapabc.class.php");
        $mapabc = new mapabc();
        $rs = $mapabc->getAddress($lat, $lng, $type);
        return $rs;
    }

    /**
     * 直接通过mapabc 驾车导航 两点或多点之间的曲线
     * @param string $lat
     * @param string $lng
     * @param string $type Type=0，表示时间最短路径，Type=1表示距离最短路径
     * @return int cm
     */
    public function getXYdistance($lats, $lngs, $type = 1) {
        global $app;
        helper::import($app->getAppRoot() . "lib/mapabc.class.php");
        $mapabc = new mapabc();
        $rs = $mapabc->getXYdistance($lats, $lngs, $type);
        return $rs;
    }

    /**
     * 
     * 判断点point是否在多边形pol中(返回值 0:在多边形外 1:在多边形边上 2:在多边形内)
     * @param string $lng
     * @param string $lat
     * @param string $points
     * 
     * 请使用standard/lib/geo.class.php中的inPolygon  liyonghua 2012-11-12
     */
    public function isInPolygon($lng, $lat, $points) { //判断点point是否在多边形pol中(返回值 0:在多边形外 1:在多边形边上 2:在多边形内)
        //下标0为x,下标1为y
        $vertexs = explode(';', $points);
        if (!$vertexs[count($vertexs) - 1]) {
            unset($vertexs[count($vertexs) - 1]);
        }
        $lng = (float) ($lng);
        $lat = (float) ($lat);
        $intersect = 0;
        for ($i = 0; $i < count($vertexs); $i++) {
            $v1 = explode(',', $vertexs[$i]);
            $v2 = explode(',', $vertexs[($i + 1) % count($vertexs)]);
            $v1[0] = (float) ($v1[0]);
            $v1[1] = (float) ($v1[1]);
            $v2[0] = (float) ($v2[0]);
            $v2[1] = (float) ($v2[1]);
            $k = 0;
            //判断点是否在线段上
            if (( ($lng <= $v1[0] && $lng >= $v2[0]) || ($lng >= $v1[0] && $lng <= $v2[0]) ) && ( ($lat <= $v1[1] && $lat >= $v2[1]) || ($lat >= $v1[1] && $lat <= $v2[1]) )) {
                if ($lng == $v1[0] && $lng == $v2[0]) {
                    $k = 1;
                } else if (round(($v1[0] - $lng) / ($v1[0] - $v2[0]), 6) == round(($v1[1] - $lat) / ($v1[1] - $v2[1]), 6)) {
                    $k = 1;
                }
            }
            if ($v1[1] == $v2[1]) {
                if ($lat == $v1[1] && (($lng <= $v1[0] && $lng >= $v2[0]) || ($lng >= $v1[0] && $lng <= $v2[0]))) {
                    return 1;
                } else
                    continue;
            }
            else if ($k == 1)
                return 1;
            else {
                if (($lat < $v1[1] && $lat >= $v2[1]) || ($lat >= $v1[1] && $lat < $v2[1])) {
                    $tmpx = $v1[0] + ((($lat - $v1[1]) * ($v2[0] - $v1[0])) / ($v2[1] - $v1[1]));
                    if ($lng <= $tmpx)
                        $intersect++;
                }
            }
        }
        if ($intersect % 2 == 0) {
            return 0;
        } else
            return 2;
    }

    /**
     * 
     * 解析电压
     * @param string $pro
     */
    private function vtarr($pro = '') {
        $temp = '';
        $ts = array();
        if (preg_match_all('/vt:[0-9]+\.[0-9]+/', $pro, $regs) && $pro) {
            $ts = explode(',', $regs[0][0]);
            if ($ts) {
                foreach ($ts as $t) {
                    $temp = str_replace('vt:', '', $t);
                    unset($t);
                }
            }
        }
        return $temp;
    }

    /**
     * 
     * 解析温度
     * @param string $pro
     */
    private function temparr($pro = '') {
        $temp = '';
        $ts = array();
        //  /t+[0-9]+[:]+(.)+[0-9]+$/
        if (preg_match_all('/t+[0-9]+[:]+(-?\d+)(\.\d+)?/', $pro, $regs) && $pro) {
            $ts = $regs[0];
            if ($ts) {
                foreach ($ts as $t) {
                    $temp .= $t;
                    unset($t);
                }
            }
        }
        return $temp;
    }

    //查找设备的设置
    public function getDeviceCache() {
      return false;
        // if ($this->devicelist == null) {
        //     $device = new deviceModel();
        //     $this->devicelist = $device->deviceCache();
        // }
        // return $this->devicelist;
    }

    public function getSimplePath($points = array()) {
        $outpoints = array();
        if ($points) {
            $count = count($points);
            if ($count < 230) {
                foreach ($points as $point) {
                    $tmp = new stdClass();
                    $tmp->lat = $point->lat;
                    $tmp->lng = $point->lng;
                    $outpoints[] = $tmp;
                    unset($tmp);
                }
            } else {
                $snum = 220; //划分的范围
                $difflat = abs($points[$count - 1]->lat - $points[0]->lat);
                $difflng = abs($points[$count - 1]->lng - $points[0]->lng);
                if ($difflat < 1)
                    $difflat = 1;
                if ($difflng < 1)
                    $difflng = 1;
                $dlat = $difflat / $snum;
                $dlng = $difflng / $snum;
                $slat = $points[0]->lat - $dlat;
                $slng = $points[0]->lng - $dlng;
                $sumlat = 0;
                $sumlng = 0;
                $sumnum = 0;
                for ($i = 0; $i < $count; $i++) {
                    $elat = $slat + 2 * $dlat;
                    $elng = $slng + 2 * $dlng;
                    if ($points[$i]->lat >= $slat && $points[$i]->lat < $elat && $points[$i]->lng >= $slng && $points[$i]->lng < $elng) {
                        $sumlat += $points[$i]->lat;
                        $sumlng += $points[$i]->lng;
                        $sumnum ++;
                    } else {
                        if ($sumnum > 0) {
                            $tmp = new stdClass();
                            $tmp->lat = round($sumlat / $sumnum, 6);
                            $tmp->lng = round($sumlng / $sumnum, 6);
                            $outpoints[] = $tmp;
                            unset($tmp);
                        }
                        $slat = $points[$i]->lat - $dlat;
                        $slng = $points[$i]->lng - $dlng;
                        $sumlat = $points[$i]->lat;
                        $sumlng = $points[$i]->lng;
                        $sumnum = 1;
                    }
                }
                if ($sumnum > 0) {
                    $tmp = new stdClass();
                    $tmp->lat = round($sumlat / $sumnum, 6);
                    $tmp->lng = round($sumlng / $sumnum, 6);
                    $outpoints[] = $tmp;
                    unset($tmp);
                }
            }
        }
        return $outpoints;
    }

    //根据入参返回精简线路(包含下面2个函数) $points:线路经纬度数组[{lat:xx,lng:xx,myorder:0},{lat:xx,lng:xx,myorder:1}...]
    public function getSimplePath1($points = array()) {
        $outpoints = array();
        if ($points) {
            $curves = $this->calIgnore($points);
            if ($curves) {
                if ($curves[0]->start->myorder > 0) {
                    $tmp = new stdClass();
                    $tmp->lat = $points[0]->lat;
                    $tmp->lng = $points[0]->lng;
                    $outpoints[] = $tmp;
                    unset($tmp);
                }
                foreach ($curves as $curve) {
                    $start = $curve->start->myorder;
                    $end = $curve->end->myorder;
                    for ($i = $start; $i <= $end; $i++) {
                        $tmp = new stdClass();
                        $tmp->lat = $points[$i]->lat;
                        $tmp->lng = $points[$i]->lng;
                        $outpoints[] = $tmp;
                        unset($tmp);
                    }
                }
                if ($curves[count($curves) - 1]->end->myorder < count($points) - 1) {
                    $tmp = new stdClass();
                    $tmp->lat = $points[count($points) - 1]->lat;
                    $tmp->lng = $points[count($points) - 1]->lng;
                    $outpoints[] = $tmp;
                    unset($tmp);
                }
            } else {
                foreach ($points as $point) {
                    $tmp = new stdClass();
                    $tmp->lat = $point->lat;
                    $tmp->lng = $point->lng;
                    $outpoints[] = $tmp;
                    unset($tmp);
                }
            }
        }
        return $outpoints;
    }

    //根据入参获得线路相对应的转折路段(主要做线路点精简时使用，也可以用于算弯道)
    private function calIgnore($points = array(), $angle = 15, $radius = 500) {
        $curves = $outcurves = array();
        $num = 0;
        if (count($points) > 2) {
            for ($i = 0; $i < count($points) - 2; $i++) {
                $nowcurve = new stdClass();
                $nowangle = $this->getAngle($points[$i], $points[$i + 1], $points[$i + 1], $points[$i + 2]);
                if (abs($nowangle->degrees) >= $angle) {
                    $nowcurve->start = $points[$i];
                    $nowcurve->end = $points[$i + 2];
                    $nowcurve->angle = $nowangle->degrees;
                    $nowcurve->startid = $i;
                    $nowcurve->endid = $i + 2;
                    $curves[$num] = $nowcurve;
                    $num++;
                } else {
                    //连续弯道demo
                    $tmpnum = $num;
                    $sumangle = $nowangle->degrees;
                    while ($i < count($points) - 3) {
                        $dis = helper::getdistance($points[$i + 1]->lat, $points[$i + 1]->lng, $points[$i + 2]->lat, $points[$i + 2]->lng) * 1000.0;
                        $nextangle = $this->getAngle($points[$i + 1], $points[$i + 2], $points[$i + 2], $points[$i + 3]);
                        if (abs($nextangle->degrees) < $angle && $nextangle->degrees * $nowangle->degrees > 0) {//连续弯角都小于规定角度切偏转方向相同
                            $sumangle += $nextangle->degrees;
                            $aveangle = ($nowangle->arc + $nextangle->arc) / 2.0;
                            $sinx = sin(abs($aveangle)); //由前条件设置，除连续2次180度掉头外(此情况基本不可能发生)，sinx必然为正数
                            if ($sinx > 0) { //基本上正常设置sinx都会大于0,
                                $now_r = $dis / (2.0 * $sinx);
                                if ($now_r <= $radius) {//小于转弯半径
                                    if ($tmpnum > $num) {
                                        $curves[$num]->end = $points[$i + 3];
                                        $curves[$num]->angle = $sumangle;
                                        $curves[$num]->endid = $i + 3;
                                    } else {
                                        $tmpnum++;
                                        $curves[$num]->start = $points[$i];
                                        $curves[$num]->end = $points[$i + 3];
                                        $curves[$num]->angle = $sumangle;
                                        $curves[$num]->startid = $i;
                                        $curves[$num]->endid = $i + 3;
                                    }
                                    $i++;
                                    $nowangle = $nextangle;
                                    if ($i >= count($points) - 3)
                                        $num = $tmpnum;
                                }
                                else {
                                    if (abs($sumangle) >= $angle) {//夹角和大于规定角度，弯道成立
                                        $i ++;
                                        $num = $tmpnum;
                                        break;
                                    } else {
                                        if ($tmpnum > $num) { //已添加弯道，但夹角和不足，删除已添加记录
                                            unset($curves[$num]);
                                            break;
                                        } else { //未添加弯道
                                            break;
                                        }
                                    }
                                }
                            } else {
                                if ($tmpnum > $num) { //已添加弯道，但夹角和不足，删除已添加记录
                                    unset($curves[$num]);
                                    break;
                                } else { //未添加弯道
                                    break;
                                }
                            }
                        } else {
                            if (abs($sumangle) >= $angle) {//夹角和大于规定角度，弯道成立
                                $i ++;
                                $num = $tmpnum;
                                break;
                            } else {
                                if ($tmpnum > $num) { //已添加弯道，但夹角和不足，删除已添加记录
                                    unset($curves[$num]);
                                    break;
                                } else { //未添加弯道
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            //合并相连弯道
            $outnum = 0;
            for ($i = 0; $i < count($curves); $i++) {
                if ($i == 0) {
                    $outcurves[$outnum] = $curves[$i];
                    $outcurves[$outnum]->id = $outnum;
                } else {
                    if ($curves[$i]->start->myorder <= $outcurves[$outnum]->end->myorder && $outcurves[$outnum]->end->myorder < $curves[$i]->end->myorder) {
                        $outcurves[$outnum]->end = $curves[$i]->end;
                        $outcurves[$outnum]->endid = $curves[$i]->endid;
                    } else {
                        $outnum++;
                        $outcurves[$outnum] = $curves[$i];
                        $outcurves[$outnum]->id = $outnum;
                    }
                }
            }
            //过滤短弯道
            for ($i = 0; $i < count($outcurves); $i++) {
                $stoedis = helper::getdistance($outcurves[$i]->start->lat, $outcurves[$i]->start->lng, $outcurves[$i]->end->lat, $outcurves[$i]->end->lng);
                $totaldis = 0.0;
                if ($outcurves[$i]->startid < $outcurves[$i]->endid) {
                    $start = $outcurves[$i]->startid;
                    $end = $outcurves[$i]->endid;
                } else {
                    $end = $outcurves[$i]->startid;
                    $start = $outcurves[$i]->endid;
                }
                for ($j = $start; $j < $end; $j++) {
                    $totaldis += helper::getdistance($points[$j]->lat, $points[$j]->lng, $points[$j + 1]->lat, $points[$j + 1]->lng);
                }
                if ($totaldis > 0) {//0.95
//				echo $stoedis / $totaldis . '|';
                }
            }
            return $outcurves;
            //return $curves;
        } else
            return false;
    }

    //计算夹角(供calIgnore函数使用)
    private function getAngle($pa, $pb, $pc, $pd) {
        $angle = new stdClass();
        $a = ($pb->lat - $pa->lat); //y2-y1
        $b = ($pb->lng - $pa->lng); //x2-x1
        $d = $b * ($pd->lat - $pa->lat) - $a * ($pd->lng - $pa->lng);
        if ($d > 0)
            $flag = -1.0; //左转弯
        else
            $flag = 1.0; //右转弯或直行
        $cosfi = $fi = $norm = 0;
        $dsx = $pa->lng - $pb->lng;
        $dsy = $pa->lat - $pb->lat;
        $dex = $pc->lng - $pd->lng;
        $dey = $pc->lat - $pd->lat;
        $cosfi = $dsx * $dex + $dsy * $dey;
        $norm = ($dsx * $dsx + $dsy * $dsy) * ($dex * $dex + $dey * $dey);
        $cosfi = sqrt($norm) > 0 ? $cosfi / sqrt($norm) : 1;
        if ($cosfi >= 1.0)
            return 0;
        if ($cosfi <= -1.0)
            return 180;
        $fi = acos($cosfi);
        if (180 * $fi / M_PI < 180) {
            $angle->degrees = 180 * $fi / M_PI;
            $angle->arc = $fi;
        } else {
            $angle->degrees = 360 - 180 * $fi / M_PI;
            $angle->arc = 2.0 * M_PI - $fi;
        }
        $angle->degrees *= $flag;
        $angle->arc *= $flag;
        return $angle;
    }
	public function serviceChangedBackData($data) {
        // $post_url = 'http://admin.gsp.cn/rest/';
        $post_url = GSPADMIN_URL;
        $app_key = GSPADMIN_KEY;
        $secret = GSPADMIN_SECRET;

        $data = urlencode(json_encode($data));
        $paramArr = array(
            'method' => 'gsp.api.serviceChangedBackData',
            'data' => $data,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => 'json',
            'app_key' => $app_key
        );
        $sign = Util::createSign($paramArr, $secret); //生成签名
        $paramArr['sign'] = $sign;
        $result = $this->init_post($post_url, $paramArr);
        return $result;
    }
    public function getServiceChangedByApi($data) {
        $post_url = GSPADMIN_URL;
        $app_key = GSPADMIN_KEY;
        $secret = GSPADMIN_SECRET;

        $data = urlencode(json_encode($data));
        $paramArr = array(
            'method' => 'gsp.api.serviceChanged',
            'data' => $data,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => 'json',
            'app_key' => $app_key
        );
        $sign = Util::createSign($paramArr, $secret); //生成签名
        $paramArr['sign'] = $sign;
        $result = $this->init_post($post_url, $paramArr);
        return $result;
    }

    public function getGpsPartsChangeByApi($data) {
        $post_url = GSPADMIN_URL;
        $app_key = GSPADMIN_KEY;
        $secret = GSPADMIN_SECRET;

        $data = urlencode(json_encode($data));
        $paramArr = array(
            'method' => 'gsp.api.synGpsFunPartsByTime',
            'data' => $data,
            'timestamp' => date("Y-m-d H:i:s"),
            'format' => 'json',
            'app_key' => $app_key
        );
        $sign = Util::createSign($paramArr, $secret); //生成签名
        $paramArr['sign'] = $sign;
        $result = $this->init_post($post_url, $paramArr);
        return $result;
    }

    public function init_post($post_url, $data, $header = '') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 'application/x-www-form-urlencoded');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if ($header)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}

?>