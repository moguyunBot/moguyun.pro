<?php

namespace app\admin\controller;

use app\common\model\{Config as ConfigM,ConfigCate};
use think\facade\{Db,Cache,View};

class Config extends Base
{
    public $noAuth = ['upload_image','upload_video'];
    public function list(){
        $where = [
            ['addon_name','=','']
        ];
        $cates = ConfigCate::where($where)->order('sort asc,id desc')->paginate(['list_rows'=>20,'query'=>$this->get]);
        return View::fetch('',['cates'=>$cates]);
    }
    public function index()
    {
        if(!empty($this->get['addon_name'])){
            $configs_path = public_path().'addons/'.$this->get['addon_name'].'/configs.php';
        }else{
            $configs_path = config_path().'configs.php';
        }
        if($this->request->isPost()){
            try{
                if(empty($this->post)){
                    throw new \Exception('配置不能为空');
                }
                foreach($this->post as $k=>$v){
                    foreach($v['configs'] as $k1=>$v1){
                        if($v1['type']=='image'){
                            if($this->request->file($k.$k1.'value')){
                                $this->post[$k]['configs'][$k1]['value'] = upload($this->request->file($k.$k1.'value'));
                            }
                        }
                    }
                }
                $str = var_export($this->post,true);
                file_put_contents($configs_path,'<?php return '.$str.';');
                
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
                if(!empty($this->get['addon_name'])){
                    hook('config',$this->get['addon_name']);
                }
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'修改失败');
            }
            return $this->success('修改成功');
        }
        if(is_file($configs_path)){
            $configs = include($configs_path);
        }else{
            $configs = [];
        }
        return View::fetch('', ['configs' => $configs]);
    }

    public function cate_add()
    {
        if ($this->request->isAjax()) {
            try {
                validate([
                    'name|分组名称'     =>  'require|unique:config_cate',
                    'alias|分组别名'    =>  'require|unique:config_cate',
                ])->check($this->post);
                $cate = ConfigCate::create($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '添加失败');
            }
            return $this->success('添加成功','list');
        }
        return View::fetch('');
    }
    
    public function cate_edit(){
        $cate = ConfigCate::find($this->get['id']);
        if($this->request->isAjax()){
            try{
                validate([
                    'name|分类名称'     =>  'require|unique:config_cate',
                    'alias|分类标识'    =>  'require|unique:config_cate',
                ])->check($this->post);
                $cate->replace()->save($this->post);
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'修改失败');
            }
            return $this->success('修改成功','list');
        }
        return View::fetch('',['cate'=>$cate]);
    }
    
    public function cate_del(){
        if($this->request->isAjax()){
            try{
                $cate = ConfigCate::find($this->post['id']);
                if(!$cate){
                    throw new \Exception('分类不存在');
                }
                $cate->delete();
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'删除失败');
            }
            return $this->success('删除成功');
        }
    }

    public function upload_image()
    {
        if ($this->request->isPost()) {
            try {
                $file = $this->request->file('image');
                validate(['image' => 'filesize:2048000|fileExt:jpg,png,gif'])->check(['image' => $file]);
                $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file);
                $savename = '/storage/' . $savename;
            } catch (\think\exception\ValidateException $e) {
                return json(['errno'=>1,'message'=>$e->getMessage()?:'上传失败']);
            }
            return json(['errno'=>0,'data'=>['url'=>$savename]]);
        }
    }


    public function upload_video()
    {
        if ($this->request->isPost()) {
            try {
                $file = $this->request->file('video');
                validate(['video' => 'filesize:200048000|fileExt:mp4,wmv,avi,mov'])->check(['video' => $file]);
                $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file);
                $savename = '/storage/' . $savename;
            } catch (\think\exception\ValidateException $e) {
                return json(['errno'=>1,'message'=>$e->getMessage()?:'上传失败']);
            }
            return json(['errno'=>0,'data'=>['url'=>$savename]]);
        }
    }

    public function add()
    {
        if ($this->request->isPost()) {
            try {
                validate([
                    'name|配置项名称'       =>  'require|unique:config,name^cate_id',
                    'alias|配置项标识'      =>  'require|unique:config,name^cate_id',
                    'cate_id|配置分类'      =>  'require',
                    'type|表单类型'         =>  'require'
                ])->check($this->post);
                $config = ConfigM::create($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '添加失败');
            }
            return $this->success('添加成功','list');
        }
        $cate = ConfigCate::find($this->get['cate_id']);
        return View::fetch('',['cate'=>$cate]);
    }
    
    public function edit(){
        $config = ConfigM::with('cate')->find($this->get['id']);
        if($this->request->isAjax()){
            Db::startTrans();
            try{
                validate([
                    'name|配置项名称'       =>  'require|unique:config,name^cate_id',
                    'alias|配置项标识'      =>  'require|unique:config,alias^cate_id',
                    'cate_id|配置分类'      =>  'require',
                    'type|表单类型'         =>  'require', 
                ])->check($this->post);
                $where = [
                    ['cate_id','=',$this->post['cate_id']],
                    ['alias','=',$this->post['alias']],
                ];
                unset($this->post['id']);
                unset($this->post['cate_alias']);
                ConfigM::where($where)->update($this->post);
                $config->replace()->save($this->post);
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                return $this->error($e->getMessage()?:'修改失败');
            }
            return $this->success('修改成功','list');
        }
        return View::fetch('',['config'=>$config,'cate'=>$config['cate']]);
    }
    
    public function del(){
        if($this->request->isAjax()){
            try{
                $config = ConfigM::find($this->post['id']);
                if(!$config){
                    throw new \Exception('配置项不存在');
                }
                $config->delete();
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'删除失败');
            }
            return $this->success('删除成功');
        }
    }
}
