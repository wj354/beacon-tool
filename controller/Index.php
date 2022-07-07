<?php


namespace tool\controller;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\DBSelector;
use beacon\core\Form;
use beacon\core\Method;
use tool\libs\MakeModel;
use tool\libs\MakeSearch;
use tool\model\AppModel;


class Index extends Base
{
    /**
     * @throws DBException
     */
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index()
    {
        if (!$this->isAjax()) {
            $this->display('list/index.tpl');
            return;
        }
        $name = $this->get('name', '');
        $sort = $this->get('sort', 'id-desc');

        $selector = new DBSelector('@pf_tool_app');
        $selector->search("`name` LIKE CONCAT('%',?,'%')", $name);
        $selector->sort($sort,['id','name']);
        $data = $selector->pageData();
        $data['list'] = $this->hookData($data['list'], 'hook/index.tpl');
        $this->success('获取数据成功', $data);
    }

    #[Method(act: 'select', method: Method::GET | Method::POST)]
    public function select()
    {
        if (!$this->isAjax()) {
            $this->display('list/index_select.tpl');
            return;
        }
        $name = $this->get('name', '');
        $sort = $this->get('sort', 'id-desc');
        $selector = new DBSelector('@pf_tool_app');
        $selector->search("`name` LIKE CONCAT('%',?,'%')", $name);
        $selector->sort($sort,['id','name']);
        $data = $selector->pageData();
        $data['list'] = $this->hookData($data['list'], 'hook/index.tpl');
        $this->success('获取数据成功', $data);
    }

    #[Method(act: 'add', method: Method::GET | Method::POST)]
    public function add()
    {
        $form = Form::create(AppModel::class, 'add');
        if ($this->isGet()) {
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        if ($input['isDefault'] == 1) {
            DB::update('@pf_tool_app', ['isDefault' => 0], 'isDefault=1');
        }
        DB::insert('@pf_tool_app', $input);
        $this->success('添加' . $form->title . '成功');
    }

    #[Method(act: 'edit', method: Method::GET | Method::POST)]
    public function edit(int $id = 0)
    {
        $form = Form::create(AppModel::class, 'edit');
        if ($this->isGet()) {
            $form->setData($id);
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        if ($input['isDefault'] == 1) {
            DB::update('@pf_tool_app', ['isDefault' => 0], 'isDefault=1');
        }
        DB::update('@pf_tool_app', $input, $id);
        DB::update('@pf_tool_form', ['namespace' => $input['namespace']], 'appId=?', $id);
        DB::update('@pf_tool_list', ['namespace' => $input['namespace']], 'appId=?', $id);
        $this->success('编辑' . $form->title . '成功');
    }

    #[Method(act: 'delete', method: Method::GET | Method::POST)]
    public function delete(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        DB::delete('@pf_tool_app', $id);
        $this->success('删除账号成功');
    }

    #[Method(act: 'check_name', method: Method::GET | Method::POST)]
    public function checkName(string $name = '')
    {
        if (AppModel::checkName($name)) {
            $this->success('项目名可以使用');
        }
        $this->error('项目名已经存在');
    }

    #[Method(act: 'make', method: Method::GET | Method::POST)]
    public function make(int $id = 0)
    {
        $formList = DB::getList('select id from @pf_tool_form where appId=?', $id);
        foreach ($formList as $item) {
            MakeModel::make($item['id']);
        }
        $listList = DB::getList('select id from @pf_tool_list where appId=?', $id);
        foreach ($listList as $item) {
            MakeSearch::make($item['id']);
        }
        $this->success('生成成功');
    }


}