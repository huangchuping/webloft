<?php
/**
 * 控制器基类
 * Created by hcp
 * User:huangcp
 */

class Model{
    protected $_model;
    function __construct() {
        $this->_model = str_replace('Model','',get_class($this));
    }
    function __destruct() {
    }
}