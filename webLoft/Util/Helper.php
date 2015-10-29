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
        $str = "<?php echo 'time:".date("Y-m-d h:i:s",time())." --------{".$content."}'; ?>";
        //写入字符串
        fwrite($fd, $str."\n");
        //关闭文件
        fclose($fd);
    }

}