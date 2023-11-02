<?php
namespace app\admin\controller;

use app\common\model\{Rule,Config,ConfigCate,Addon as AddonM,UserAddon};
use think\facade\{Session,View,Db,Cache};

class Addon extends Base{
    public $noAuth = ['upload_image','upload_video','config','manage'];
    
    public function index(){
        if($this->request->isPost()){
            try{
                validate([
                    'file|压缩包'       =>  'require',
                ])->check(['file'=>$this->request->file('file')]);
                $addon_name = pathinfo($this->request->file('file')->getOriginalName(),PATHINFO_FILENAME);
                $zip = new \ZipArchive;
                if ($zip->open($this->request->file('file')->getRealPath()) === TRUE) {
                    if($zip->locateName($addon_name.'/info.ini')===false||$zip->locateName($addon_name.'/Plugin.php')===false){
                        $zip->close();
                        throw new \Exception('安装包错误');
                    }
                    //找到了INI文件
                    $iniContents = explode("\r\n",$zip->getFromName($addon_name.'/info.ini'));
                    $config = [];
                    foreach($iniContents as $v){
                        [$key,$val] = explode('=',$v);
                        $config[trim($key)] = trim($val);
                    }
                    if(isset($config['status'])&&$config['status']==1){
                        $zip->close();
                        throw new \Exception('安装包状态错误');
                    }
                    $extractPath = public_path().'addons';
                    $zip->extractTo($extractPath);
                    $zip->close();
                } else {
                    throw new \Exception('无法打开ZIP文件或ZIP文件损坏。');
                }
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'上传失败');
            }
            return $this->success('上传成功');
        }
        $path = public_path().'addons';
        $dirs = opendir($path);
        $addons = [];
        if ($dirs) {
            while (($dir = readdir($dirs)) !== FALSE) {
                if(in_array($dir,['.','..']))continue;
                if(!is_dir($path.'/'.$dir))continue;
                if(!is_file($path.'/'.$dir.'/Plugin.php'))continue;
                $info = get_addons_info($dir);
                if(empty($info))continue;
                $addons[] = $info;
            }
        }
        closedir($dirs);
        return View::fetch('',['addons'=>$addons]);
    }
    
    public function install(){
        if($this->request->isPost()){
            try{
                validate(['name|应用名称'=>'require'])->check($this->post);
                $addon = get_addons_instance($this->post['name']);
                $this->importsql($this->post['name']);
                $addon->install();
                $iniContents = explode("\r\n",file_get_contents(public_path().'addons/'.$this->post['name'].'/info.ini'));
                $iniStr = '';
                foreach($iniContents as $v){
                    if($v=="\r\n"||!$v||strpos($v,'=')===false)continue;
                    [$key,$val] = explode('=',$v);
                    if(trim($key)=='status'){
                        $v = 'status = 1';
                    }
                    $iniStr .= $v."\r\n";
                }
                file_put_contents(public_path().'addons/'.$this->post['name'].'/info.ini',trim($iniStr,"\r\n"));
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'安装失败');
            }
            return $this->success('安装成功');
        }
    }
    
    private static function importsql($name,$fileName=null)
    {
        $fileName = is_null($fileName) ? 'install.sql' : $fileName;
        $sqlFile = public_path().'addons/' .$name. '/'.$fileName;
        
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', env('DB_PREFIX', ''), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    
                    try {
                        Db::execute($templine);
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                        
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }
    
    public function uninstall(){
        if($this->request->isPost()){
            // try{
                validate(['name|应用名称'=>'require'])->check($this->post);
                $addon = get_addons_instance($this->post['name']);
                $addon->uninstall();
                $iniContents = explode("\r\n",file_get_contents(public_path().'addons/'.$this->post['name'].'/info.ini'));
                $iniStr = '';
                foreach($iniContents as $v){
                    if($v=="\r\n"||!$v||strpos($v,'=')===false)continue;
                    [$key,$val] = explode('=',$v);
                    if(trim($key)=='status'){
                        $v = 'status = 0';
                    }
                    $iniStr .= $v."\r\n";
                }
                file_put_contents(public_path().'addons/'.$this->post['name'].'/info.ini',trim($iniStr,"\r\n"));
            // }catch(\Exception $e){
            //     return $this->error($e->getMessage()?:'卸载失败');
            // }
            return $this->success('卸载成功');
        }
    }
    
    public function entrance(){
        $where = [
            ['status','=',1],
            ['is_menu','=',1]
        ];
        if($this->admin['group']['rules']!='*'){
            $where[] = ['id','in',$this->admin['group']['rules']];
        }
        
        $map1 = [
            ['addon_name', '=', $this->get['addon_name']]
        ];
        
        $map2 = [
            ['addon_name', '=', '']
        ];
        if($this->admin['group']['rules']!='*'){
            $map2[] = ['id','in',$this->admin['group']['rules']];
        }
        
        $configs_path = public_path().'addons/'.$this->get['addon_name'].'/configs.php';
        if(is_file($configs_path)&&count(include($configs_path))){
            $map2[] = ['uri', 'in', ['config/index','rule/index']];
        }else{
            $map2[] = ['uri', 'in', ['rule/index']];
        }
        $rules = Rule::where($where)->where(function($query) use($map1,$map2){
            $query->whereOr([$map1,$map2]);
        })->order('sort asc')->select()->map(function($v){
            if($v['uri']=='rule/index'){
                $v['pid'] = 0;
                $v['icon'] = 'mdi-menu';
            }
            if(!$v['addon_name']){
                $v['options'] = [
                    ["key"=>"addon_name","value"=>$this->get['addon_name']],
                    ["key"=>"key","value"=>'basic']
                ];
            }
            return $v;
        });
        $after = [];
        foreach($rules as $k=>$v){
            if(in_array($v['uri'],['config/index','rule/index'])){
                $after[] = $v->toArray();
                unset($rules[$k]);
            }
        }
        $rules = array_merge($rules->toArray(),$after);
        $menus = Rule::children_menus($rules);
        $info = get_addons_info($this->get['addon_name']);
        return View::fetch('',['menus'=>$menus,'info'=>$info]);
    }
}