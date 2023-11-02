<?php

namespace app\admin\controller;

use app\admin\model\Group as GroupM;
use app\common\model\Rule;
use think\facade\View;

class Group extends Base
{
    public function index()
    {
        $where = [
            ['rules','<>','*'],
            ['id','<>',$this->admin->group['id']]
        ];
        if (!empty($this->get['name'])) {
            $where[] = ['name', 'like', '%' . $this->get['name'] . '%'];
        }
        
        $groups = GroupM::where($where)->order('id desc')->paginate(['list_rows' => 20, 'query' => $this->get]);
        return View::fetch('', ['groups' => $groups]);
    }

    public function add()
    {
        if ($this->request->isAjax()) {
            try {
                validate([
                    'name|用户组名称'       =>  'require|unique:group',
                ])->check($this->post);
                if(empty($this->post['rules'])){
                    $this->post['rules'] = '';
                }else{
                    $this->post['rules'] = join(',',$this->post['rules']);
                }
                GroupM::create($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '添加失败');
            }
            return $this->success('添加成功', 'index');
        }
        $menus = Rule::field('id,pid parent,title text')->where(['addon_name'=>''])->order('sort asc,id asc')->select()->map(function ($v) {
            $v['parent'] = $v['parent'] ?: '#';
            return $v;
        });
        $addon_names = Rule::where([['addon_name','<>','']])->group('addon_name')->column('addon_name');
        $addon_menus = [];
        foreach($addon_names as $addon_name){
            $addon_info = get_addons_info($addon_name);
            if(empty($addon_info))continue;
            $addon_menus[$addon_info['title']] = Rule::field('id,pid parent,title text')->where(['addon_name'=>$addon_name])->order('sort asc,id asc')->select()->map(function ($v) {
                $v['parent'] = $v['parent'] ?: '#';
                return $v;
            });
        }
        return View::fetch('', ['menus' => $menus,'addon_menus'=>$addon_menus]);
    }

    public function edit()
    {
        $group = GroupM::find($this->get['id']);
        if ($this->request->isAjax()) {
            try {
                validate([
                    'name|用户组名称'       =>  'require|unique:group',
                ])->check($this->post);
                if(empty($this->post['rules'])){
                    $this->post['rules'] = '';
                }else{
                    $this->post['rules'] = join(',',$this->post['rules']);
                }
                $group->replace()->save($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '修改失败');
            }
            return $this->success('修改成功', url('index'));
        }
        $menus = Rule::field('id,pid parent,title text')->where(['addon_name'=>''])->order('sort asc,id asc')->select()->map(function ($v) use ($group) {
            $v['parent'] = $v['parent'] ?: '#';
            if ($group['rules'] == '*') {
                $v['state'] = [
                    'selected'      =>  true
                ];
            } else if (!Rule::where(['pid' => $v['id']])->find() && in_array($v['id'], explode(',', $group['rules']))) {
                $v['state'] = [
                    'selected'      =>  true
                ];
            } else {
                $v['state'] = [
                    'selected'      =>  false
                ];
            }
            return $v;
        });
        $addon_names = Rule::where([['addon_name','<>','']])->group('addon_name')->column('addon_name');
        $addon_menus = [];
        foreach($addon_names as $addon_name){
            $addon_info = get_addons_info($addon_name);
            if(empty($addon_info))continue;
            $addon_menus[$addon_info['title']] = Rule::field('id,pid parent,title text')->where(['addon_name'=>$addon_name])->order('sort asc,id asc')->select()->map(function ($v) use ($group) {
                $v['parent'] = $v['parent'] ?: '#';
                if ($group['rules'] == '*') {
                    $v['state'] = [
                        'selected'      =>  true
                    ];
                } else if (!Rule::where(['pid' => $v['id']])->find() && in_array($v['id'], explode(',', $group['rules']))) {
                    $v['state'] = [
                        'selected'      =>  true
                    ];
                } else {
                    $v['state'] = [
                        'selected'      =>  false
                    ];
                }
                return $v;
            })->toArray();
        }
        return View::fetch('', ['menus' => $menus->toArray(), 'group' => $group,'addon_menus'=>$addon_menus]);
    }

    public function del()
    {
        if ($this->request->isAjax()) {
            try {
                GroupM::where($this->post)->find()->delete();
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '删除失败');
            }
            return $this->success('删除成功', 'index');
        }
    }
}
