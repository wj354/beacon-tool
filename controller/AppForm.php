<?php


namespace tool\controller;


use beacon\core\Logger;
use tool\libs\Helper;
use tool\libs\MakeModel;
use tool\libs\ToolDB;
use tool\model\AppFormModel;
use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\DBSelector;
use beacon\core\Form;
use beacon\core\Method;
use beacon\core\Request;
use beacon\core\Util;

class AppForm extends AppBase
{
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index()
    {
        if (!$this->isAjax()) {
            $appList = DB::getList('select * from @pf_tool_app');
            $this->assign('apps', $appList);
            $this->display('list/app_form.tpl');
            return;
        }
        $name = $this->get('name', '');
        $selector = new DBSelector('@pf_tool_form');
        if ($name) {
            if (preg_match('#@pf_(\w+)#', $name, $data)) {
                $selector->where("`tbName` LIKE CONCAT('%',?,'%')", [$data[1]]);
            } else {
                $selector->where("(`key` LIKE CONCAT('%',?,'%') or `title` LIKE CONCAT('%',?,'%'))", [$name, $name]);
            }
        }
        if ($this->appId) {
            $selector->where("appId=?", $this->appId);
        }
        $sort = $this->get('sort:s', 'updateTime-desc');
        $selector->sort($sort,['id','key','updateTime']);
        $data = $selector->pageData();
        foreach ($data['list'] as &$datum) {
            $datum['appName'] = DB::getOne('select name from @pf_tool_app where id=?', $datum['appId']);
        }
        $data['list'] = $this->hookData($data['list'], 'hook/app_form.tpl');
        $this->success('获取数据成功', $data);
    }

