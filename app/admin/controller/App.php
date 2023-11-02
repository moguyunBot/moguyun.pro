<?php
namespace app\admin\controller;

use think\facade\View;

class App extends Base{
    public function index(){
        
        return View::fetch();
    }
}