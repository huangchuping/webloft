<?php
/**
 * package: 控制器基类
 * @org: WEBLOFT
 * @author: huangcp
 * @email: hcp0224@163.com
 * @created: 2015-11-04
 * @logs:
 */

class Model{
    protected $_model;
    function __construct() {
        $this->_model = str_replace('Model','',get_class($this));
    }
    function __destruct() {
    }
}