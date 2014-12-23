<?php
/**
 * 视图层基类
 * Created by hcp
 * User:huangcp
 */

class View {
    protected $variables = array();
    protected $_controller;
    protected $_action;

    public function __construct($controller,$action) {
        $this->_controller = $controller;
        $this->_action =$action;
    }

    /**
     * 设置变量
     */
    public function setSign($name,$value) {
        $this->variables[$name] = $value;
    }

    /**
     * 显示模板
     */
    public function render($arr = null) {
        if(empty($arr)){
            $urlArray = explode("/",__URL__);
            if(empty($urlArray[1])){
                $urlArray = explode('/', _DEFAULT_PATH_);
            }
        }else{
            $urlArray = $arr;
        }

        extract($this->variables);
        if (file_exists(ROOT.DS. 'application' .DS. 'views' .DS . $urlArray[1] . DS . $this->_controller .DS. 'header.php')) {
            include(ROOT.DS. 'application' .DS. 'views' .DS. $urlArray[1] . DS. $this->_controller .DS. 'header.php');
        } else {
            include(ROOT.DS. 'application' .DS. 'views' .DS. 'layouts' . DS. 'header.php');
        }

        include (ROOT.DS. 'application' .DS. 'views' .DS. $urlArray[1] . DS. $this->_controller .DS. $this->_action . '.php');

        if (file_exists(ROOT.DS. 'application' .DS. 'views' .DS. $urlArray[1] . DS. $this->_controller .DS. 'footer.php')) {
            include (ROOT.DS. 'application' .DS. 'views' .DS. $urlArray[1] . DS. $this->_controller .DS. 'footer.php');
        } else {
            include (ROOT.DS. 'application' .DS. 'views' .DS.'layouts' . DS. 'footer.php');
        }
    }
}
