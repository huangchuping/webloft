<?php
/**
 * 项目文件引入
 * Created by WEBLOFT
 * User:huangcp
 */

class InputFiles{

    function __construct(){
        $dirs = array('../runTime/cache/',
            '../runTime/logs/',
            '../runTime/sessions/'
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



