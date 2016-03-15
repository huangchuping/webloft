<?php
/**
 * package: 项目文件引入&接口化入口
 * @org: WEBLOFT
 * @author: huangcp
 * @email: hcp0224@163.com
 * @created: 2015-11-04
 * @logs:
 */

class InputFiles{

    function __construct(){
        $dirs = array('../runTime/cache/',
            '../runTime/logs/'
        );
        foreach($dirs as $dir){
            if (is_dir($dir)) break;
            mkdir($dir,0777,true);
        }
    }

    public function index($includePath){
        foreach($includePath as $path){
            $this->get_files_scandir($path);
        }
//        //邮件配置
//        $title = '据脚本运行情况';
//        $sm = new Email('smtp.163.com', 'hcp0224@163.com', 'xuanshao..',true);
//        $sendTo = "hcp0224@qq.com,1194886447@qq.com";
//        $content = "<table style='background-color: #CCCCCC;' cellspacing='1' cellpadding='3'  width='99%' >";
//        $content .= "<tr height='30'><td bgcolor='#FFFFFF'>信息类型：</td> <td bgcolor='#FFFFFF'> <strong style='color:#F00; size:14px;' >职能部门员工" . date("Y 年 m 月", strtotime("-1 month")) . "考勤数据抽取</strong></td>";
//        $content .= "<tr height='30' ><td width='10%' bgcolor='#FFFFFF'>信息反馈：</td><td width='40%' bgcolor='#FFFFFF'>该月数据已经保存过了</td></tr></table>";
//
//        $end = $sm->SendMail($sendTo,'hcp0224',$title,$content);
//        echo 123,'-----';
//        echo var_dump($end);exit;
    }

    private function get_files_scandir($path){
        $result = array();
        $temp = array();
        if (!is_dir($path)||!is_readable($path)) return null; //检测目录有效性
        $allfiles = scandir($path); //获取目录下所有文件与文件夹
        foreach($allfiles as $filename){ //遍历一遍目录下的文件与文件夹
            if(in_array($filename,array('.','..'))) continue; //无视 . 与 ..
            $fullname = $path.'/'.$filename; //得到完整文件路径

            if(is_dir($fullname)){ //是目录的话继续递归
                $result[$filename] = $this->get_files_scandir($fullname); //递归开始
            }else{
                require_once($path . DS . $filename);
                $temp[] = $filename; //如果是文件，就存入数组
            }
        }

        foreach ($temp as $tmp) { //把临时数组的内容存入保存结果的数组
            require_once($path . DS . $tmp);
//            $result[] = $tmp; //这样可以让文件夹排前面，文件在后面
        }
//    var_dump($result);
//    return $result;
    }
}



