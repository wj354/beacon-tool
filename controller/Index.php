<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-26
 * Time: 上午3:29
 */

namespace tool\controller;


use beacon\DB;
use beacon\SqlSelector;
use tool\form\AppFrom;
use tool\lib\MakeController;
use tool\lib\MakeForm;
use tool\lib\MakeSearch;

class Index extends BaseController
{
    /**
     * 首页APP
     * @throws \Exception
     */
    public function indexAction()
    {
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_app');
            $name = $this->get('name', '');
            if ($name) {
                $selector->where("`name` LIKE CONCAT('%',?,'%')", [$name]);
            }
            $sort = $this->get('sort:s', '');
            switch ($sort) {
                case 'id-asc':
                    $selector->order('id asc');
                    break;
                case 'id-desc':
                    $selector->order('id desc');
                    break;
                case 'name-asc':
                    $selector->order('name asc');
                    break;
                case 'name-desc':
                    $selector->order('name desc');
                    break;
                default:
                    $selector->order('id asc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageData = $plist->getInfo();
            $list = $plist->getList();
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageData);
            $data = $this->getAssign();
            $data['list'] = $this->hook('App.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }
        $this->display('App');
    }

    public function selectAction()
    {
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_app');
            $name = $this->get('name', '');
            if ($name) {
                $selector->where("`name` LIKE CONCAT('%',?,'%')", [$name]);
            }
            $sort = $this->get('sort:s', '');
            switch ($sort) {
                case 'id-asc':
                    $selector->order('id asc');
                    break;
                case 'id-desc':
                    $selector->order('id desc');
                    break;
                case 'name-asc':
                    $selector->order('name asc');
                    break;
                case 'name-desc':
                    $selector->order('name desc');
                    break;
                default:
                    $selector->order('id asc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageData = $plist->getInfo();
            $list = $plist->getList();
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageData);
            $data = $this->getAssign();
            $data['list'] = $this->hook('App.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }
        $this->display('App.select.tpl');
    }

    public function addAction()
    {
        $form = new AppFrom('add');
        if ($this->isGet()) {
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            if ($form->getField('isDefault')->value) {
                DB::update('@pf_tool_app', ['isDefault' => false], '1');
            }
            $form->insert();
            $this->success('添加' . $form->title . '成功');
        }
    }

    public function checkNameAction(string $name = '')
    {
        $form = new AppFrom();
        $field = $form->getField('name');
        if ($field) {
            $remoteFunc = $field->getFunc('remote');
            if ($remoteFunc && $remoteFunc($name)) {
                $this->success('项目名可以使用');
            }
        }
        $this->error('项目名已经存在');
    }

    public function editAction(int $id = 0)
    {
        $form = new AppFrom('edit');
        if ($id == 0) {
            $this->error('参数有误');
        }
        $row = $form->getRow($id);
        $form->setValues($row);
        if ($this->isGet()) {
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $value = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            if ($form->getField('isDefault')->value) {
                DB::update('@pf_tool_app', ['isDefault' => false], '1');
            }
            $form->update($id);
            DB::update('@pf_tool_form', ['namespace' => $value['namespace']], 'appId=?', $id);
            DB::update('@pf_tool_list', ['namespace' => $value['namespace']], 'appId=?', $id);
            $this->success('编辑' . $form->title . '成功');
        }
    }

    public function deleteAction(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        DB::delete('@pf_tool_app', $id);
        $this->success('删除账号成功');
    }

    public function makeAction(int $id = 0)
    {
        $formList = DB::getList('select id from @pf_tool_form where appId=?', $id);
        foreach ($formList as $item) {
            MakeForm::make($item['id']);
        }
        $listList = DB::getList('select id from @pf_tool_list where appId=?', $id);
        foreach ($listList as $item) {
            MakeSearch::make($item['id']);
        }
        $this->success('生成成功');
    }
}