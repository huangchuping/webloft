<?php
/**
 * Created by WEBLOFT.
 * User: Administrator
 * Date: 14-11-12
 */

class DemoController extends WebController {

    /**
     * 验证码调用方法
     */
    public function codeAction(){
        $code = new Captcha();
        $length = 4;
        $param = array(
            'width'  => 25,    //captcha 字符宽度
            'height' => 20,    //captcha 字符高度
            'pnum'   => 100,   // 干扰点个数
            'lnum'   => 0     //干扰线条数
        );
        $code->create($length,$param);

    }

    /**
     * 指定查看元素
     */
    public function viewAction() {
        $geter = $this->GET('id');//这里是重点  获取get参数
        $item = new DemoModel();
        $res = $item->selectItem($geter);
        if(!empty($res)){
            $this->sign('item',$res);//向页面渲染数据
        }else{
            $this->sign('error','没查到数据');//向页面渲染数据
        }

        $this->render();
    }

    /**
     * 查询所有
     */
    public function viewAllAction() {
        $getter = $this->GET('page');//页数
        $item   = new DemoModel();
        $res    = $item->selectAllInfos($getter);
        $this->sign('page',$res['page']);//向页面渲染数据
        $this->sign('item',$res['infos']);//向页面渲染数据
        $this->render();
    }

    /**
     * 添加
     */
    public function addAction() {
        $todo = $this->POST('name');
        $codes = $this->POST('code');
        $code = new Captcha();
        $result = $code->check($codes);
        if($result){
            $items['name'] = $todo;
            $item = new DemoModel();
            $save = $item->saveInfo($items);
            $this->sign('item',$save);//向页面渲染数据
            $this->render();
        }else{
            $this->render('/home/demo/viewAll');
        }
    }

    /**
     * 删除指定元素
     */
    public function deleteAction() {
        $geter = $this->GET('id');//这里是重点  获取get参数
        $get['id'] = $geter;
        $item = new DemoModel();
        $res = $item->deleteItem($get);
        $this->sign('item',$res);//向页面渲染数据
        $this->render('/home/demo/view/id/104');
    }

}