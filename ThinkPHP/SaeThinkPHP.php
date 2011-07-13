<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: ThinkPHP.php 1829 2010-10-18 08:15:58Z liu21st $

/**
  +------------------------------------------------------------------------------
 * ThinkPHP公共文件
  +------------------------------------------------------------------------------
 */
if (!isset($_SERVER["HTTP_APPNAME"])) {//非SAE平台下运行，加载普通核心
    define("IS_SAE",FALSE);
    require dirname(__FILE__) . '/ThinkPHP.php';
    App::run();
    exit();
}

/**
 * 模板缓存类
 * 用memcache存储ThinkPHP模版编译缓存。
 * 缓存的有效期和正常模式下的文件缓存一样。
 */
class Tplcache {

    //实例化对象
    static private $instance;
    //缓存对象
    private $handler;
    //记录缓存创建时间
    public $mtime;
    private function __construct() {
        $this->handler = memcache_init(); //初始化memcache
        if (!is_object($this->handler)) {
            header("Content-Type:text/html; charset=utf-8");
            exit('<div style=\'font-weight:bold;float:left;width:430px;text-align:center;border:1px solid silver;background:#E8EFFF;padding:8px;color:red;font-size:14px;font-family:Tahoma\'>您的Memcache还没有初始化，请登录SAE平台进行初始化~</div>');
        }
    }

    //获得单例对象
    static public function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new Tplcache();
        }
        return self::$instance;
    }

    /**
     * 写入模板缓存
     * 写入时同时记录了创建时间。
     * 缓存名称加上了SAE版本号，避免同一应用不同版本缓存共享。
     */
    public function set($name, $value) {
        $this->handler->set($name . "_" . $_SERVER["HTTP_APPVERSION"], time() . $value, MEMCACHE_COMPRESSED, 0);
    }

    /**
     * 获得模板缓存，同时记录模版的创建时间
     */
    public function get($name) {
        $content = $this->handler->get($name . "_" . $_SERVER["HTTP_APPVERSION"]);
        if ($content !== false) {
            $this->mtime = substr($content, 0, 10);
            return substr($content, 10);
        } else {
            return false;
        }
    }

    /**
     * 删除模板缓存
     */
    public function delete($name) {
        return $this->handler->delete($name . "_" . $_SERVER["HTTP_APPVERSION"]);
    }

    public function __call($name, $args) {
        return call_user_func_array(array($this->handler, $name), $args);
    }

}

define('THINK_MODE', "Sae");
define('IS_SAE', TRUE);
// 记录和统计时间（微秒）
if (!function_exists("G")) {

    function G($start, $end='', $dec=3) {
        static $_info = array();
        if (!empty($end)) { // 统计时间
            if (!isset($_info[$end])) {
                $_info[$end] = microtime(TRUE);
            }
            return number_format(($_info[$end] - $_info[$start]), $dec);
        } else { // 记录时间
            $_info[$start] = microtime(TRUE);
        }
    }

}

//记录开始运行时间
G('beginTime');
if (!defined('APP_PATH'))
    define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
if (!is_dir(APP_PATH . "/Lib/"))
    exit('<div style=\'font-weight:bold;float:left;width:345px;text-align:center;border:1px solid silver;background:#E8EFFF;padding:8px;color:red;font-size:14px;font-family:Tahoma\'>请手动生成项目目录~</div>');
if (!defined('RUNTIME_PATH'))
    define('RUNTIME_PATH', APP_PATH . '/Runtime/');
if (!defined('APP_CACHE_NAME'))
    define('APP_CACHE_NAME', 'app'); // 指定缓存名称
if (defined('RUNTIME_ALLINONE') && is_file(RUNTIME_PATH . '~allinone.php')) {
    // ALLINONE 模式直接载入allinone缓存
    $result = require RUNTIME_PATH . '~allinone.php';
    C($result);
    // 自动设置为运行模式
    define('RUNTIME_MODEL', true);
} else {
    if (version_compare(PHP_VERSION, '5.0.0', '<'))
        die('require PHP > 5.0 !');
    // ThinkPHP系统目录定义
    if (!defined('THINK_PATH'))
        define('THINK_PATH', dirname(__FILE__));
    if (!defined('APP_NAME'))
        define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));
    $runtime = defined('THINK_MODE') ? '~' . strtolower(THINK_MODE) . '_runtime.php' : '~runtime.php';
    $cache = Tplcache::getInstance();
    $content = $cache->get($runtime);
    if ($content !== false) {
        // 加载框架核心编译缓存
        eval("?>" . $content);
        //require RUNTIME_PATH.$runtime;
    } else {
        // 加载编译函数文件
        require THINK_PATH . "/Mode/Sae/runtime.php";
        // 生成核心编译~runtime缓存
        build_runtime();
    }
}
// 记录加载文件时间
G('loadTime');
?>