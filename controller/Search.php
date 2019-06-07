<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-1
 * Time: 下午8:04
 */

namespace tool\controller;


use beacon\Config;
use beacon\DB;
use beacon\Logger;
use beacon\Request;
use beacon\Route;
use beacon\SqlSelector;
use tool\form\SearchForm;
use tool\lib\Helper;
use tool\lib\MakeSearch;

class Search extends BaseController
{
    public $listId = 0;

    private function loadListId()
    {
        $this->listId = $this->param('listId:i', 0);
        if ($this->listId == 0) {
            $this->error('缺少参数', ['back' => Route::url('~/Lists')]);
        }
        $this->assign('listId', $this->listId);
    }

    public function indexAction()
    {
        $this->loadListId();
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_search');
            $selector->where('listId=?', $this->listId);
            $name = $this->get('name', '');
            $selector->search("(`name` LIKE CONCAT('%',?,'%') or `label` LIKE CONCAT('%',?,'%'))", $name);
            $selector->search('tabIndex=?', $this->get('tabIndex', ''));
            $sort = $this->get('sort', 'sort-asc');
            switch ($sort) {
                case 'id-asc':
                    $selector->order('id asc');
                    break;
                case 'id-desc':
                    $selector->order('id desc');
                    break;
                case 'sort-asc':
                    $selector->order('sort asc');
                    break;
                case 'sort-desc':
                    $selector->order('sort desc');
                    break;
                default:
                    $selector->order('sort asc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageInfo = $plist->getInfo();
            $list = $plist->getList();
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageInfo);
            $data = $this->getAssign();
            $data['list'] = $this->hook('Search.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }
        $row = DB::getRow('select * from @pf_tool_list where id=?', $this->listId);
        $appId = $row['appId'];
        $module = DB::getOne('select `module` from @pf_tool_app where id=?', $appId);
        $row['testUrl'] = Route::url('^/' . $module . '/' . $row['key']);
        $this->assign('listRow', $row);
        $this->display('Search.tpl');
    }


    /**
     * 设置插件表单
     * @param $type string
     * @param $form \beacon\Form
     */
    private function setPlugForm($type, $form)
    {
        $field = $form->getField('extend');
        if ($field) {
            $field->plugName = Helper::getWidgetClassName($type);
            $field->regFunc('tinkerFunc', function (\beacon\Form $form) {
                foreach ($form->getFields() as $field) {
                    $field->dataValDisabled = true;
                }
            });
        }
    }

    /**
     * 添加字段
     * @param int $copyId
     * @throws \beacon\MysqlException
     */
    public function addAction(int $copyId = 0)
    {
        $this->loadListId();
        $form = new SearchForm('add');
        if ($this->isGet()) {
            if ($copyId > 0) {
                $row = DB::getRow('select * from @pf_tool_search where id=?', $copyId);
                if ($row == null) {
                    $this->error('不存在的数据');
                }
                $type = $row['type'];
                $this->setPlugForm($type, $form);
                $form->setValues($row, true);
            }
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $type = $this->post('type', '');
            $this->setPlugForm($type, $form);
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $values['listId'] = $this->listId;
            $values['names'] = null;
            if (!empty($values['extend']) && isset($values['extend']['names'])) {
                $values['names'] = json_decode($values['names'], true);
            }
            DB::insert('@pf_tool_search', $values);
            MakeSearch::make($this->listId);
            $this->success('添加' . $form->title . '成功', $values);
        }
    }

    /**
     * 编辑字段
     * @param int $id
     * @throws \beacon\MysqlException
     */
    public function editAction(int $id = 0)
    {
        $form = new SearchForm('edit');
        if ($id == 0) {
            $this->error('参数有误');
        }
        $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        $this->listId = $row['listId'];
        if ($this->isGet()) {
            $type = $row['type'];
            $this->setPlugForm($type, $form);
            $form->setValues($row);
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $type = $this->post('type', '');
            $this->setPlugForm($type, $form);
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $values['names'] = [];
            if (!empty($values['extend']) && isset($values['extend']['names'])) {
                $values['names'] = json_decode($values['names'], true);
            }
            DB::update('@pf_tool_search', $values, $id);
            MakeSearch::make($this->listId);
            $this->success('编辑' . $form->title . '成功');
        }
    }

    public function paste(int $id = 0)
    {
        $values = DB::getRow('select * from @pf_tool_search where id=?', $id);
        if ($values == null) {
            $this->error('不存在的数据');
        }
        unset($values['id']);
        $values['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $this->listId)) + 10;
        $values['listId'] = $this->listId;
        if ($values['names']) {
            $values['names'] = json_decode($values['names'], true);
            if ($values['extend']) {
                $values['extend'] = json_decode($values['extend'], true);
            } else {
                $values['extend'] = [];
            }
        }
        DB::insert('@pf_tool_search', $values);
    }

    /**
     * 黏贴字段
     * @param string $type
     * @param array $fields
     */
    public function pasteAction($type = '', array $fields = [])
    {
        $this->loadListId();
        if ($type !== 'search') {
            $this->error('字段拷贝失败');
        }
        if (empty($fields)) {
            $this->error('字段拷贝失败');
        }
        foreach ($fields as $id) {
            $this->paste($id);
        }
        MakeSearch::make($this->listId);
        $this->success('字段拷贝成功');

    }

    /**
     * 插件解析
     * @param string $type
     * @throws \Exception
     */
    public function widgetAction(string $type = '')
    {
        if ($type == '') {
            $this->error('类型不能为空');
        }
        $form = Helper::getWidgetForm($type);
        if ($form == null) {
            $this->success('', null);
        }
        $data = Request::post('extend:a', []);
        $form->fillComplete($data);
        $fields = $form->getFields();
        foreach ($fields as $name => $child) {
            $child->boxId = 'extend_' . $child->boxId;
            $child->boxName = 'extend[' . $child->boxName . ']';
        }
        $this->assign('form', $form);
        $this->fetch($form->template);
        $wrapFunc = $this->view()->getHook('single');
        if ($wrapFunc == null) {
            throw new \Exception('模板中没有找到 {hook fn="single"} 的钩子函数');
        }
        $code = $wrapFunc(['field' => null, 'form' => $form]);
        $this->success('加载成功', ['data' => $code]);
    }

    //查找表单字段对话框
    public function selectFieldAction()
    {
        $this->loadListId();
        $listRow = DB::getRow('select * from @pf_tool_list where id=?', $this->listId);
        if ($listRow == null) {
            $this->error('不存在的数据');
        }
        $formId = $listRow['formId'];
        $this->assign('formId', $formId);
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_field');
            $selector->where('formId=?', $formId);
            $name = $this->get('name', '');
            if ($name) {
                $selector->where("`name` LIKE CONCAT('%',?,'%')", $name);
            }
            $sort = $this->get('sort', 'sort-asc');
            switch ($sort) {
                case 'id-asc':
                    $selector->order('id asc');
                    break;
                case 'id-desc':
                    $selector->order('id desc');
                    break;
                case 'sort-asc':
                    $selector->order('sort asc');
                    break;
                case 'sort-desc':
                    $selector->order('sort desc');
                    break;
                default:
                    $selector->order('sort asc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageInfo = $plist->getInfo();
            $list = $plist->getList();
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageInfo);
            $data = $this->getAssign();
            $data['list'] = $this->hook('SelectField.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }
        $this->display('SelectField.tpl');
    }

    public function copyChoiceAction(array $choice = [])
    {
        $this->loadListId();
        $search_type = Config::get('tool.search_type');
        $typeMap = [];
        foreach ($search_type as $item) {
            $typeMap[$item['value']] = true;
        }
        foreach ($choice as $id) {
            $field = DB::getRow('select * from @pf_tool_field where id=?', $id);
            if ($field == null) {
                $this->error('不存在的数据');
            }
            if (!isset($typeMap[$field['type']])) {
                continue;
            }
            $values = [];
            foreach (['name', 'label', 'type', 'hideBox',
                         'beforeText', 'afterText', 'viewMerge', 'default', 'forceDefault',
                         'extend', 'custom', 'boxPlaceholder'
                         , 'boxClass'
                         , 'boxStyle'
                         , 'boxAttrs'
                         , 'names'
                     ] as $key) {
                $values[$key] = $field[$key];
            }
            $values['tabIndex'] = 'base';
            $values['varType'] = 'string';
            if ($field['dbtype'] == 'int') {
                $values['varType'] = 'int';
            } elseif ($field['dbtype'] == 'decimal' || $field['dbtype'] == 'double' || $field['dbtype'] == 'float') {
                $values['varType'] = 'float';
            } elseif ($field['dbtype'] == 'tinyint') {
                $values['varType'] = 'bool';
            }
            if ($values['varType'] == 'string') {
                $values['tbWhere'] = "`{$values['name']}` like concat('%',?,'%')";
                $values['tbWhereType'] = 2;
            } else {
                $values['tbWhere'] = "`{$values['name']}` = ?";
                $values['tbWhereType'] = 2;
            }
            $values['varType'] = 'string';
            $values['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $this->listId)) + 10;
            $values['listId'] = $this->listId;
            if ($values['type'] == 'check') {
                $values['type'] = 'select';
                $extend = Helper::convertArray($values['extend'], []);
                $extend['headerText'] = '全部';
                $values['afterText'] = '';
                $values['default'] = '';
                if ($values['name'] == 'allow') {
                    $extend['options'] = [['value' => 1, 'text' => '正常'], ['value' => 0, 'text' => '禁用']];
                }
                if ($values['name'] == 'lock') {
                    $extend['options'] = [['value' => 1, 'text' => '锁定'], ['value' => 0, 'text' => '正常']];
                }
                $values['extend'] = json_encode($extend);
            }
            DB::insert('@pf_tool_search', $values);
        }
        MakeSearch::make($this->listId);
        $this->success('拷贝成功');
    }

    private function delete($id = 0)
    {
        if ($id == 0) {
            return;
        }
        $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
        $this->listId = $row['listId'];
        DB::delete('@pf_tool_search', $id);
    }

    public function sortAction(int $id = 0, $sort = 0)
    {
        $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        $this->listId = $row['listId'];
        DB::update('@pf_tool_search', ['sort' => $sort], $id);
        MakeSearch::make($this->listId);
        $this->success('更新排序成功');
    }

    public function deleteAction(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        $this->delete($id);
        MakeSearch::make($this->listId);
        $this->success('删除字段成功');
    }

    public function deleteChoiceAction(array $choice = [])
    {
        foreach ($choice as $id) {
            $this->delete($id);
        }
        MakeSearch::make($this->listId);
        $this->success('删除选中字段成功');
    }

}