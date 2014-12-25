<?php
/**
 * DEMO
 * User: WEBLOFTER
 * 框架DB层
 * 主要处理数据库CURD操作
 */

//类名必须以驼峰命名跟数据库表名一致
class DemoInfos extends WebDb{

    //数据库所有字段映射（除ID）
    private $name = 'item_name';
    private $status = 'status';

    /**
     * 保存信息
     * @param $items
     * @return array
     */
    public function saveInfo($items){
        $infos = array();
        $infos[$this->name] = $items['name'];
        $infos[$this->status] = 'A';
        return $this->insert($infos);
    }

    /**
     * 删除
     * @param $items
     * @return array
     */
    public function delete($items){
        $infos['id'] = $items['id'];
        return $this->deleteByCondition($infos);
    }

    /**
     * 修改
     * @param $items
     * @return int
     */
    public function update($items){
        $change['status'] = 'D';
        $condition['id'] = $items['id'];
        return $this->updateByCondition($change,$condition);
    }

    /**
     * 查询所有信息
     * @param $getter
     * @return array|PDOStatement
     */
    public function selectAllInfo($getter){
        $condition['status'] = 'A';
        $options = 'count(*) count';
        $count = $this->select($condition,$options);//查询全部内容个数
        //分页
        $currentPage = isset($getter)?$getter:1;
        $myPage  = new pager($count[0]['count'],intval($currentPage),5);
        $pageStr = $myPage->GetPagerContent();
        $page = $myPage->getPageInfo();
        foreach($page as $key=>$item){
            $condition['limit'] = $key . ',' .$item;
        }
        $res = $this->select($condition);//分页查询内容

        $result['page'] = $pageStr;
        $result['infos'] = $res;
        return $result;
    }

    /**
     * 根据ID指定查询
     * @param $id
     * @return mixed
     */
    public function selectById($id){
        $condition['id'] = $id;
        $condition['status'] = 'A';
        return $this->select($condition);
    }

}