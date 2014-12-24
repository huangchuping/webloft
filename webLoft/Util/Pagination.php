<?php
/**
 * Created by WEBLOFT.
 * User: Huangcp
 *
 * PHP分页类
 * @package Page
 */

class Pager{
    private $pageSize = 10;
    private $pageIndex;
    private $totalNum;
    private $totalPagesCount;
    private $pageUrl;

    public function __construct($p_totalNum, $p_pageIndex, $p_pageSize = 10, $p_initNum = 3, $p_initMaxNum = 5){
        if (!isset ($p_totalNum) || !isset($p_pageIndex)) {
            die ("pager initial error");
        }
        $this->totalNum = $p_totalNum;
        $this->pageIndex = $p_pageIndex;
        $this->pageSize = $p_pageSize;
        $this->initNum = $p_initNum;
        $this->initMaxNum = $p_initMaxNum;
        $this->totalPagesCount = ceil($p_totalNum / $p_pageSize);
        $this->pageUrl = $this->_getPageUrl();
        $this->_initPagerLegal();
    }

    /**
     * 获取去除page部分的当前URL字符串
     *
     * @return String URL字符串
     */
    private function _getPageUrl(){
        if(strpos(__URL__,'/page/')){
            $CurrentUrl = substr(__URL__,0,18). "/page";
        }else{
            $CurrentUrl =__URL__. "/page";
        }
        return $CurrentUrl;
    }

    /**
     * 设置页面参数合法性
     * @return void
     */
    private function _initPagerLegal(){
        if ((!is_numeric($this->pageIndex)) || $this->pageIndex < 1) {
            $this->pageIndex = 1;
        } elseif ($this->pageIndex > $this->totalPagesCount) {
            $this->pageIndex = $this->totalPagesCount;
        }
    }

    /**
     * 获取分页样式及信息
     * @return string
     */
    public function GetPagerContent(){
        $str = "<div class=\"Pagination\">";
        //首页 上一页
        if ($this->pageIndex == 1) {
            $str .= "<a href='javascript:void(0)' class='tips' title='首页'>首页</a> " . "\n";
            $str .= "<a href='javascript:void(0)' class='tips' title='上一页'>上一页</a> " . "\n" . "\n";
        } else {
            $str .= "<a href='{$this->pageUrl}/1' class='tips' title='首页'>首页</a> " . "\n";
            $str .= "<a href='{$this->pageUrl}/" . ($this->pageIndex - 1) . "' class='tips' title='上一页'>上一页</a> " . "\n" . "\n";
        }
        //除首末后 页面分页逻辑
        //10页（含）以下
        $currnt = "";
        if ($this->totalPagesCount <= 10) {
            for ($i = 1; $i <= $this->totalPagesCount; $i++) {
                if ($i == $this->pageIndex) {
                    $currnt = " class='current'";
                } else {
                    $currnt = "";
                }
                $str .= "<a href='{$this->pageUrl}/{$i} ' {$currnt}>$i</a>" . "\n";
            }
        } else { //10页以上
            if ($this->pageIndex < 3) { //当前页小于3
                for ($i = 1; $i <= 3; $i++) {
                    if ($i == $this->pageIndex) {
                        $currnt = " class='current'";
                    } else {
                        $currnt = "";
                    }
                    $str .= "<a href='{$this->pageUrl}/{$i} ' {$currnt}>$i</a>" . "\n";
                }
                $str .= "<span class=\"dot\">……</span>" . "\n";
                for ($i = $this->totalPagesCount - 3 + 1; $i <= $this->totalPagesCount; $i++){ //功能1
                    $str .= "<a href='{$this->pageUrl}/{$i}' >$i</a>" . "\n";
                }
            } elseif ($this->pageIndex <= 5) { //   5 >= 当前页 >= 3
                for ($i = 1; $i <= ($this->pageIndex + 1); $i++) {
                    if ($i == $this->pageIndex) {
                        $currnt = " class='current'";
                    } else {
                        $currnt = "";
                    }
                    $str .= "<a href='{$this->pageUrl}/{$i} ' {$currnt}>$i</a>" . "\n";

                }
                $str .= "<span class=\"dot\">……</span>" . "\n";
                for ($i = $this->totalPagesCount - 3 + 1; $i <= $this->totalPagesCount; $i++){ //功能1
                    $str .= "<a href='{$this->pageUrl}={$i}' >$i</a>" . "\n";
                }
            } elseif (5 < $this->pageIndex && $this->pageIndex <= $this->totalPagesCount - 5) { //当前页大于5，同时小于总页数-5
                for ($i = 1; $i <= 3; $i++) {
                    $str .= "<a href='{$this->pageUrl}/{$i}' >$i</a>" . "\n";
                }
                $str .= "<span class=\"dot\">……</span>";
                for ($i = $this->pageIndex - 1; $i <= $this->pageIndex + 1 && $i <= $this->totalPagesCount - 5 + 1; $i++) {
                    if ($i == $this->pageIndex) {
                        $currnt = " class='current'";
                    } else {
                        $currnt = "";
                    }
                    $str .= "<a href='{$this->pageUrl}/{$i} ' {$currnt}>$i</a>" . "\n";
                }
                $str .= "<span class=\"dot\">……</span>";
                for ($i = $this->totalPagesCount - 3 + 1; $i <= $this->totalPagesCount; $i++) {
                    $str .= "<a href='{$this->pageUrl}/{$i}' >$i</a>" . "\n";
                }
            } else {
                for ($i = 1; $i <= 3; $i++) {
                    $str .= "<a href='{$this->pageUrl}/{$i}' >$i</a>" . "\n";
                }
                $str .= "<span class=\"dot\">……</span>" . "\n";
                for ($i = $this->totalPagesCount - 5; $i <= $this->totalPagesCount; $i++){ //功能1
                    if ($i == $this->pageIndex) {
                        $currnt = " class='current'";
                    } else {
                        $currnt = "";
                    }
                    $str .= "<a href='{$this->pageUrl}/{$i} ' {$currnt}>$i</a>" . "\n";

                }
            }

        }

        //除首末后 页面分页逻辑结束
        //下一页 末页
        if ($this->pageIndex == $this->totalPagesCount) {
            $str .= "\n" . "<a href='javascript:void(0)' class='tips' title='下一页'>下一页</a>" . "\n";
            $str .= "<a href='javascript:void(0)' class='tips' title='末页'>末页</a>" . "\n";
        } else {
            $str .= "\n" . "<a href='{$this->pageUrl}/" . ($this->pageIndex + 1) . "' class='tips' title='下一页'>下一页</a> " . "\n";
            $str .= "<a href='{$this->pageUrl}/{$this->totalPagesCount}' class='tips' title='末页'>末页</a> " . "\n";
        }
        $str .= "</div>";
        return $str;
    }

    /**
     * 显示分页类容
     * @return mixed
     */
    public function getPageInfo(){
        $pages = (intval($this->pageIndex)-1) * intval($this->pageSize);
        $infos[$pages] = $this->pageSize;
        return $infos;
    }
}

