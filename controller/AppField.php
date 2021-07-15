<?php


namespace tool\controller;


use beacon\core\App;
use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\DBSelector;
use beacon\core\Form;
use beacon\core\Method;
use beacon\core\View;
use beacon\widget\Single;
use sdopx\SdopxException;
use tool\libs\Helper;
use tool\libs\MakeModel;
use tool\libs\ToolDB;
use tool\model\AppFieldModel;

/**
 * Class AppField
 * @property array $form
 * @property int $formId
 * @package tool\controller
 */
class AppField extends AppBase
{
    private int $_formId = 0;
    private ?array $_form = null;

    public function __get(string $name)
    {
        if ($name == 'formId') {
            if ($this->_formId == 0) {
                $this->_formId = $this->param('formId:i', 0);
                if ($this->_formId == 0) {
                    $this->error('缺少参数', ['back' => App::url('~/AppForm')]);
                }
            }
            return $this->_formId;
        }
        if ($name == 'form') {
            if ($this->_form === null) {
                $this->_form = DB::getRow('select * from @pf_tool_form where id=?', $this->formId);
                if ($this->_form == null) {
                    $this->error('添加失败,表单不存在');
                }
            }
            return $this->_form;
        }
    }

    /**
     * @throws DBException
     */
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index()
    {
        if (!$this->isAjax()) {
            $this->assign('formRow', $this->form);
            $this->display('list/field.tpl');
            return;
        }
        $selector = new DBSelector('@pf_tool_field');
        $selector->where('formId=?', $this->formId);
        $selector->search("(`name` LIKE CONCAT('%',?,'%') or `label` LIKE CONCAT('%',?,'%'))", $this->get('name'));
        $sort = $this->get('sort:s', 'sort-asc');
        $selector->sort($sort);
        $data = [];
        $data['pageInfo'] = ['recordsCount' => $selector->getCount()];
        $data['list'] = $selector->getList();
        $data['list'] = $this->hookData($data['list'], 'hook/field.tpl');
        $this->success('获取数据成功', $data);
    }

    /**
     * @param string $type
     * @return Form
     */
    private function getForm(string $type): Form
    {
        $form = Form::create(AppFieldModel::class, $type);
        if (intval($this->form['viewUseTab']) == 1) {
            $temp = Helper::convertArray($this->form['viewTabs']);
            if (count($temp) > 0) {
                $options = [];
                foreach ($temp as $item) {
                    $options[] = [$item['key'], $item['value'] . '(' . $item['key'] . ')'];
                }
                $field = $form->getField('tabIndex');
                $field->options = $options;
                $field->close = false;
            }
        }
        if ($this->isPost()) {
            $name = $this->post('type:s', '');
            $this->setSupport($form, $name);
        }
        return $form;
    }

    private function setSupport(Form $form, string $name)
    {
        $field = $form->getField('extend');
        if ($field) {
            $field->itemClass = Helper::getSupportClassName($name);
        }
    }

    /**
     * @param array $input
     */
    private function fixInput(array &$input)
    {
        $input['names'] = [];
        $input['validGroup'] = [];
        if (!empty($input['extend'])) {
            $extend = $input['extend'];
            if (isset($extend['names'])) {
                $input['names'] = Helper::convertArray($extend['names']);
            }
            if (isset($extend['validGroup'])) {
                $input['validGroup'] = Helper::convertArray($extend['validGroup']);
            }
        }
        if ($input['type'] != 'Hidden') {
            $input['hidden'] = false;
        }
    }

    /**
     * @param $input
     * @param $item
     * @return array
     */
    private function getNameOption($input, $item): array
    {
        $option = [
            'type' => $input['dbType'],
            'len' => 11,
            'scale' => 0,
            'comment' => empty($input['dbComment']) ? $input['label'] : $input['dbComment'],
        ];
        if (!isset($item['type'])) {
            $item['type'] = 'int';
        }
        switch ($item['type']) {
            case 'varchar':
                $option['type'] = 'varchar';
                $option['len'] = 200;
                $option['def'] = '';
                break;
            case 'tinyint':
                $option['type'] = 'tinyint';
                $option['len'] = 1;
                $option['def'] = 0;
                break;
            case 'decimal':
                $option['type'] = 'decimal';
                $option['len'] = 18;
                $option['def'] = 0;
                $option['scale'] = 2;
                break;
            default:
                $option['type'] = 'int';
                $option['len'] = 11;
                $option['def'] = 0;
                break;
        }
        return $option;
    }

    /**
     * @param $input
     * @return array
     */
    private function getDbOption($input): array
    {
        $def = null;
        if ($input['dbDefType'] == 'empty') {
            $def = '';
        } else if ($input['dbDefType'] == 'value') {
            if ($input['dbType'] == 'int' || $input['dbType'] == 'tinyint') {
                $def = intval($input['dbDefValue']);
            } else {
                $def = $input['dbDefValue'];
            }
        }
        return [
            'type' => $input['dbType'],
            'len' => $input['dbLen'],
            'scale' => $input['dbPoint'],
            'def' => $def,
            'comment' => empty($input['dbComment']) ? $input['label'] : $input['dbComment'],
        ];
    }

