<?php
/**
 * ������
 * Created by WEBLOFT.
 * @author huangChuPing
 */

class Helper{


    public function __construct(){

    }


    public static function setLogs($content,$filename,$type=false){
        $dir = '../runTime/logs/'.date('Y-m-d').'/';
        if(!file_exists($dir)) mkdir($dir,0777,true);
        //���ļ�
        $filename = $dir.$filename.'.php';
        $fd = fopen($filename,"a");
        //�����ļ�
        $str = "<?php echo 'time:".date("Y-m-d h:i:s",time())." --------{".$content."}'; ?>";
        //д���ַ���
        fwrite($fd, $str."\n");
        //�ر��ļ�
        fclose($fd);
    }

}