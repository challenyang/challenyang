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
// $Id$

// 生成核心编译缓存
function build_runtime() {
    // 加载常量定义文件
    require THINK_PATH.'/Common/defines.php';
    // 加载路径定义文件
    require defined('PATH_DEFINE_FILE')?PATH_DEFINE_FILE:THINK_PATH.'/Common/paths.php';
    // 读取核心编译文件列表
    if(is_file(CONFIG_PATH.'core.php')) {
        // 加载项目自定义的核心编译文件列表
        $list   =  include CONFIG_PATH.'core.php';
    }elseif(defined('THINK_MODE')) {
        // 根据设置的运行模式加载不同的核心编译文件
        $list   =  include THINK_PATH.'/Mode/'.strtolower(THINK_MODE).'.php';
    }else{
        // 默认核心
        $list = include THINK_PATH.'/Common/core.php';
    }
     // 加载兼容函数
    if(version_compare(PHP_VERSION,'5.2.0','<') )
        $list[]	= THINK_PATH.'/Common/compat.php';
    // 加载核心编译文件列表
    foreach ($list as $key=>$file){
        if(is_file($file))  require $file;
    }
    // 生成核心编译缓存 去掉文件空白以减少大小
    if(!defined('NO_CACHE_RUNTIME')) {
        $cache=  Tplcache::getInstance();
$compile = defined('RUNTIME_ALLINONE');
        $content  = compile(THINK_PATH.'/Common/defines.php',$compile);
        $content .= compile(defined('PATH_DEFINE_FILE')?   PATH_DEFINE_FILE  :   THINK_PATH.'/Common/paths.php',$compile);
        foreach ($list as $file){
            $content .= compile($file,$compile);
        }
        $runtime = defined('THINK_MODE')?'~'.strtolower(THINK_MODE).'_runtime.php':'~runtime.php';
        if(defined('STRIP_RUNTIME_SPACE') && STRIP_RUNTIME_SPACE == false ) {
          //  file_put_contents(RUNTIME_PATH.$runtime,'<?php'.$content);
            $cache->set($runtime,'<?php'.$content);
        }else{
          //  file_put_contents(RUNTIME_PATH.$runtime,strip_whitespace('<?php'.$content));
            $cache->set($runtime,strip_whitespace('<?php'.$content));
        }
        unset($content);
    }
}

// 批量创建目录
function mkdirs($dirs,$mode=0777) {
    foreach ($dirs as $dir){
        if(!is_dir($dir))  mkdir($dir,$mode);
    }
}

// 默认创建测试Action处理函数
if (!function_exists('build_action'))
{
    function build_action()
    {
        $content = file_get_contents(THINK_PATH.'/Tpl/'.(defined('BUILD_MODE')?BUILD_MODE:'AutoIndex').'.tpl.php');
        file_put_contents(LIB_PATH.'Action/IndexAction.class.php',$content);
    }
}
?>