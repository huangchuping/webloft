<?php
/**
 * 数据库查询基类接口类
 * Created by WEBLOFT.
 * User: huangcp
 */

interface DbUtil {

    //查询所有
    function select($condition);

    //删除指定
    function deleteByCondition($con);

    //修改指定位置
    function updateByCondition($change,$con);

    //写入
    function insert($val);
}