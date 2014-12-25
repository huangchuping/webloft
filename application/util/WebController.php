<?php
/**
 * Created by WEBLOFT.
 * User: huangcp
 */
class WebController extends Controller{

    public $cache_info;

    public function init(){
        //运行缓存
        if (CACHE_ENABLE) {
            $this->_cache = new Cache(CACHE_DIR,10);
            if(!$this->_cache->load()){//装载缓存,缓存有效则不执行以下页面代码
//                $res = $this->getResource();
//                echo json_encode($res);
//                $this->_cache->write(2,json_encode($res));
            }else{
                $this->_cache->get();
            }
        }
    }

    public function setResource($res){
        $this->cache_info = $res;
    }

    public function getResource(){
        return $this->cache_info;
    }

    /**
     * 全局登录校验
     */
    public function loginCheck($type = null) {

    }
}