<?php
/**
 * DEMO
 * User: Administrator
 * Date: 14-11-12
 * Time: 下午3:45
 */
class DemoModel extends WebModel {

    /**
     * 保存信息
     * @param $items
     * @return bool
     */
    public function saveInfo($items){
        $db = new DemoInfos();
        return $db->saveInfo($items);
    }

    /**
     * 查询所有数据
     * @param $getter
     * @return array
     */
    public function selectAllInfos($getter){
        $db = new DemoInfos();
        return $db->selectAllInfo($getter);
    }

    /**
     * 根据指定ID删除元素
     * @param $id
     * @return array
     */
    public function deleteItem($id){
        $db = new DemoInfos();
        return $db->update($id);
    }

    /**
     * 根据指定ID查询元素
     * @param $id
     * @return array
     */
    public function selectItem($id){
        $db = new DemoInfos();
        return $db->selectById($id);
    }
}
