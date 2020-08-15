<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-30
 * Time: 上午8:39
 */

namespace tool\controller;


use beacon\DB;
use beacon\MysqlException;
use beacon\Request;
use beacon\Route;
use beacon\SqlSelector;
use beacon\Utils;
use tool\form\FieldForm;
use tool\lib\Helper;
use tool\lib\MakeForm;
use tool\lib\ToolDb;

class Field extends BaseController
{
    public $formId = 0;
    public $appId = 0;

    public function initialize()
    {
        parent::initialize();
        $this->appId = $this->get('appId:s', '');
        if ($this->appId === '') {
            $this->appId = DB::getOne('select id from @pf_tool_app order by isDefault desc,id desc limit 0,1');
            if ($this->appId == null) {
                $appId = 0;
            }
        }
        $this->appId = intval($this->appId);
        $this->assign('appId', $this->appId);
    }

    private function loadFormId()
    {
        $this->formId = $this->param('formId:i', 0);
        if ($this->formId == 0) {
            $this->error('缺少参数', ['back' => Route::url('~/Form')]);
        }
        $this->assign('formId', $this->formId);
    }

    public function indexAction()
    {
        $this->loadFormId();
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_field');
            $selector->where('formId=?', $this->formId);
            $selector->search("(`name` LIKE CONCAT('%',?,'%') or `label` LIKE CONCAT('%',?,'%'))", $this->get('name'));
            $sort = $this->get('sort', '');
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
            $pageInfo = ['recordsCount' => $selector->getCount()];
            $list = $selector->getList();
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageInfo);
            $data = $this->getAssign();
            $data['list'] = $this->hook('Field.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }
        $row = DB::getRow('select * from @pf_tool_form where id=?', $this->formId);
        $this->assign('formRow', $row);
        $this->display('Field.tpl');
    }

    public function checkKeyAction()
    {
        $form = new FieldForm();
        $name = $this->param('name', '');
        $remoteFunc = $form->getField('name')->getFunc('remote');
        if ($remoteFunc && $remoteFunc($name)) {
            $this->success('字段名称可以使用');
        }
        $this->error('字段名称已经存在');
    }

    /**
     * @param $type string
     * @param $form \beacon\Form
     */
    private function setPlugForm($type, $form)
    {
        $field = $form->getField('extend');
        if ($field) {
            $field->plugName = Helper::getWidgetClassName($type);
        }
    }

