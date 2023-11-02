<?php

namespace app\common\middleware;

class Config
{
    public function handle($request, \Closure $next)
    {
        if(!is_file(config_path().'install.lock')){
            return redirect('/install.php')->send();
        }
        $configs = include(config_path().'configs.php');
        $dirs = opendir(public_path().'addons');
        
        while (($dir = readdir($dirs)) !== FALSE) {
            if(in_array($dir,['.','..']))continue;
            if(is_dir(public_path().'addons/'.$dir)&&is_file(public_path().'addons/'.$dir.'/configs.php')){
                foreach(include(public_path().'addons/'.$dir.'/configs.php') as $key=>$val){
                    $configs[$dir.'_'.$key] = $val;
                }
            }
        }
        foreach($configs as $k=>$v){
            $config = [];
            foreach($v['configs'] as $k1=>$v1){
                $config[$k1] = $v1['value'];
            }
            \think\facade\Config::set($config,$k);
        }
        return $next($request);
    }
}
