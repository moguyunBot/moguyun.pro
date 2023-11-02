<?php

namespace app\admin\model;

class Group extends \think\Model
{
    protected $name = 'group';
    protected $readonly = ['create_time'];

    public static function onBeforeDelete($v)
    {
        if($v['rules']=='*'){
            throw new \Exception('禁止删除超管角色');
        }
        if(Admin::where(['group_id'=>$v['id']])->find()){
            throw new \Exception('用户组存在用户,暂不能删除');
        }
    }

    public static function onBeforeUpdate($v){
        if ($v['rules'] == '*')
            throw new \Exception('超级管理员禁止修改');
    }
}
