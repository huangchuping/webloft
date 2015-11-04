<?php
/**
 * @package: 数据库查询基类
 * @org: WEBLOFT
 * @author: huangcp
 * @email: hcp0224@163.com
 * @created: 2015-11-04
 * @logs:
 */

class DB implements DbUtil{
    protected $_dbHandle;
    protected $_result;
    protected $_table;

    //数据库默认字段
    protected $id;
    protected $creater;
    protected $created_at;
    protected $modifyer;
    protected $modefied_at;

    /**
     * 连接数据库
     * @author: huangcp
     * @logs:
     */
    public function __construct() {
        $this->_dbHandle = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
        $this->_dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_dbHandle->exec('set names utf8');

        $this->_table = DB_PREFIX.strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/','_',get_called_class()));

    }

    /**
     * 查询指定数据表的所有内容
     * @param $condition
     * @param null $options
     * @return array|PDOStatement
     * @author: huangcp
     * @logs:
     */
    public function select($condition,$options = null) {
        if(!isset($options)){
            $query = 'select * from `'.$this->_table.'` ';
        }else{
            $query = 'select '.$options.' from `'.$this->_table.'` ';
        }

        if(!empty($condition)){
            $query .= ' where 1=1 AND ';
            foreach($condition as $key=>$con){
                if(strtolower($key) !== 'limit'){
                    $query .= $key .'="'.$con .'" and ';
                }
            }
            $query = substr($query ,0,strlen($query)-4);
            if(isset($condition['limit'])){
                $query .= ' limit ' .$condition['limit'];
            }
        }
        $result = $this->_dbHandle->query($query);
        $result = $result->fetchAll();
        return $result;
    }

    /**
     * 根据条件删除数据
     * @param $con
     * @return int
     * @author: huangcp
     * @logs:
     */
    public function deleteByCondition($con){
        $query = 'delete from `'.$this->_table.'` ';
        if(!empty($con)){
            $query .= ' where 1=1 AND ';
            foreach($con as $key=>$val){
                $query .=  $key.' = \''.mysql_real_escape_string($val).'\' and ';
            }
            $query = substr($query ,0,strlen($query)-4);
        }
        $res = $this->_dbHandle->exec($query);
        return $res;
    }

    /**
     * 根据条件修改数据
     * @param $change
     * @param $con
     * @return int
     * @author: huangcp
     * @logs:
     */
    public function updateByCondition($change,$con){
        if(!empty($con)){
            $sql  = "UPDATE `".$this->_table."` SET " ;
            foreach($change as $key=>$val){
                $sql .= '`'.$key.'`="'.$val.'",';
            }
            $sql = substr($sql ,0,strlen($sql)-1);
            $sql .= ' where 1=1 AND ';
            foreach($con as $k=>$item){
                $sql .= '`'.$k.'`='.$item.' and ';
            }
            $sql = substr($sql ,0,strlen($sql)-4);
            $res = $this->_dbHandle->exec($sql);
            return $res;
        }
        return null;
    }

    /**
     * 写入数据操作
     * @param $val
     * @return array
     * @author: huangcp
     * @logs:
     */
    public function insert($val){
        $this->_dbHandle->beginTransaction();
        try{
            $insert = 'insert into '.$this->_table.'(';
            foreach($val as $key=>$item){
                $insert .= '`'.$key .'`,';
            }
            $insert = substr($insert ,0,strlen($insert)-1);
            $insert .= ') value (';
            foreach($val as $item){
                $insert.= '\''.mysql_real_escape_string($item).'\',';
            }
            $insert = substr($insert ,0,strlen($insert)-1);
            $insert .= ')';

            return $this->_dbHandle->query($insert);
        }catch(PDOexception $e){
            $this->_dbHandle->rollback();
            echo $e->getCode().'-----'.$e->getMessage();
            $this->_dbHandle = null;
        }
    }


}