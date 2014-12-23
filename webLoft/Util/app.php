<?php
/**
 * 路径组装
 * Created by WEBLOFT
 * User:huangcp
 */

class App{

    function __construct(){
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
    }

    /**
     * 检查是否为开发环境并设置是否记录错误日志
     */
    private function setReporting(){
        if (DEBUG == true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors','On');
            ini_set('error_log',ROOT.DS. 'runTime' .DS. 'logs' .DS. 'error.log');
        }
    }

    /**
     * 检测敏感字符转义（Magic Quotes）并移除他们
     */
    private function stripSlashDeep($value){
        $value = is_array($value) ? array_map('stripSlashDeep',$value) : stripslashes($value);
        return $value;
    }

    /**
     * 判断是否是请求参数
     */
    private function removeMagicQuotes(){
        if (get_magic_quotes_gpc()) {
            $_GET    = $this->stripSlashDeep($_GET);
            $_POST   = $this->stripSlashDeep($_POST);
            $_COOKIE = $this->stripSlashDeep($_COOKIE);
        }
    }

    /**
     * 检测全局变量设置（register globals）并移除他们
     */
    private function unregisterGlobals(){
        if (ini_get('register_globals')) {
            $array = array('_SESSION','_POST','_GET','_COOKIE','_REQUEST','_SERVER','_ENV','_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    /**
     * 主请求方法，主要目的拆分URL请求
     */
    public function run() {
        if(__URL__ == '/'){
            $_SERVER['REQUEST_URI'] = _DEFAULT_PATH_;
        }
        $urlArray = explode("/",__URL__);
        if(empty($urlArray[1])){
            $urlArray = explode('/', _DEFAULT_PATH_);
        }
        $controller = $urlArray[2];
        array_shift($urlArray);
        $action = $urlArray[2];
        array_shift($urlArray);
        $queryString = $urlArray;
        $controllerName = $controller;
        $controller = ucwords($controller);
        $model = rtrim($controller, 's').'Model';
        $controller .= 'Controller';
        $dispatch = new $controller($model,$controllerName,$action);
        if ((int)method_exists($controller, $action.'Action')) {
            call_user_func_array(array($dispatch,$action.'Action'),$queryString);
        } else {
            echo "路径出错咯！";
        }
    }

}

/* 自动加载控制器和模型 */
function __autoload($className) {
    $urlArray = explode("/",__URL__);
    if(empty($urlArray[1])){
        $urlArray = explode('/', _DEFAULT_PATH_);
    }
    if (file_exists(ROOT . DS . 'webLoft' . DS . strtolower($className) . '.class.php')) {//加载控制器基类
        require_once(ROOT . DS . 'webLoft' . DS . strtolower($className) . '.class.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS .$urlArray[1].DS. strtolower($className) . '.php')) {//加载控制器
        require_once(ROOT . DS . 'application' . DS . 'controllers' . DS.$urlArray[1].DS . strtolower($className) . '.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS .$urlArray[1].DS . strtolower($className) . '.php')) {//加载model层
        require_once(ROOT . DS . 'application' . DS . 'models' . DS .$urlArray[1].DS . strtolower($className) . '.php');
    } else if(file_exists(ROOT . DS . 'db' .DS . substr($className,0,-5) . 's.php')){//加载db层
        require_once(ROOT . DS . 'db' .DS . substr($className,0,-5) . 's.php');
    }else {
        /* 生成错误代码 */
        echo ' 规则写错了';
    }
}