    /**
     * @param int $copyId
     * @param string $import
     * @throws DBException
     */
    #[Method(act: 'add', method: Method::GET | Method::POST)]
    public function add(int $copyId = 0, string $import = '')
    {
        $form = Form::create(AppFormModel::class, 'add');
        if ($this->isGet()) {
            if ($copyId) {
                $row = DB::getRow('select * from @pf_tool_form where id=?', $copyId);
                if (!$row) {
                    $this->error('表单信息不存在');
                }
                unset($row['title']);
                unset($row['key']);
                unset($row['tbName']);
                $form->setData($row);
            } elseif (!empty($import)) {
                $data = $this->getImport($import);
                $form->setData($data['form']);
            }
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $app = DB::getRow('select id,`namespace` from @pf_tool_app where id=?', $input['appId']);
        if ($app) {
            $input['namespace'] = $app['namespace'];
        } else {
            $this->error(['appId' => '不存在的项目']);
        }
        $this->appId = intval($app['id']);
        //扩展模式不需要创建表
        if ($input['extMode'] == 1) {
            $input['tbCreate'] = 0;
        }
        //继承表模式
        if ($input['extMode'] == 4) {
            $input['tbCreate'] = 0;
            $input['tbName'] = $input['tbNameEx'];
        }
        unset($input['tbNameEx']);
        //创建数据库表
        if ($input['tbCreate'] == 1) {
            try {
                if (empty($input['tbName'])) {
                    $this->error(['tbName' => '数据库表名没有填写']);
                }
                if (empty($input['tbEngine'])) {
                    $this->error(['tbEngine' => '数据库表引擎没有选择']);
                }
                $db = ToolDB::db($this->appId);
                $newTitle = (empty($input['title']) ? '' : $input['title']);
                $db->createTable('@pf_' . $input['tbName'], ['engine' => $input['tbEngine'], 'comment' => $newTitle]);
            } catch (DBException $exception) {
                Logger::error($exception->getMessage(), $exception->getTraceAsString());
                $this->error(['tbName' => '创建数据库表失败']);
            }
        }
        $input['updateTime'] = time();
        DB::insert('@pf_tool_form', $input);
        $formId = DB::lastInsertId();
        if ($copyId != 0) {
            $fieldList = DB::getList('select id from @pf_tool_field where formId=? order by sort asc', $copyId);
            foreach ($fieldList as $field) {
                $this->copyField($formId, $field['id'], $this->appId);
            }
        } else if (!empty($import)) {
            $data = $this->getImport($import);
            $fieldList = $data['fields'];
            $dbField = DB::getFields('@pf_tool_field');
            $fieldMap = [];
            foreach ($dbField as $item) {
                $fieldMap[$item['Field']] = true;
            }
            unset($fieldMap['id']);
            foreach ($fieldList as $field) {
                foreach ($field as $name => $value) {
                    if (!isset($fieldMap[$name])) {
                        unset($field[$name]);
                    }
                }
                $this->importField($formId, $field, $this->appId);
            }
            $path = Util::path(ROOT_DIR, 'runtime/temp', $import);
            if (file_exists($path)) {
                unlink($path);
            }
        } else if ($input['extMode'] == 3) {
            $fieldList = [
                [
                    'name' => 'name',
                    'tabIndex' => NULL,
                    'label' => '类型名称',
                    'boxName' => '',
                    'type' => 'Text',
                    'hidden' => '0',
                    'dbField' => '1',
                    'dbType' => 'varchar',
                    'dbLen' => '200',
                    'dbPoint' => NULL,
                    'dbComment' => '',
                    'dbDefType' => 'empty',
                    'dbDefValue' => '',
                    'dbUnique' => '0',
                    'before' => '',
                    'after' => '',
                    'sort' => '10',
                    'default' => '{"type":1,"value":""}',
                    'viewMerge' => '0',
                    'close' => '0',
                    'viewClose' => '0',
                    'offEdit' => '0',
                    'extend' => '[]',
                    'attrClass' => '',
                    'attrStyle' => '',
                    'attrPlaceholder' => '',
                    'attrs' => '[]',
                    'prompt' => '',
                    'dynamic' => '[]',
                    'names' => '[]',
                    'validRule' => '{"required":["类别名称不可为空"]}',
                    'validGroup' => '[]',
                    'validDefault' => '',
                    'validCorrect' => '',
                    'validDisplay' => '',
                    'validDisabled' => '0',
                    'star' => '0',
                    'validFunc' => '',
                ],
                [
                    'name' => 'sort',
                    'tabIndex' => NULL,
                    'label' => '排序权重',
                    'boxName' => '',
                    'type' => 'Integer',
                    'hidden' => '0',
                    'dbField' => '1',
                    'dbType' => 'int',
                    'dbLen' => '11',
                    'dbPoint' => NULL,
                    'dbComment' => '',
                    'dbDefType' => 'value',
                    'dbDefValue' => '0',
                    'dbUnique' => '0',
                    'before' => '',
                    'after' => '',
                    'sort' => '20',
                    'default' => '{"type":4,"inner":"maxSort"}',
                    'viewMerge' => '0',
                    'close' => '0',
                    'viewClose' => '0',
                    'offEdit' => '0',
                    'extend' => '[]',
                    'attrClass' => '',
                    'attrStyle' => '',
                    'attrPlaceholder' => '',
                    'attrs' => '[]',
                    'prompt' => '',
                    'dynamic' => '[]',
                    'names' => '[]',
                    'validRule' => '',
                    'validGroup' => '[]',
                    'validDefault' => '',
                    'validCorrect' => '',
                    'validDisplay' => '',
                    'validDisabled' => '0',
                    'star' => '0',
                    'validFunc' => '',
                ],
                [
                    'name' => 'allow',
                    'tabIndex' => NULL,
                    'label' => '审核状态',
                    'boxName' => '',
                    'type' => 'Check',
                    'hidden' => '0',
                    'dbField' => '1',
                    'dbType' => 'tinyint',
                    'dbLen' => '1',
                    'dbPoint' => NULL,
                    'dbComment' => '',
                    'dbDefType' => 'value',
                    'dbDefValue' => '0',
                    'dbUnique' => '0',
                    'before' => '',
                    'after' => '勾选审核启用',
                    'sort' => '30',
                    'default' => '{"type":1,"value":"1"}',
                    'viewMerge' => '0',
                    'close' => '0',
                    'viewClose' => '0',
                    'offEdit' => '0',
                    'extend' => '[]',
                    'attrClass' => '',
                    'attrStyle' => '',
                    'attrPlaceholder' => '',
                    'attrs' => '[]',
                    'prompt' => '',
                    'dynamic' => '[]',
                    'names' => '[]',
                    'validRule' => '',
                    'validGroup' => '[]',
                    'validDefault' => '',
                    'validCorrect' => '',
                    'validDisplay' => '',
                    'validDisabled' => '0',
                    'star' => '0',
                    'validFunc' => '',
                ],
            ];
            foreach ($fieldList as $field) {
                $this->importField($formId, $field, $this->appId);
            }
        }
        MakeModel::make($formId);
        $this->success('添加' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'edit', method: Method::GET | Method::POST)]
    public function edit(int $id = 0)
    {
        $row = DB::getRow('select * from @pf_tool_form where id=?', $id);
        if (!$row) {
            $this->error('表单信息不存在');
        }
        $this->appId = intval($row['appId']);
        $form = Form::create(AppFormModel::class, 'edit');
        if ($this->isGet()) {
            if (intval($row['extMode']) == 4) {
                $row['tbNameEx'] = $row['tbName'];
            }
            $form->setData($row);
            $this->displayForm($form);
            return;
        }
        $form->getField('extMode')->setValue(intval($row['extMode']));
        $input = $this->completeForm($form);
        $app = DB::getRow('select id,`namespace` from @pf_tool_app where id=?', $input['appId']);
        if ($app) {
            $input['namespace'] = $app['namespace'];
        } else {
            $this->error(['appId' => '不存在的项目']);
        }
        $this->appId = intval($app['id']);
        $db = ToolDB::db($this->appId);
        $input['updateTime'] = time();

        $input['extMode'] = $row['extMode'];
        $input['tbCreate'] = $row['tbCreate'];
        $input['tbEngine'] = empty($row['tbEngine']) ? 'InnoDB' : $row['tbEngine'];
        if ($input['extMode'] == 4) {
            $input['tbCreate'] = 0;
            unset($input['tbName']);
        }
        unset($input['tbNameEx']);
        if ($input['tbCreate'] == 1) {
            $tbName = '@pf_' . $input['tbName'];
            $oldName = '@pf_' . $row['tbName'];
            $oldTitle = $row['title'];
            $newTitle = empty($input['title']) ? '' : $input['title'];
            try {
                //如果新表不存在
                if (!$db->existsTable($tbName)) {
                    if (empty($input['tbName'])) {
                        $this->error(['tbName' => '数据库表名没有填写']);
                    }
                    if ($row['tbCreate'] == 1 && $oldName != $tbName && $db->existsTable($oldName)) {
                        #存在旧表,把旧表改名成新表
                        $db->exec('ALTER TABLE ' . $oldName . ' RENAME TO ' . $tbName . ';');
                        if ($oldTitle != $newTitle) {
                            $db->execute('ALTER TABLE ' . $tbName . ' COMMENT ?', $newTitle);
                        }
                    } else {
                        #不存在旧表，创建新表
                        $db->createTable($tbName, ['engine' => $input['tbEngine'], 'comment' => $newTitle]);
                        $fieldList = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $id);
                        foreach ($fieldList as $field) {
                            $this->addDbField($tbName, $field, $this->appId);
                        }
                    }
                } else if ($oldTitle != $newTitle) {
                    $db->execute('ALTER TABLE ' . $tbName . ' COMMENT ?', $newTitle);
                }
            } catch (\Exception $e) {
                $this->error(['tbName' => '创建数据库表失败']);
            }
        }
        DB::update('@pf_tool_form', $input, $id);
        MakeModel::make($id);
        $this->success('编辑' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'del', method: Method::GET | Method::POST)]
    public function delete(int $id = 0)
    {
        $row = DB::getRow('select * from @pf_tool_form where id=?', $id);
        if (!$row) {
            $this->error('表单信息不存在');
        }
        if ($row['extMode'] == 0 || $row['extMode'] == 2) {
            $appId = intval($row['appId']);
            $oldName = '@pf_' . $row['tbName'];
            $db = ToolDB::db($appId);
            if ($db->existsTable($oldName)) {
                $db->exec('ALTER TABLE ' . $oldName . ' RENAME TO `__' . $oldName . '`;');
            }
        }
        DB::delete('@pf_tool_form', $id);
        DB::delete('@pf_tool_field', 'formId=?', $id);
        $this->success('删除表单成功');

    }

