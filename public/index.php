<?php
/**
 * 项目总入口
 * Created by WEBLOFT
 * User:huangcp
 */

error_reporting(E_ALL ^ E_NOTICE);
header("Content-type: text/html; charset=utf-8");

//设置初始化路径 由用户手动修改/添加
define('_DEFAULT_PATH_','/home/demo/viewAll');

//设置是否为开发状态
define('DEBUG',true);
session_start();

//路径信息
define('DS',DIRECTORY_SEPARATOR);
define('ROOT',dirname(dirname(__FILE__)));
define('HOST',$_SERVER['HTTP_HOST']);

//资源文件路径
define('__IMG__','/public/scripts/images/');
define('__JS__','/public/scripts/js/');
define('__CSS__','/public/scripts/css/');
define('__FONT__','/public/scripts/font/');

define('__URL__',$_SERVER['REQUEST_URI']);
require_once(ROOT.DS.'webLoft'.DS.'Core '.DS.'api.php');//对webLoft文件夹下的Core api.php请求

//需要引入的文件及文件夹路径  引入文件
$include_path = array(
    ROOT.DS.'webLoft',
    ROOT.DS.'extention',
    ROOT.DS.'application'.DS.'util',
    ROOT.DS.'config'
);

$inputFiles = new InputFiles();
$inputFiles->index($include_path);

//项目总入口
$app = new App();
$app->run();