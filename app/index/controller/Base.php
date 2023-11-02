<?php
namespace app\index\controller;

use think\facade\{Session,View};
use app\common\model\User;

class Base extends \app\BaseController{
    public $noLogin = [];
    public $post = [];
    public $get = [];
    public $param = [];
    public $user;
    public $group;
    use \think\Jump;
    public function initialize(){
        parent::initialize();
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
            View::assign('user',$user);
        }else if(!in_array($this->request->action(),$this->noLogin)){
            return $this->error('请先登录','login/index');
        }
    }
}