    /**
     * @param string $name
     * @throws DBException
     */
    #[Method(act: 'check_key', method: Method::GET | Method::POST)]
    public function checkKey(string $name = '')
    {
        [$ret, $msg] = AppFormModel::validKeyFunc($name);
        if ($ret) {
            $this->success($msg);
        }
        $this->error($msg);
    }

    /**
     * 导入表单
     */
    #[Method(act: 'import', method: Method::GET)]
    public function import()
    {
        $this->display('form/form_import.tpl');
    }

    /**
     * 导出表单
     * @param int $formId
     * @throws DBException
     */
    #[Method(act: 'export', method: Method::GET)]
    public function export(int $formId)
    {
        $form = DB::getRow('select * from @pf_tool_form where id=?', $formId);
        if ($form == null) {
            $this->error('表单不存在');
        }
        $fields = DB::getList('select * from @pf_tool_field where formId=?', $formId);
        foreach ($fields as &$field) {
            unset($field['id']);
            unset($field['formId']);
        }
        $data = [];
        $data['tool'] = '4.0';
        $data['type'] = 'form';
        unset($form['id']);
        unset($form['appId']);
        $data['form'] = $form;
        $data['fields'] = $fields;
        $out = json_encode($data, JSON_UNESCAPED_UNICODE);
        $filename = '表单-' . $form['title'] . '.form';
        Request::setHeader('Content-type', 'text/plain');
        Request::setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        echo $out;
        exit;
    }

