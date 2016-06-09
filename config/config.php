<?php
/**
 * 数据库配置文件及缓存规则设置
 * Created by WEBLOFT
 * User: huangCP
 */


// 设置数据库连接所需数据
define('DB_HOST','localhost');      //数据库主机地址
define('DB_NAME','testmvc');        //数据库名
define('DB_USER','root');           //数据库账号
define('DB_PASSWORD','123456');     //数据库密码
define('DB_PREFIX','loft_');        //数据库表名前缀

//从数据库-读
define('R_DB_HOST','localhost');      //数据库主机地址
define('R_DB_NAME','testmvc');        //数据库名
define('R_DB_USER','root');           //数据库账号
define('R_DB_PASSWORD','123456');     //数据库密码
define('R_DB_PREFIX','loft_');        //数据库表名前缀

//从数据库-写
define('C_DB_HOST','localhost');      //数据库主机地址
define('C_DB_NAME','testmvc');        //数据库名
define('C_DB_USER','root');           //数据库账号
define('C_DB_PASSWORD','123456');     //数据库密码
define('C_DB_PREFIX','loft_');        //数据库表名前缀

//设置php缓存
define('CACHE_DIR','../runTime/cache/'); //缓存目录
define('CACHE_PREFIX','cache_');     //缓存文件前缀
define('CACHE_TIME',1800);           //缓存时间
define('CACHE_MODE',2);              //mode 1 为serialize ，model 2为保存为可执行文件


//设置memcache


//设置redis


//设置邮件



