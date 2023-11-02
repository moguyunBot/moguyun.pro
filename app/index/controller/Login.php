<?php
namespace app\index\controller;

use think\facade\{View,Session};
use app\common\model\User;

class Login extends Base{
    public $noLogin = ['index','auth'];
    public function index(){
        if($this->request->isPost()){
            try{
                validate([
                    'username|用户名'       =>  'require',
                    'password|登录密码'     =>  'require',
                    'captcha|验证码'        =>  'require|captcha',
                ])->check($this->post);
                $user = User::where(['username'=>$this->post['username']])->find();
                if(!$user){
                    throw new \Exception('用户不存在');
                }
                if($user['status']!=1){
                    throw new \Exception('用户已停用');
                }
                Session::set('user_id',$user['id']);
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'登录失败');
            }
            return $this->success('登录成功'.Session::get('user_id'),'index/index');
        }
        return View::fetch();
    }
    
    public function auth(){
        if($this->request->isPost()){
            try{
                ksort($this->post);
                $str = '';
                foreach($this->post as $k=>$v){
                    if(!in_array($k,['hash']))
                    $str .= $k.'='.$v."\n";
                }
                $str = trim($str,"\n");
                $secret_key = hash_hmac('sha256', config('telegram.token'), 'WebAppData');
                $hash = hash_hmac('sha256', $str, hex2bin($secret_key));
                // 将计算出的哈希值与接收到的哈希值进行比对
                if ($this->post['hash'] !== $hash) {
                    throw new \Exception('授权失败');
                }
                $userinfo = json_decode($this->post['user'],true);
                $user = User::where(['from_id'=>$userinfo['id']])->find();
                if(!$user){
                    $user = User::create([
                        'from_id'       =>  $from['id'],
                        'nickname'      =>  ($from['first_name']??'').($from['last_name']??''),
                        'username'      =>  $from['username']??'',
                    ]);
                }
                if($user['status']!=1){
                    throw new \Exception('用户已禁用,请联系客服@bot_kf');
                }
                Session::set('user_id',$user['id']);
            }catch(\Exception $e){
                return $this->error($e->getMessage()?:'授权失败');
                // $error = "Exception: " . $e->getMessage() . "<br>";
                // $error .= "Stack Trace:<br>";
                // foreach ($e->getTrace() as $entry) {
                //     if(!empty($entry['file']))
                //     $error .= "File: " . $entry['file'] . "<br>";
                //     if(!empty($entry['line']))
                //     $error .= "Line: " . $entry['line'] . "<br>";
                //     if(!empty($entry['function']))
                //     $error .= "Function: " . $entry['function'] . "<br>";
                //     $error .= "<br>";
                // }
                // return $this->error($error?:'授权失败');
            }
            return $this->success('授权成功'.Session::get('user_id'));
        }
        return View::fetch();
    }
}