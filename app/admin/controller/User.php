<?php
namespace app\admin\controller;

use app\common\model\User as UserM;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;

class User extends Base{
    public $noAuth = ['get_parent','recharge','del'];
    public function index(){
        $where = [];
        if (!empty($this->get['email'])) {
            $where[] = ['email', 'like', '%' . $this->get['email'] . '%'];
        }
        if(isset($this->get['status'])&&$this->get['status']!==''){
            $where[] = ['status','=',$this->get['status']];
        }
        
        $users = UserM::where($where)->order('id desc')->paginate(['list_rows' => 20, 'query' => $this->get]);
        
        
        return View::fetch('', ['users' => $users]);
    }
    
    public function recharge(){
        
        $user = UserM::find($this->get['id']);
        if ($this->request->isAjax()) {
            Db::startTrans();
            try {
                validate([
                    'jifen|金额'      =>  'require',
                ])->check($this->post);
                $user->inc('jifen',$this->post['jifen'])->update();
                $param = [
                    'chat_id'               =>  $user['from_id'],
                    'text'                  =>  config('bot.recharge_jifen_msg').$this->post['jifen'],
                ];
                $url = 'https://api.telegram.org/bot'.config('bot.monitor_token').'/'.'sendMessage';
                $headers = ['Content-Type: application/json; charset=UTF-8'];
                curl($url,json_encode($param),$headers);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error($e->getMessage() ?: '充值成功');
            }
            return $this->success('充值成功', 'index');
        }
        return View::fetch('', ['user' => $user]);
    }
    
    public function login($id){
        // Session::set('user_id',$id);
        return $this->redirect('/');
    }
}