<?php

class Cache {

    /**
     * $dir
     * $lifetime
     * $cacheid
     * $ext
     */
    private $dir;
    private $lifetime;
    private $cacheid;
    private $ext;

    /**
     * 检查缓存目录是否有效,默认赋值
     */
    function __construct($dir='',$lifetime=1800) {
        if ($this->dir_isvalid($dir)) {
            $this->dir = $dir;
            $this->lifetime = $lifetime;
            $this->ext = '.Php';
            $this->cacheid = $this->getcacheid();
        }
    }

    /**
     * 检查缓存是否有效
     */
    private function isvalid() {
        if (!file_exists($this->cacheid)) return false;
        if (!(@$mtime = filemtime($this->cacheid))) return false;
        if (mktime(time()) - $mtime > $this->lifetime) return false;
        return true;
    }

    /**
     * 写入缓存
     * $mode == 0 , 以浏览器缓存的方式取得页面内容
     * $mode == 1 , 以直接赋值(通过$content参数接收)的方式取得页面内容
     * $mode == 2 , 以本地读取(fopen ile_get_contents)的方式取得页面内容(似乎这种方式没什么必要)
     */
    public function write($mode=0,$content) {
        switch ($mode) {
            case 0:
                $content = @ob_get_contents();
                break;
            default:
                break;
        }
        @ob_end_flush();
        try {
            file_put_contents($this->cacheid,$content);
        }
        catch (Exception $e) {
            $this->error('写入缓存失败!请检查目录权限!');
        }
    }

    /**
     * 加载缓存
     * exit() 载入缓存后终止原页面程序的执行,缓存无效则运行原页面程序生成缓存
     * ob_start() 开启浏览器缓存用于在页面结尾处取得页面内容
     */
    public function load() {
        if ($this->isvalid()) {
            echo "<span style='display:none;'>This is Cache.</span> ";
            return  file_get_contents($this->cacheid);
//            exit();
        }else {
//            $this->clean();
            ob_start();
            return false;
        }
    }


    public function get(){
        $filename = $this->cacheid;
        if (!file_exists($filename) || !is_readable($filename)){//can't read the cache
            return false;
        }
        if ( time() < (filemtime($filename) + $this->lifetime) ) {//cache for the key not expired
            $file = fopen($filename, "r");// read data file
            if ($file){//able to open the file
                $data = fread($file, filesize($filename));
                fclose($file);
                return $data;//return the values
            }
            else return false;
        }
        else return false;//was expired you need to create new
    }

    /**
     * 清除缓存
     */
    public function clean() {
        try {
            unlink($this->cacheid);
        }
        catch (Exception $e) {
            $this->error('清除缓存文件失败!请检查目录权限!');
        }
    }

    /**
     * 取得缓存文件路径
     */
    private function getcacheid() {
        return $this->dir.CACHE_PREFIX.md5($this->geturl()).$this->ext;
    }

    /**
     * 检查目录是否存在或是否可创建
     */
    private function dir_isvalid($dir) {
        if (is_dir($dir)) return true;
        try {
            mkdir($dir,0777,true);
        }
        catch (Exception $e) {
            $this->error('所设定缓存目录不存在并且创建失败!请检查目录权限!');
            return false;
        }
        return true;
    }

    /**
     * 取得当前页面完整url
     */
    private function geturl() {
        $url = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        }
        else {
            $url = $_SERVER['Php_SELF'];
            $url .= empty($_SERVER['QUERY_STRING'])?'':'?'.$_SERVER['QUERY_STRING'];
        }
        return $url;
    }

    /**
     * 输出错误信息
     */
    private function error($str) {
        echo '<div style="color:red;">'.$str.'</div>';
    }
}
