<?php
namespace app\common\model;

class Rule extends \think\Model{
    protected $name = 'rule';
    protected $readonly = ['create_time'];
    protected $json = ['options'];
    protected $jsonAssoc = true;
    
    public static function children_menus($rules,$pid=0){
        $arr = [];
        foreach($rules as $v){
            if($v['pid'] == $pid){
                $children = self::children_menus($rules,$v['id']);
                if(count($children)==0){
                    // $url = '/'.$v['app_name'].'/'.$v['uri'];
                    $params = [];
                    if(is_array($v['options'])&&count($v['options'])){
                        foreach($v['options'] as $option){
                            if($option['key']&&$option['value']){
                                $params[$option['key']] = $option['value'];
                            }
                        }
                    }
                    
                    $url = $v['addon_name']?(string)addons_url($v['addon_name'].'://'.$v['uri'],$params):(string)url($v['uri'],$params);
                    $arr[] = [
                        'title'     =>  $v['title'],
                        'icon'      =>  $v['icon'],
                        'href'      =>  $url
                    ];
                }else{
                    $arr[] = [
                        'title'     =>  $v['title'],
                        'icon'      =>  $v['icon'],
                        'href'      =>  '#',
                        'children'  =>  $children
                    ];
                }
            }
        }
        return $arr;
    }
    
    public static function init_menus($list){
        $html = '';
        foreach($list as $v){
            if(count($v['childrens'])==0){
                $html .= '<li class="nav-item">
                              <a class="multitabs" href="/'.$v['app_name'].'/'.$v['uri'].'">
                                <i class="'.$v['icon'].'"></i>
                                <span>'.$v['title'].'</span>
                              </a>
                            </li>';
            }else{
                $html .= '<li class="nav-item nav-item-has-subnav">
                              <a href="javascript:void(0)">
                                <i class="'.$v['icon'].'"></i>
                                <span>'.$v['title'].'</span>
                              </a>
                              <ul class="nav nav-subnav">
                                '.self::init_menus($v['childrens']).'
                              </ul>
                            </li>';
            }
        }
        return $html;
    }
    
    public static function childrens_title($list, $pid = 0, $level = 0)
    {
        $arr = [];
        foreach ($list as $v) {
            if ($v['pid'] == $pid) {
                $v['title'] = str_repeat('|——', $level) . $v['title'];
                $arr[] = $v;
                $arr = array_merge($arr, self::childrens_title($list, $v['id'], $level + 1));
            }
        }
        return $arr;
    }
    
    public function getOptionsAttr($v){
        return $v?:[];
    }
}