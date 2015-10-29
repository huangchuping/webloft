<?php
/**
 * 工具类
 * Created by WEBLOFT.
 * @author huangChuPing
 */

class Helper{


    public function __construct(){

    }


    public static function setLogs($content,$filename,$type=false){
        $dir = '../runTime/logs/'.date('Y-m-d').'/';
        if(!file_exists($dir)) mkdir($dir,0777,true);
        //打开文件
        $filename = $dir.$filename.'.php';
        $fd = fopen($filename,"a");
        //增加文件
        $str = "<?PHP exit;?> \"time:".date("Y-m-d h:i:s",time())." --------{".$content."}\"";
        //写入字符串
        fwrite($fd, $str."\n");
        //关闭文件
        fclose($fd);
    }


    /**
     * 过滤参数(需要更好的方式)
     * $p=*, 表示过滤所有为空的$pararms,
     */
    public static function filterParams( $p=null ) {
        //$pararms = $_REQUEST;
        $pararms = array_merge($_POST, $_GET);
        unset($pararms['m']);
        unset($pararms['f']);
        unset($pararms['t']);
        unset($pararms['page_no']);;
        unset($pararms['page_size']);
        unset($pararms['sortname']);
        unset($pararms['sortorder']);
        unset($pararms['query']);
        unset($pararms['qtype']);
        unset($pararms['qop']);
        unset($pararms['XDEBUG_SESSION_START']);
        unset($pararms['KEY']);

        foreach ($pararms as $key=>&$value){
            $oldvalue = $newvalue = $value;
            $value = trim($value);
            if(!is_array($newvalue)) {
                //添加了过滤条件 by huangChuPing
                $newvalue = preg_replace('/(select|delete|update|insert|from|iframe|replace|group|drop|src|href|sleep|\'|,|"|and|exec|count|%|chr|mid|master|truncate|char|declare){1}[\s\(]+/i','',$value);
                $newvalue = str_replace(array('iframe','<script>','<>','VBScript','src','href','SRC','HREF'), array(), $newvalue);
            }
            if($oldvalue != $newvalue) {
                helper::datalog('key:'.$key.'|oldvalue:'.var_export($oldvalue,true).'|newvalue:'.var_export($newvalue,true),'filterparams_');
            }
            $value = $newvalue;
            if($p){
                if(!is_array($value)) $value = trim($value);
                if($value===null||$value===""||$value=="undefined"){
                    unset($pararms[$key]);
                }
                elseif(strpos($key,"confilter_")>-1){
                    $array_key =  explode("_",$key) ;
                    $newkey = $array_key[1].' '.$array_key[2].' '.$array_key[3] ;
                    $pararms[$newkey] = $value;
                    unset($pararms[$key]);
                }
            }
        }

        return $pararms;
    }

}