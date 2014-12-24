<?php
/**
 * 控制器基类
 * Created by hcp
 * User:huangcp
 */

class Controller implements ControllerUtil{
    protected $_HOST;
    protected $_model;
    protected $_controller;
    protected $_action;
    protected $_template;
    protected $cache = true;

    /**
     * 构造方法
     * @param $model
     * @param $controller
     * @param $action
     */
    function __construct($model, $controller,$action) {

        if(__URL__ == '/'){
            header('location:'._DEFAULT_PATH_);
        }
        $this->loginCheck();
        $this->_controller = $controller;
        $this->_action     = $action;
        $this->_model      = $model;
//        $this->model      = new $model;
        $this->_template   = new View($controller,$action);

        if (CACHE_ENABLE) {
            $this->cache = new Cache(CACHE_DIR,10);
            $this->cache->load(); //装载缓存,缓存有效则不执行以下页面代码
        }

    }

    public function init(){}

    /**
     * 全局登录校验
     */
    public function loginCheck($type = null) {

    }

    /**
     * 渲染页面及设定渲染值
     * @param $name
     * @param $value
     */
    public function sign($name,$value) {
        $this->_template->setSign($name,$value);
    }

    /**
     * 页面渲染方法
     */
    public function render($url = null) {
        if(empty($url)){
            $this->_template->render();
        }else{
            //指定跳转
            header('location:'.$url);
        }

    }

    /**
     * 获取GET参数
     * @param $type
     * @return array
     */
    public function GET($type){
        $geter = array();
        $urlArr = explode('/',__URL__);
        unset($urlArr[0],$urlArr[1],$urlArr[2],$urlArr[3]);
        if(count($urlArr) > 0 ){
            if($urlArr[4] === ''){
                return null;
            }
            foreach($urlArr as $key=>$item){
                if($type == $item){
                    $geter[$item] = @$urlArr[$key+1];
                }
            }
            return $geter[$type];
        }
        return null;

    }

    /**
     * 获取POST参数
     * @param $type
     * @return mixed
     */
    public function POST($type = null){
        if($type == ''){
            return $_POST;
        }else{
            return $_POST[$type];
        }
    }

}