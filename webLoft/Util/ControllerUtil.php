<?php
/**
 * @package: 控制器基类接口类
 * @org: WEBLOFT
 * @author: huangcp
 * @email: hcp0224@163.com
 * @created: 2015-11-04
 * @logs:
 */
interface ControllerUtil{

    //渲染数据
    function sign($name,$value);

    //渲染页面
    function render($url = null);

    //GET参数
    function GET($type);

    //POST参数
    function POST($type);
}