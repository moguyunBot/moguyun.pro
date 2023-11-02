<?php
namespace app\index\controller;

use think\facade\View;

class Index extends Base{
    public $noLogin = ['index'];
    public function index(){
        return View::fetch();
    }
}