    /**
     * @param int $copyId
     * @throws DBException
     */
    #[Method(act: 'add', method: Method::GET | Method::POST)]
    public function add(int $copyId = 0)
    {
        $this->appId = intval($this->form['appId']);
        $form = $this->getForm('add');
        if ($this->isGet()) {
            if ($copyId > 0) {
                $row = DB::getRow('select * from @pf_tool_field where id=?', $copyId);
                if ($row == null) {
                    $this->error('不存在的数据');
                }
                $name = $row['type'];
                $this->setSupport($form, $name);
                $form->setData($row);
            }
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $this->fixInput($input);
        DB::insert('@pf_tool_field', $input);
        //创建数据表字段
        if ($this->form['tbCreate'] == 1 && ($this->form['extMode'] == 0 || $this->form['extMode'] == 2 || $this->form['extMode'] == 3)) {
            $tbName = '@pf_' . $this->form['tbName'];
            $db = ToolDB::db($this->appId);
            if ($input['dbType'] != 'none' && $input['dbField'] == true) {
                if ($db->existsField($tbName, $input['name'])) {
                    $this->error(['name' => '创建字段失败,字段名已经存在']);
                }
                $option = $this->getDbOption($input);
                $db->addField($tbName, $input['name'], $option);
            }
            //创建拆分的字段
            if (!empty($input['names'])) {
                foreach ($input['names'] as $item) {
                    if ($db->existsField($tbName, $item['field'])) {
                        continue;
                    }
                    $option = $this->getNameOption($input, $item);
                    $db->addField($tbName, $item['field'], $option);
                }
            }
        }
        MakeModel::make($this->formId);
        $this->success('添加' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'edit', method: Method::GET | Method::POST)]
    public function edit(int $id = 0)
    {
        $this->appId = intval($this->form['appId']);
        $form = $this->getForm('edit');
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        if ($this->isGet()) {
            $name = $row['type'];
            $this->setSupport($form, $name);
            $form->setData($row);
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $this->fixInput($input);
        DB::update('@pf_tool_field', $input, $id);

        if ($this->form['tbCreate'] == 1 && ($this->form['extMode'] == 0 || $this->form['extMode'] == 2 || $this->form['extMode'] == 3)) {
            $tbName = '@pf_' . $this->form['tbName'];
            $db = ToolDB::db($this->appId);
            if ($input['dbType'] != 'none' && $input['dbField'] == true) {
                //不存在才需要检查
                if ($row['name'] != $input['name']) {
                    if ($db->existsField($tbName, $input['name'])) {
                        $this->error(['name' => '创建字段失败,字段名已经存在']);
                    }
                }
                $option = $this->getDbOption($input);
                $db->updateField($tbName, $row['name'], $input['name'], $option);
            }
            //创建拆分的字段
            if (!empty($input['names'])) {
                foreach ($input['names'] as $item) {
                    if ($db->existsField($tbName, $item['field'])) {
                        continue;
                    }
                    $option = $this->getNameOption($input, $item);
                    $db->modifyField($tbName, $item['field'], $option);
                }
            }
        }
        MakeModel::make($this->formId);
        $this->success('编辑' . $form->title . '成功');

    }

    /**
     * @param int $id
     * @throws DBException
     */
    private function remove($id = 0)
    {
        if ($id == 0) {
            return;
        }
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        //删除字段
        if ($this->form['tbCreate'] == 1 && ($this->form['extMode'] == 0 || $this->form['extMode'] == 2 || $this->form['extMode'] == 3)) {
            $this->appId = intval($this->form['appId']);
            $db = ToolDB::db($this->appId);
            $tbName = '@pf_' . $this->form['tbName'];
            if ($row['names']) {
                $row['names'] = Helper::convertArray($row['names']);
                foreach ($row['names'] as $item) {
                    $field = $item['field'] ?? '';
                    if (!empty($field)) {
                        $db->dropField($tbName, $field);
                    }
                }
            }
            $db->dropField($tbName, $row['name']);
        }
        DB::delete('@pf_tool_field', $id);
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'delete', method: Method::GET | Method::POST)]
    public function delete(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        $this->remove($id);
        if ($this->formId) {
            MakeModel::make($this->formId);
        }
        $this->success('删除字段成功');
    }

    /**
     * @param array $choice
     * @throws DBException
     */
    #[Method(act: 'delete_choice', method: Method::GET | Method::POST)]
    public function deleteChoice(array $choice = [])
    {
        foreach ($choice as $id) {
            $this->remove($id);
        }
        MakeModel::make($this->formId);
        $this->success('删除选中字段成功');
    }

    #[Method(act: 'edit_field', method: Method::GET | Method::POST)]
    public function editField(int $id = 0, string $label = '')
    {
        DB::update('@pf_tool_field', ['label' => $label], $id);
        MakeModel::make($this->formId);
        $this->success('修改标签成功');
    }

    #[Method(act: 'sort', method: Method::GET | Method::POST)]
    public function sort(int $id = 0, $sort = 0)
    {
        $row = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        $this->_formId = $row['formId'];
        DB::update('@pf_tool_field', ['sort' => $sort], $id);
        MakeModel::make($this->formId);
        $this->success('更新排序成功');
    }

    /**
     * @param string $name
     * @throws DBException
     */
    #[Method(act: 'check_name', method: Method::GET | Method::POST)]
    public function checkName(string $name = '')
    {
        [$ret, $msg] = AppFieldModel::nameValidFunc($name);
        if ($ret) {
            $this->success($msg);
        }
        $this->error($msg);
    }

    /**
     * @param int $formId
     * @param string $choice
     * @throws DBException
     */
    #[Method(act: 'table', method: Method::GET | Method::POST)]
    public function table(int $formId = 0, string $choice = '')
    {
        $select = explode(',', $choice);
        $selectMap = [];
        foreach ($select as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $selectMap[$item] = 1;
            }
        }
        $list = DB::getList('select id,`name`,`label`,`type`,`dbType` from @pf_tool_field where formId=?', $formId);
        foreach ($list as &$item) {
            $name = $item['name'];
            if (isset($selectMap[$name])) {
                $item['checked'] = true;
            } else {
                $item['checked'] = false;
            }
        }
        $this->assign('list', $list);
        $this->display('list/field.table.tpl');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    private function pasteField(int $id = 0)
    {
        $this->appId = intval($this->form['appId']);
        $input = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($input == null) {
            $this->error('不存在的数据');
        }
        unset($input['id']);
        $input['sort'] = intval(DB::getMax('@pf_tool_field', 'sort', 'formId=?', $this->formId)) + 10;
        $input['formId'] = $this->formId;
        $input['extend'] = Helper::convertArray($input['extend']);
        $input['names'] = Helper::convertArray($input['names']);
        $tbName = '@pf_' . $this->form['tbName'];
        if ($this->form['tbCreate'] == 1 && ($this->form['extMode'] == 0 || $this->form['extMode'] == 2 || $this->form['extMode'] == 3)) {
            $db = ToolDB::db($this->appId);
            if ($input['dbType'] != 'null' && $input['dbField'] == 1) {
                $idx = 1;
                $name = $input['name'];
                while ($db->existsField($tbName, $input['name'])) {
                    $input['name'] = $name . $idx;
                    $idx++;
                }
                $option = $this->getDbOption($input);
                $db->addField($tbName, $input['name'], $option);
            }
            if (!empty($input['names'])) {
                foreach ($input['names'] as &$item) {
                    $idx = 1;
                    $name = $item['field'];
                    while ($db->existsField($tbName, $item['field'])) {
                        $item['field'] = $name . $idx;
                        $idx++;
                    }
                    $option = $this->getNameOption($input, $item);
                    $db->addField($tbName, $item['field'], $option);
                }
                $input['extend']['names'] = $input['names'];
            }
        }
        DB::insert('@pf_tool_field', $input);
    }

    /**
     * @param string $type
     * @param array $fields
     * @throws DBException
     */
    #[Method(act: 'paste', method: Method::GET | Method::POST)]
    public function paste(string $type = '', array $fields = [])
    {
        if ($type !== 'field') {
            $this->error('字段拷贝失败');
        }
        if (empty($fields)) {
            $this->error('字段拷贝失败');
        }
        foreach ($fields as $id) {
            $this->pasteField($id);
        }
        MakeModel::make($this->formId);
        $this->success('字段拷贝成功');
    }

    /**
     * @param string $type
     * @throws SdopxException
     */
    #[Method(act: 'support', method: Method::GET | Method::POST)]
    public function support(string $type = '')
    {
        if ($type == '') {
            $this->error('类型不能为空');
        }
        $form = Helper::getSupportForm($type, 'add');
        if ($form == null) {
            $this->success('', ['data' => '']);
        }
        $data = $this->post('extend:a', []);
        $form->setData($data);
        $fields = $form->getFields();
        foreach ($fields as $child) {
            if (!empty($child->boxName) && !isset($data[$child->boxName]) && $child->default !== null) {
                $child->setValue($child->default);
            }
        }
        Single::perfect($fields, 'extend');

        $viewer = new View();
        $viewer->assign('field', $this);
        $viewer->assign('form', $form);
        if (empty($form->template)) {
            throw new \Exception('子表单获模板未定义');
        }
        $code = $viewer->fetch($form->template);
        $this->success('加载成功', ['data' => $code]);
    }

}