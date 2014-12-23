<?php
/**
 * 控制器基类接口类
 * Created by hcp
 * User:huangcp
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