    /**
     * @param string $file
     * @return array
     */
    private function getImport(string $file): array
    {
        if (empty($file)) {
            $this->error('导入文件名不能为空');
        }
        if (!preg_match('@^[0-9]+\.form$@', $file)) {
            $this->error('导入文件名不正确');
        }
        $path = Util::path(ROOT_DIR, 'runtime/temp', $file);
        if (!file_exists($path)) {
            $this->error('导入文件名不存在');
        }
        $json = file_get_contents($path);
        if (!Util::isJson($json)) {
            unlink($path);
            $this->error('导入文件数据格式不正确');
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            unlink($path);
            $this->error('导入文件数据格式不正确');
        }
        if (empty($data['tool']) || $data['tool'] != '4.0') {
            unlink($path);
            $this->error('无效的文件数据');
        }
        if (empty($data['type']) || $data['type'] != 'form') {
            unlink($path);
            $this->error('无效的文件数据');
        }
        if (empty($data['form']) || !is_array($data['form'])) {
            unlink($path);
            $this->error('无效的文件数据');
        }
        if (empty($data['fields']) || !is_array($data['fields'])) {
            unlink($path);
            $this->error('无效的文件数据');
        }
        return $data;
    }

    /**
     * @param string $tbName
     * @param array $input
     * @param int $appId
     * @throws DBException
     */
    private function addDbField(string $tbName, array &$input, int $appId)
    {
        if ($input['names']) {
            $input['names'] = Helper::convertArray($input['names']);
            $input['extend'] = Helper::convertArray($input['extend']);
        }
        $db = ToolDB::db($appId);
        $dbType = strtolower($input['dbType']);
        if ($dbType != 'null' && $dbType != 'none' && $input['dbField'] == 1) {
            $idx = 1;
            $name = $input['name'];
            while ($db->existsField($tbName, $input['name'])) {
                $input['name'] = $name . $idx;
                $idx++;
            }
            $db->addField($tbName, $input['name'], [
                'type' => $input['dbType'],
                'len' => $input['dbLen'],
                'scale' => $input['dbPoint'],
                'comment' => empty($input['dbComment']) ? $input['label'] : $input['dbComment'],
            ]);
        }
        if (!empty($input['names'])) {
            foreach ($input['names'] as &$item) {
                $idx = 1;
                $name = $item['field'];
                while ($db->existsField($tbName, $item['field'])) {
                    $item['field'] = $name . $idx;
                    $idx++;
                }
                $option = [
                    'type' => $input['dbType'],
                    'len' => 11,
                    'scale' => 0,
                    'comment' => empty($input['dbComment']) ? $input['label'] : $input['dbComment'],
                ];
                if (!isset($item['type'])) {
                    $item['type'] = 'bool';
                }
                if ($item['type'] == 'int') {
                    $option['type'] = 'int';
                    $option['len'] = 11;
                } elseif ($item['type'] == 'bool') {
                    $option['type'] = 'tinyint';
                    $option['len'] = 1;
                } else {
                    $option['type'] = 'varchar';
                    $option['len'] = 250;
                }
                $db->addField($tbName, $item['field'], $option);
            }
            $input['extend']['names'] = json_encode($input['names'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 拷贝字段
     * @param int $formId
     * @param int $id
     * @param int $appId
     * @throws DBException
     */
    private function copyField(int $formId, int $id, int $appId)
    {
        $form = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs,appId from @pf_tool_form where id=?', $formId);
        if ($form == null) {
            return;
        }
        $values = DB::getRow('select * from @pf_tool_field where id=?', $id);
        if ($values == null) {
            return;
        }
        unset($values['id']);
        $values['sort'] = intval(DB::getMax('@pf_tool_field', 'sort', 'formId=?', $formId)) + 10;
        $values['formId'] = $formId;
        $tbName = '@pf_' . $form['tbName'];
        if ($form['tbCreate'] == 1 && $form['extMode'] != 4) {
            $this->addDbField($tbName, $values, $appId);
        }
        DB::insert('@pf_tool_field', $values);
    }

    /**
     * 导入字段
     * @param int $formId
     * @param array|null $values
     * @param int $appId
     * @throws DBException
     */
    private function importField(int $formId, array|null $values, int $appId)
    {
        if (empty($values)) {
            return;
        }
        $form = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs from @pf_tool_form where id=?', $formId);
        if ($form == null) {
            return;
        }
        unset($values['id']);
        $values['sort'] = intval(DB::getMax('@pf_tool_field', 'sort', 'formId=?', $formId)) + 10;
        $values['formId'] = $formId;
        $tbName = '@pf_' . $form['tbName'];
        if ($form['tbCreate'] == 1 && $form['extMode'] != 4) {
            $this->addDbField($tbName, $values, $appId);
        }
        DB::insert('@pf_tool_field', $values);
    }
}