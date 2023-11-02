<?php
namespace app\common\controller;

use think\facade\{Session};
use app\admin\model\Admin;
use app\common\model\Rule;

class AddonsAdmin extends \think\Addons{
    public $noAuth = [];
    public $noLogin = [];
    public $post = [];
    public $get = [];
    public $param = [];
    public $admin;
    public $group;
    use \think\Jump;
    public function initialize(){
        parent::initialize();
        $this->post = $this->request->post();
        $this->get = $this->request->get();
        $this->param = $this->request->param();
        if(Session::has('admin_id')){
            $admin = Admin::find(Session::get('admin_id'));
            if(!$admin){
                return $this->error('管理员不存在');
            }
            if($admin['status']!=1){
                return $this->error('管理员已禁用');
            }
            $this->admin = $admin;
            if(!in_array($this->request->action(),$this->noLogin)&&$admin['group']['rules']!='*'&&!in_array($this->request->action(),$this->noAuth)){
                $where = [
                    ['uri','=',strtolower($this->request->controller()).'/'.$this->request->action()],
                    ['addon_name','=',$this->name]
                ];
                $rule = Rule::where($where)->find();
                if(!$rule){
                    return $this->error('节点不存在','','',5000);
                }
                if(!in_array($rule['id'],explode(',',$admin['group']['rules']))){
                    return $this->error('暂无权限','','',5000);
                }
            }
            $this->assign('admin',$admin);
        }else if(!in_array($this->request->action(),$this->noLogin)){
            return $this->error('请先登录','index/login');
        }
    }
    
    public function install(){}
    
    public function uninstall(){}
}