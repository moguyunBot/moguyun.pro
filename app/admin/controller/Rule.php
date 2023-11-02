<?php

namespace app\admin\controller;

use app\common\model\Rule as RuleM;
use think\facade\View;

class Rule extends Base
{
    public function index()
    {
        $where = [
            ['addon_name','=',$this->get['addon_name']??'']
        ];
        $rules = RuleM::where($where)->order('sort asc,id asc')->select();
        $rules = RuleM::childrens_title($rules->toArray());
        return View::fetch('', ['rules' => $rules]);
    }

    public function add()
    {
        if ($this->request->isAjax()) {
            try {
                $this->post['addon_name'] = $this->get['addon_name']??'';
                validate([
                    'title|菜单标题'        =>  'require',
                ])->check($this->post);
                $rule = RuleM::create($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '添加失败');
            }
            return $this->success('添加成功', url('index',['addon_name'=>$this->get['addon_name']??'']));
        }
        $where = [
            ['status','=',1],
            ['addon_name','=',$this->get['addon_name']??'']
        ];
        $rules = RuleM::where($where)->order('sort asc,id asc')->select();
        $rules = RuleM::childrens_title($rules);
        return View::fetch('', ['rules' => $rules]);
    }

    public function edit()
    {
        $rule = RuleM::find($this->get['id']);
        if ($this->request->isAjax()) {
            try {
                $this->post['addon_name'] = $this->get['addon_name']??'';
                validate([
                    'title|菜单标题'        =>  'require',
                ])->check($this->post);
                $this->post['options'] = $this->post['options']??[];
                $rule->replace()->save($this->post);
            } catch (\Exception $e) {
                return $this->error($e->getMessage() ?: '修改失败');
            }
            return $this->success('修改成功', url('index',['addon_name'=>$this->get['addon_name']??'']));
        }
        $where = [
            ['status','=',1],
            ['id','<>',$rule['id']],
            ['addon_name','=',$this->get['addon_name']??'']
        ];
        $rules = RuleM::where($where)->order('sort asc,id asc')->select();
        $rules = RuleM::childrens_title($rules);
        return View::fetch('', ['rules' => $rules, 'menu' => $rule]);
    }

    public function del()
    {
        if ($this->request->isAjax()) {
            try {
                $rule = RuleM::find($this->post['id']);
                $rule->delete();
            } catch (\Exception $e) {
                return $this->error($e->getMessage()?:'删除失败');
            }
            return $this->success('删除成功');
        }
    }
}
