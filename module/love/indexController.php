<?php

include_once(dirname(dirname(dirname( __FILE__ ))).'/module/util/WebController.php') ;


class IndexController extends WebController {
    /**
     * ָ���鿴Ԫ��
     */
    public function indexAction() {
//        phpinfo();
//        $inModel = new IndexModel();
//        $inModel->add();
        $this->render();
    }
}