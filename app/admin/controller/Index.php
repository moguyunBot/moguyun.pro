<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\model\Rule;
use think\facade\{View,Session};

class Index extends Base{
    public $noLogin = ['login'];
    public $noAuth = ['index','main'];
    
    public function index(){
        $where = [
            ['addon_name','=',''],
            ['status','=',1],
            ['is_menu','=',1]
        ];
        if($this->admin['group']['rules']!='*'){
            $where[] = ['id','in',$this->admin['group']['rules']];
        }
        $rules = Rule::where($where)->order('sort asc')->select();
        $menus = Rule::children_menus($rules);
        // $path = public_path().'addons';
        // $dirs = opendir($path);
        // $addons = [];
        // if ($dirs) {
        //     while (($dir = readdir($dirs)) !== FALSE) {
        //         if(in_array($dir,['.','..']))continue;
        //         if(!is_file($path.'/'.$dir.'/Plugin.php'))continue;
        //         $info = get_addons_info($dir);
        //         if(empty($info))continue;
        //         $addons[] = [
        //             'title'     =>  $info['title'],
        //             'href'      =>  (string)url('addon/index',['addon_name'=>$info['name']])
        //         ];
        //     }
        // }
        // closedir($dirs);
        // array_push($menus,[
        //     'title'     =>  '应用中心',
        //     'icon'      =>  'mdi mdi-apps',
        //     'href'      =>  '#',
        //     'children'  =>  $addons,
        // ]);
        return View::fetch('',['menus'=>$menus]);
    }
    
    public function main(){
        
        return View::fetch();
    }
    
    public function login(){
        if($this->request->method()=='POST'){
            try{
                validate([
                    'username|用户名'       =>  'require',
                    'password|密码'         =>  'require',
                    'captcha|验证码'        =>  'require|captcha'
                ])->check($this->post);
                $admin = Admin::where(['username'=>$this->post['username']])->find();
                if(!$admin){
                    throw new \Exception('用户名或密码错误');
                }
                if($admin['password']!=md5($this->post['password'].$admin['salt'])){
                    throw new \Exception('用户名或密码错误');
                }
                Session::set('admin_id',$admin['id']);
            }catch(\Exception $e){
                return json(['code'=>0,'msg'=>$e->getMessage()?:'登录失败']);
            }
            return json(['code'=>1,'msg'=>'登录成功，页面即将跳转~','url'=>'index']);
        }
        Session::delete('admin_id');
        return View::fetch();
    }
}