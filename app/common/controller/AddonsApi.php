<?php
namespace app\common\controller;

use think\facade\{Session};

class AddonsApi extends \think\Addons{
    public $post = [];
    public $get = [];
    public $param = [];
    use \think\Jump;
    public function initialize(){
        parent::initialize();
        if(is_file($this->addon_path.'vendor/autoload.php'))
        include($this->addon_path.'vendor/autoload.php');
        $this->post = $this->request->post();
        $this->get = $this->request->get();
        $this->param = $this->request->param();
    }
    
    public function install(){}
    
    public function uninstall(){}
}