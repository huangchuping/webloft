<?php
include_once(dirname(dirname(dirname( __FILE__ ))).'/module/util/WebController.php') ;
/**
 * Created by PhpStorm.
 * User: xuanshao
 * Date: 2016/3/15
 * Time: 21:51
 */
class IndexController extends WebController {
    /**
     * ָ���鿴Ԫ��
     */
    public function indexAction() {
//        phpinfo();
        $inModel = new IndexModel();
        $inModel->add();
        $this->render();
    }
}