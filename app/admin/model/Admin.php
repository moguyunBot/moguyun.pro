<?php
namespace app\admin\model;

class Admin extends \think\Model{
    protected $name = 'admin';
    protected $readonly = ['create_time','username'];
    
    public function group(){
        return $this->belongsTo(Group::class,'group_id','id');
    }
    
    public static function onBeforeDelete($m){
        if($m['group']['rules']=='*'){
            throw new \Exception('超级管理员不能删除');
        }
    }
}