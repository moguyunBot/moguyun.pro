<?php
namespace app\common\controller;

use think\facade\{Session};
use app\admin\model\Admin;

class AddonsApi extends \think\Addons{
    public $post = [];
    public $get = [];
    public $param = [];
    use \think\Jump;
    public function initialize(){
        parent::initialize();
        $this->post = $this->request->post();
        $this->get = $this->request->get();
        $this->param = $this->request->param();
    }
    
    public function install(){}
    
    public function uninstall(){}
}