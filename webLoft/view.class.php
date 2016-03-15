<?php
/**
 * package: 视图层基类
 * @org: WEBLOFT
 * @author: huangcp
 * @email: hcp0224@163.com
 * @created: 2015-11-04
 * @logs:
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
     * @param $name
     * @param $value
     * @author: huangcp
     * @logs:
     */
    public function setSign($name,$value) {
        $this->variables[$name] = $value;
    }

    /**
     * 显示模板
     * @param null $arr
     * @author: huangcp
     * @logs:
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
//        if (file_exists(ROOT.DS. 'module' .DS . $urlArray[1] . DS . $this->_controller .DS. 'header.php')) {
//            include(ROOT.DS. 'module' .DS. $urlArray[1] . DS. $this->_controller .DS. 'header.php');
//        } else {
//            include(ROOT.DS. 'module' .DS. 'layouts' . DS. 'header.php');
//        }

        include (ROOT.DS. 'www' .DS. $urlArray[1] . DS.  $this->_action . '.html');

//        if (file_exists(ROOT.DS. 'module' .DS. $urlArray[1] . DS. $this->_controller .DS. 'footer.php')) {
//            include (ROOT.DS. 'module' .DS. $urlArray[1] . DS. $this->_controller .DS. 'footer.php');
//        } else {
//            include (ROOT.DS. 'module' .DS.'layouts' . DS. 'footer.php');
//        }
    }
}