    public function addAction(int $copyId = 0)
    {
        $this->loadFormId();
        $form = new FieldForm('add');
        $fRow = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs,appId from @pf_tool_form where id=?', $this->formId);
        if ($fRow == null) {
            $this->error('添加失败,表单不存在');
        }
        $this->appId = intval($fRow['appId']);

        if ($fRow['viewUseTab']) {
            if (!empty($fRow['viewTabs']) && Utils::isJson($fRow['viewTabs'])) {
                $temp = json_decode($fRow['viewTabs'], 1);
                $options = [];
                foreach ($temp as $item) {
                    $options[] = [$item['key'], $item['value'] . '(' . $item['key'] . ')'];
                }
                $form->addField('tabIndex', ['label' => '选择所属Tab [tab-index]', 'type' => 'select', 'options' => $options, 'tips' => '选择所属TAB标签', 'tab-index' => 'base'], 'sort');
            }
        }

        if ($this->isGet()) {
            if ($copyId > 0) {
                $row = DB::getRow('select * from @pf_tool_field where id=?', $copyId);
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
            $values['names'] = null;
            if (!empty($values['extend'])) {
                $extend = Helper::convertArray($values['extend'], []);
                if (isset($extend['names'])) {
                    $values['names'] = Helper::convertArray($extend['names'], []);
                }
            }
            if ($values['type'] != 'hidden') {
                $values['hideBox'] = 0;
            }
            $tbName = '@pf_' . $fRow['tbName'];
            try {
                $db = ToolDb::getDb($this->appId);
                $db->beginTransaction();
                if ($fRow['tbCreate'] == 1 && ($fRow['extMode'] == 0 || $fRow['extMode'] == 2)) {
                    if ($values['dbtype'] != 'none' && $values['dbfield'] == 1) {
                        if ($db->existsField($tbName, $values['name'])) {
                            $db->rollBack();
                            $this->error(['name' => '创建字段失败,字段名已经存在']);
                        }
                        $def = null;
                        if ($values['db_def1'] == 'empty') {
                            $def = '';
                        } else if ($values['db_def1'] == 'value') {
                            if ($values['dbtype'] == 'int' || $values['dbtype'] == 'tinyint') {
                                $def = intval($values['db_def2']);
                            } else {
                                $def = $values['db_def2'];
                            }
                        }
                        $db->addField($tbName, $values['name'], [
                            'type' => $values['dbtype'],
                            'len' => $values['dblen'],
                            'scale' => $values['dbpoint'],
                            'def' => $def,
                            'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                        ]);
                    }
                    if (!empty($values['names'])) {
                        foreach ($values['names'] as $item) {
                            $option = [
                                'type' => $values['dbtype'],
                                'len' => 11,
                                'scale' => 0,
                                'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                            ];
                            if (!isset($item['type'])) {
                                $item['type'] = 'bool';
                            }
                            if ($item['type'] == 'int') {
                                $option['type'] = 'int';
                                $option['len'] = 11;
                                $option['def'] = 0;
                            } elseif ($item['type'] == 'bool') {
                                $option['type'] = 'tinyint';
                                $option['len'] = 1;
                                $option['def'] = 0;
                            } else {
                                $option['type'] = 'varchar';
                                $option['len'] = 250;
                                $option['def'] = '';
                            }
                            $db->addField($tbName, $item['field'], $option);
                        }
                    }
                }
                $db->commit();
            } catch (MysqlException $exception) {
                $db->rollBack();
                $this->error('创建字段失败');
            }
            DB::insert('@pf_tool_field', $values);
            MakeForm::make($this->formId);
            $this->success('添加' . $form->title . '成功');
        }
    }

    public function editAction(int $id = 0)
    {
        $this->loadFormId();
        $form = new FieldForm('edit');
        $fRow = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs,appId from @pf_tool_form where id=?', $this->formId);
        if ($fRow == null) {
            $this->error('编辑失败,表单不存在');
        }
        $this->appId = intval($fRow['appId']);
        if ($fRow['viewUseTab']) {
            if (!empty($fRow['viewTabs']) && Utils::isJson($fRow['viewTabs'])) {
                $temp = json_decode($fRow['viewTabs'], 1);
                $options = [];
                foreach ($temp as $item) {
                    $options[] = [$item['key'], $item['value'] . '(' . $item['key'] . ')'];
                }
                $form->addField('tabIndex', ['label' => '选择所属Tab [tab-index]', 'type' => 'select', 'options' => $options, 'tips' => '选择所属TAB标签', 'tab-index' => 'base'], 'sort');
            }
        }
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
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
            $values['names'] = null;
            if (!empty($values['extend'])) {
                $extend = Helper::convertArray($values['extend'], []);
                if (isset($extend['names'])) {
                    $values['names'] = Helper::convertArray($extend['names'], []);
                }
            }
            if ($values['type'] != 'hidden') {
                $values['hideBox'] = 0;
            }
            $tbName = '@pf_' . $fRow['tbName'];
            try {
                $db = ToolDb::getDb($this->appId);
                $db->beginTransaction();
                //如果创建表
                if ($fRow['tbCreate'] == 1 && ($fRow['extMode'] == 0 || $fRow['extMode'] == 2)) {
                    if ($values['dbtype'] != 'null' && $values['dbfield'] == 1) {
                        if ($row['name'] != $values['name']) {
                            if ($db->existsField($tbName, $values['name'])) {
                                $db->rollBack();
                                $this->error(['name' => '创建字段失败,字段名已经存在']);
                            }
                        }
                        $def = null;
                        if ($values['db_def1'] == 'empty') {
                            $def = '';
                        } else if ($values['db_def1'] == 'value') {
                            if ($values['dbtype'] == 'int' || $values['dbtype'] == 'tinyint') {
                                $def = intval($values['db_def2']);
                            } else {
                                $def = $values['db_def2'];
                            }
                        }
                        $db->updateField($tbName, $row['name'], $values['name'], [
                            'type' => $values['dbtype'],
                            'len' => $values['dblen'],
                            'scale' => $values['dbpoint'],
                            'def' => $def,
                            'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                        ]);
                    }
                    if (!empty($values['names'])) {
                        foreach ($values['names'] as $item) {
                            $option = [
                                'type' => $values['dbtype'],
                                'len' => 11,
                                'scale' => 0,
                                'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                            ];
                            if (!isset($item['type'])) {
                                $item['type'] = 'bool';
                            }
                            if ($item['type'] == 'int') {
                                $option['type'] = 'int';
                                $option['len'] = 11;
                                $option['def'] = 0;
                            } elseif ($item['type'] == 'bool') {
                                $option['type'] = 'tinyint';
                                $option['len'] = 1;
                                $option['def'] = 0;
                            } else {
                                $option['type'] = 'varchar';
                                $option['len'] = 250;
                                $option['def'] = '';
                            }
                            $db->modifyField($tbName, $item['field'], $option);
                        }
                    }
                }
                $db->commit();
            } catch (MysqlException $exception) {
                $db->rollBack();
                $this->error('修改字段失败');
            }
            DB::update('@pf_tool_field', $values, $id);
            MakeForm::make($this->formId);
            $this->success('编辑' . $form->title . '成功');
        }
    }

    public function widgetAction(string $type = '')
    {
        if ($type == '') {
            $this->error('类型不能为空');
        }
        $form = Helper::getWidgetForm($type);
        if ($form == null) {
            $this->success('');
        }
        $data = Request::post('extend:a', []);
        $form->fillComplete($data);
        $fields = $form->getFields();
        foreach ($fields as $name => $child) {
            if (!empty($child->boxName) && !isset($data[$child->boxName]) && $child->default !== null) {
                $child->value = $child->default;
            }
            $child->boxId = 'extend_' . $child->boxId;
            $child->boxName = 'extend[' . $child->boxName . ']';
        }
        $this->fetch($form->template);
        $wrapFunc = $this->view()->getHook('single');
        if ($wrapFunc == null) {
            throw new \Exception('模板中没有找到 {hook fn="single"} 的钩子函数');
        }
        $code = $wrapFunc(['field' => null, 'form' => $form]);
        $this->success('加载成功', ['data' => $code]);
    }

    public function deleteChoiceAction(array $choice = [])
    {
        foreach ($choice as $id) {
            $this->delete($id);
        }
        MakeForm::make($this->formId);
        $this->success('删除选中字段成功');
    }

    public function paste(int $id = 0)
    {
        $fRow = DB::getRow('select tbName,extMode,tbCreate,appId from @pf_tool_form where id=?', $this->formId);
        if ($fRow == null) {
            $this->error('添加失败,表单不存在');
        }
        $this->appId = intval($fRow['appId']);
        $values = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($values == null) {
            $this->error('不存在的数据');
        }
        unset($values['id']);
        $values['sort'] = intval(DB::getMax('@pf_tool_field', 'sort', 'formId=?', $this->formId)) + 10;
        $values['formId'] = $this->formId;
        if ($values['names']) {
            $values['names'] = json_decode($values['names'], true);
            if ($values['extend']) {
                $values['extend'] = json_decode($values['extend'], true);
            } else {
                $values['extend'] = [];
            }
        }
        $tbName = '@pf_' . $fRow['tbName'];
        try {
            $db = ToolDb::getDb($this->appId);
            $db->beginTransaction();
            if ($fRow['tbCreate'] == 1 && ($fRow['extMode'] == 0 || $fRow['extMode'] == 2)) {
                if ($values['dbtype'] != 'null' && $values['dbfield'] == 1) {
                    $idx = 1;
                    $name = $values['name'];
                    while ($db->existsField($tbName, $values['name'])) {
                        $values['name'] = $name . $idx;
                        $idx++;
                    }
                    $def = null;
                    if ($values['db_def1'] == 'empty') {
                        $def = '';
                    } else if ($values['db_def1'] == 'value') {
                        if ($values['dbtype'] == 'int' || $values['dbtype'] == 'tinyint') {
                            $def = intval($values['db_def2']);
                        } else {
                            $def = $values['db_def2'];
                        }
                    }
                    $db->addField($tbName, $values['name'], [
                        'type' => $values['dbtype'],
                        'len' => $values['dblen'],
                        'scale' => $values['dbpoint'],
                        'def' => $def,
                        'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                    ]);
                }
                if (!empty($values['names'])) {
                    foreach ($values['names'] as &$item) {
                        $idx = 1;
                        $name = $item['field'];
                        while ($db->existsField($tbName, $item['field'])) {
                            $item['field'] = $name . $idx;
                            $idx++;
                        }
                        $def = null;
                        if ($values['db_def1'] == 'empty') {
                            $def = '';
                        } else if ($values['db_def1'] == 'value') {
                            if ($values['dbtype'] == 'int' || $values['dbtype'] == 'tinyint') {
                                $def = intval($values['db_def2']);
                            } else {
                                $def = $values['db_def2'];
                            }
                        }
                        $option = [
                            'type' => $values['dbtype'],
                            'len' => 11,
                            'scale' => 0,
                            'def' => $def,
                            'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                        ];
                        if (!isset($item['type'])) {
                            $item['type'] = 'bool';
                        }
                        if ($item['type'] == 'int') {
                            $option['type'] = 'int';
                            $option['len'] = 11;
                            $option['def'] = 0;
                        } elseif ($item['type'] == 'bool') {
                            $option['type'] = 'tinyint';
                            $option['len'] = 1;
                            $option['def'] = 0;
                        } else {
                            $option['type'] = 'varchar';
                            $option['len'] = 250;
                            $option['def'] = '';
                        }
                        $db->addField($tbName, $item['field'], $option);
                    }
                    $values['extend']['names'] = json_encode($values['names'], JSON_UNESCAPED_UNICODE);
                }
            }
            $db->commit();
        } catch (MysqlException $exception) {
            $db->rollBack();
            $this->error('字段拷贝失败');
        }
        MakeForm::make($this->formId);
        DB::insert('@pf_tool_field', $values);
    }

    public function pasteAction($type = '', array $fields = [])
    {
        $this->loadFormId();
        if ($type !== 'field') {
            $this->error('字段拷贝失败');
        }
        if (empty($fields)) {
            $this->error('字段拷贝失败');
        }
        foreach ($fields as $id) {
            $this->paste($id);
        }
        MakeForm::make($this->formId);
        $this->success('字段拷贝成功');

    }

    private function delete($id = 0)
    {
        if ($id == 0) {
            return;
        }
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        $this->formId = $row['formId'];
        $fRow = DB::getRow('select tbName,extMode,tbCreate,appId from @pf_tool_form where id=?', $row['formId']);
        //删除字段
        if ($fRow != null && $fRow['tbCreate'] == 1 && ($fRow['extMode'] == 0 || $fRow['extMode'] == 2)) {
            $this->appId = intval($fRow['appId']);
            $db = ToolDb::getDb($this->appId);
            $tbName = '@pf_' . $fRow['tbName'];
            if ($row['names']) {
                $row['names'] = json_decode($row['names'], true);
                foreach ($row['names'] as $item) {
                    $field = isset($item['field']) ? $item['field'] : '';
                    if (!empty($field) && $db->existsField($tbName, $field)) {
                        $db->dropField($tbName, $field);
                    }
                }
            }
            if ($db->existsField($tbName, $row['name'])) {
                $db->dropField($tbName, $row['name']);
            }
        }
        DB::delete('@pf_tool_field', $id);
    }

    public function deleteAction(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        $this->delete($id);
        if ($this->formId) {
            MakeForm::make($this->formId);
        }
        $this->success('删除字段成功');
    }

    /**
     * 编辑单个标题
     * @param string $label
     */
    public function editFieldAction(int $id = 0, string $label = '')
    {
        $this->formId = $this->param('formId:i', 0);
        DB::update('@pf_tool_field', ['label' => $label], $id);
        MakeForm::make($this->formId);
        $this->success('修改标签成功');
    }

    public function sortAction(int $id = 0, $sort = 0)
    {
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        $this->formId = $row['formId'];
        DB::update('@pf_tool_field', ['sort' => $sort], $id);
        MakeForm::make($this->formId);
        $this->success('更新排序成功');
    }

    public function mdfieldAction(int $formId = 0, string $ofield = '')
    {
        $select = explode(',', $ofield);
        $selectMap = [];
        foreach ($select as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $selectMap[$item] = 1;
            }
        }
        $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $formId);
        if ($fRow == null) {
            $this->error('不存在模型');
        }
        $list = DB::getList('select id,`name`,`label`,`type`,`dbtype` from @pf_tool_field where formId=?', $formId);
        foreach ($list as &$item) {
            $name = $item['name'];
            if (isset($selectMap[$name])) {
                $item['checked'] = true;
            } else {
                $item['checked'] = false;
            }
        }
        $this->assign('list', $list);
        $this->display('Field.mdfield.tpl');
    }
}