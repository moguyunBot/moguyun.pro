<?php

namespace app\admin\controller;

use app\admin\model\{Admin as AdminM,Group};
use app\common\model\Rule;
use PHPGangsta\GoogleAuthenticator;
use think\facade\View;

class Admin extends Base
{
    public $noAuth = ['info','google_bind','get_google_secret','edit_pwd'];
    public function index(){
        $where = [
            ['username','<>','manage'],
            ['id','<>',$this->admin['id']],
        ];
        if (!empty($this->get['username'])) {
            $where[] = ['username', 'like', '%' . $this->get['username'] . '%'];
        }
        if(isset($this->get['status'])&&$this->get['status']!==''){
            $where[] = ['status','=',$this->get['status']];
        }
        $admins = AdminM::where($where)->order('id desc')->paginate(['list_rows' => 20, 'query' => $this->get]);
        // dump($admins->toArray());exit;
        return View::fetch('', ['admins' => $admins]);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            try {
                validate([
                    'group_id|角色'         =>  'require',
                    'username|用户名'       =>  'require|unique:admin',
                    'password|登录密码'     =>  'require|confirm:rePassword|length:5,25',
                ])->check($this->post);
                $this->post['salt'] = nonce_str();
                $this->post['admin_id'] = $this->admin['id'];
                $this->post['password'] = md5($this->post['password'] . $this->post['salt']);
                AdminM::create($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '添加失败');
            }
            return $this->success('添加成功', 'index');
        }
        $groups = Group::where([
            ['status','=',1],
            ['rules','<>','*'],
            ['id','<>',$this->admin->group['id']]
        ])->order('id desc')->select();
        // dump($groups);exit;
        return View::fetch('', ['groups' => $groups]);
    }

    public function edit()
    {
        if ($this->get['id'] == 1 && $this->admin['group_id'] != 1) {
            return $this->error('非法操作');
        }
        $admin = AdminM::find($this->get['id']);
        $admin->hidden(['password']);
        if ($this->request->isPost()) {
            try {
                validate([
                    'group_id|角色'         =>  'require',
                    'username|用户名'       =>  'require|unique:admin',
                    'password|登录密码'     =>  'confirm:rePassword|length:5,25',
                ])->check($this->post);
                if (!empty($this->post['password'])) {
                    $this->post['salt'] = nonce_str();
                    $this->post['password'] = md5($this->post['password'] . $this->post['salt']);
                }
                $admin->replace()->save($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '修改失败');
            }
            return $this->success('修改成功', 'index');
        }
        $groups = Group::where([
            ['status','=',1],
            ['rules','<>','*'],
            ['id','<>',$this->admin->group['id']]
        ])->order('id desc')->select();
        // dump($groups);exit;
        return View::fetch('', ['groups' => $groups, 'admin1' => $admin]);
    }
    
    public function del(){
        if($this->request->isPost()){
            try{
                AdminM::where($this->post)->find()->delete();
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'删除失败');
            }
            return $this->success('删除成功');
        }
    }
    
    public function edit_pwd()
    {
        if ($this->request->isPost()) {
            try {
                validate([
                    'new_password|新密码'       =>  'require|confirm:confirm_password'
                ])->check($this->post);
                //检测旧密码
                if ($this->admin['password'] != md5($this->post['old_password'] . $this->admin['salt'])) {
                    throw new \Exception('旧密码错误');
                }
                if ($this->admin['password']==md5($this->post['new_password'].$this->admin['salt'])){
                    throw new \Exception('旧密码不能和新密码相同');
                }
                $this->admin['salt'] = nonce_str();
                $this->admin['password'] = md5($this->post['new_password'] . $this->admin['salt']);
                $this->admin->save();
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '修改失败');
            }
            return $this->success('修改成功', '/admin/index/login', ['_blank' => 1]);
        }
        return View::fetch();
    }
    
    public function info(){
        $group = $this->admin->group;
        $rules = Rule::field('id,pid parent,title text')->order('sort asc,id asc')->select()->map(function ($v) use ($group) {
            $v['parent'] = $v['parent'] ?: '#';
            if ($group['rules'] == '*') {
                $v['state'] = [
                    'selected'      =>  true
                ];
            } else if (!Rule::where(['pid' => $v['id']])->find() && in_array($v['id'], explode(',', $group['rules']))) {
                $v['state'] = [
                    'selected'      =>  true
                ];
            } else {
                $v['state'] = [
                    'selected'      =>  false
                ];
            }
            return $v;
        });
        return View::fetch('',['rules'=>$rules]);
    }
    
    public function google_bind(){
        if($this->request->isPost()){
            try{
                validate([
                    'password|登录密码'         =>  'require',
                    'google_secret|谷歌秘钥'    =>  'require',
                    'google_code|谷歌验证码'    =>  'require',
                ])->check($this->post);
                $ga = new GoogleAuthenticator();
                if($this->admin['password']!=md5($this->post['password'].$this->admin['salt'])){
                    throw new \Exception('登录密码错误');
                }
                $checkResult = $ga->verifyCode($this->post['google_secret'], $this->post['google_code'], 2);
                if (!$checkResult) {
                    throw new \Exception('谷歌验证码错误');
                }
                $this->admin->save(['google_secret'=>$this->post['google_secret']]);
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'绑定失败');
            }
            return $this->success('绑定成功','index/index');
        }
        return View::fetch();
    }
    
    public function get_google_secret(){
        $ga = new GoogleAuthenticator();
        return $ga->createSecret();
    }
}
