<?php

namespace app\common\model;

class User extends \think\Model
{
    protected $name = 'user';
    protected $readonly = ['create_time', 'email','parent_id'];
    
    public static function onBeforeInsert($m){
        $m['invitation'] = nonce_str(10);
    }
}
