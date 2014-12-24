<?php
/**
 * 验证码类
 * Created by WEBLOFT.
 * User: huangcp
 */

class Captcha{


    private $sname = '';

    public function __construct($sname=''){ // $sname captcha session name
        $this->sname = $sname==''? 'm_captcha' : $sname;
    }

    /** 生成验证码图片
     * @param int $length 验证码长度
     * @param Array $param 參數
     * @return IMG
     */
    public function create($length=4,$param=array()){
        Header("Content-type: image/PNG");
        $authnum = $this->random($length); //生成验证码字符.

        $width  = isset($param['width'])? $param['width'] : 13; //文字宽度
        $height = isset($param['height'])? $param['height'] : 18; //文字高度
        $pnum   = isset($param['pnum'])? $param['pnum'] : 100; //干扰象素个数
        $lnum   = isset($param['lnum'])? $param['lnum'] : 2; //干扰线条数

        $this->captcha_session($this->sname,$authnum);  //将随机数写入session

        $pw = $width*$length+10;
        $ph = $height+6;

        $im = imagecreate($pw,$ph);   //imagecreate() 新建图像，大小为 x_size 和 y_size 的空白图像。
        $black = ImageColorAllocate($im, 238,238,238); //设置背景颜色

        $values = array(
            mt_rand(0,$pw), mt_rand(0,$ph),
            mt_rand(0,$pw), mt_rand(0,$ph),
            mt_rand(0,$pw), mt_rand(0,$ph),
            mt_rand(0,$pw), mt_rand(0,$ph),
            mt_rand(0,$pw), mt_rand(0,$ph),
            mt_rand(0,$pw), mt_rand(0,$ph)
        );
        imagefilledpolygon($im, $values, 6, ImageColorAllocate($im, mt_rand(170,255),mt_rand(200,255),mt_rand(210,255))); //设置干扰多边形底图

        /* 文字 */
        for ($i = 0; $i < strlen($authnum); $i++){
            $font = ImageColorAllocate($im, mt_rand(0,50),mt_rand(0,150),mt_rand(0,200));//设置文字颜色
            $x = $i/$length * $pw + rand(1, 6); //设置随机X坐标
            $y = rand(1, $ph/3);   //设置随机Y坐标
            imagestring($im, mt_rand(4,6), $x, $y, substr($authnum,$i,1), $font);
        }

        /* 加入干扰象素 */
        for($i = 0; $i<$pnum; $i++){
            $dist = ImageColorAllocate($im, mt_rand(0,255),mt_rand(0,255),mt_rand(0,255)); //设置杂点颜色
            imagesetpixel($im, mt_rand(0,$pw) , mt_rand(0,$ph) , $dist);
        }

        /* 加入干扰线 */
        for($i = 0; $i<$lnum; $i++){
            $dist = ImageColorAllocate($im, mt_rand(50,255),mt_rand(150,255),mt_rand(200,255)); //设置线颜色
            imageline($im,mt_rand(0,$pw),mt_rand(0,$ph),mt_rand(0,$pw),mt_rand(0,$ph),$dist);
        }

        ImagePNG($im); //以 PNG 格式将图像输出到浏览器或文件
        ImageDestroy($im); //销毁一图像
    }

    /** 检查验证码
     * @param String $captcha 验证码
     * @param int $flag 验证成功后 0:不清除session 1:清除session
     * @return boolean
     */
    public function check($captcha,$flag=1){
        if(empty($captcha)){
            return false;
        }else{
            if(strtolower($captcha) == strtolower($this->captcha_session($this->sname))){ //检测验证码
                if($flag == 1){
                    $this->captcha_session($this->sname,'');
                }
                return true;
            }else{
                return false;
            }
        }
    }

    /* 产生随机数函数
    * @param int $length 需要随机生成的字符串數
    * @return String
    */
    private function random($length){
        $hash  = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /** 验证码session处理方法
     * @param String $name captcha session name
     * @param String $value
     * @return String
     */
    private function captcha_session($name,$value=null){
        if(isset($value)){
            if($value!==''){
                $_SESSION[$name] = $value;
            }else{
                unset($_SESSION[$name]);
            }
        }else{
            return isset($_SESSION[$name])? $_SESSION[$name] : '';
        }
    }
}
