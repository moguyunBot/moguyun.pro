<?php
namespace app\common\controller;

use think\facade\{Session};
use app\common\model\User;

class AddonsIndex extends \think\Addons{
    public $noLogin = [];
    public $post = [];
    public $get = [];
    public $param = [];
    public $user;
    public $group;
    use \think\Jump;
    public function initialize(){
        parent::initialize();
        if(is_file($this->addon_path.'vendor/autoload.php'))
        include($this->addon_path.'vendor/autoload.php');
        $this->post = $this->request->post();
        $this->get = $this->request->get();
        $this->param = $this->request->param();
        if(Session::has('user_id')){
            $user = User::find(Session::get('user_id'));
            if(!$user){
                return $this->error('管理员不存在');
            }
            if($user['status']!=1){
                return $this->error('管理员已禁用');
            }
            $this->user = $user;
            if(!in_array($this->request->action(),$this->noLogin)&&$user['group']['rules']!='*'&&!in_array($this->request->action(),$this->noAuth)){
                $rule = Rule::where(['uri'=>strtolower($this->request->controller()).'/'.$this->request->action()])->find();
                if(!$rule){
                    return $this->error('节点不存在','','',5000);
                }
                if(!in_array($rule['id'],explode(',',$user['group']['rules']))){
                    return $this->error('暂无权限','','',5000);
                }
            }
            $this->assign('user',$user);
        }else if(!in_array($this->request->action(),$this->noLogin)){
            // return $this->fetch('index/login.html');
            return $this->error('请先登录','index/login');
        }
    }
    
    public function install(){}
    
    public function uninstall(){}
}