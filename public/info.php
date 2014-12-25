<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-11-12
 * Time: 下午3:51
 */

//phpinfo();
header("Content-type: text/html; charset=utf-8");


define('_CachePath_','../runTime/cache');      //模板缓存路径
define('_CacheEnable_',1);        //自动缓存机制是否开启，未定义或为空，表示关闭自动缓存机制
define('_ReCacheTime_',1800);        //自动重新缓存间隔时间，单位为秒，未定义或为空，表示关闭自动重新缓存


class caches
{
    var $cachefile;
    var $cachefilevar;

    function __construct()
    {
        //生成当前页的Cache组文件名 $this->cachefilevar 及文件名 $this->cachefile
        //动态页的参数不同对应的Cache文件也不同，但是每一个动态页的所有Cache文件都有相同的文件名，只是扩展名不同
        $s=array(".","/");$r=array("_","");
        $this->cachefilevar=str_replace($s,$r,$_SERVER["SCRIPT_NAME"]);
        $this->cachefile=$this->cachefilevar.".".md5($_SERVER["REQUEST_URI"]);
    }

    //删除当前页/模块的缓存
    function delete()
    {
        //删除当前页的缓存
        $d = dir(_CachePath_);
        $strlen=strlen($this->cachefilevar);
        //返回当前页的所有Cache文件组
        while (false !== ($entry = $d->read()))
        {
            if (substr($entry,0,$strlen)==$this->cachefilevar)
            {

                if (!unlink(_CachePath_."/".$entry)) {echo "Cache目录无法写入";exit;}
            }
        }
    }

    //判断是否已Cache过，以及是否需要Cache
    function check()
    {
        //如果设置了缓存更新间隔时间 _ReCacheTime_
        if (_ReCacheTime_ + 0 > 0)
        {
            //返回当前页Cache的最后更新时间
            $var=@file(_CachePath_."/".$this->cachefilevar);$var=$var[0];
            //如果更新时间超出更新间隔时间则删除Cache文件
            if (time()-$var>_ReCacheTime_)
            {
                $this->delete();$ischage=true;
            }
        }
        //返回当前页的Cache
        $file=_CachePath_."/".$this->cachefile;
        //判断当前页Cache是否存在 且 Cache功能是否开启
        return (file_exists($file) and _CacheEnable_ and !@$ischange);
    }

    //读取Cache
    function read()
    {
        //返回当前页的Cache
        $file=_CachePath_."/".$this->cachefile;
//        echo readfile($file);
        $content = readfile($file);
        $content = unserialize($content);
        //读取Cache文件的内容
        if (_CacheEnable_) return $content;
        else return false;
    }

    //生成Cache
    function write($output)
    {
        //返回当前页的Cache
        $file=_CachePath_."/".$this->cachefile;
        //如果Cache功能开启
        if (_CacheEnable_)
        {
            //把输出的内容写入Cache文件
            $fp=@fopen($file,'w');
            if (!@fputs($fp,$output)) {echo "模板Cache写入失败";exit;}
            @fclose($fp);
            //如果设置了缓存更新间隔时间 _ReCacheTime_
            if (_ReCacheTime_+0>0)
            {
                //更新当前页Cache的最后更新时间
                $file=_CachePath_."/".$this->cachefilevar;
                $fp=@fopen($file,'w');
                if (!@fwrite($fp,time())) {echo "Cache目录无法写入";exit;}
                @fclose($fp);
            }
        }
    }
}

$cache=new caches();
if ($cache->check()){
    $template = $cache->read();
}else{
    ob_start();
    ob_implicit_flush(0);
    echo '页面内容。。。。  ';
    $template = ob_get_contents();
    $cache->write(serialize($template));
}
