<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-27
 * Time: 下午10:05
 */

namespace tool\controller;


use beacon\DB;
use beacon\MysqlException;
use beacon\SqlSelector;
use beacon\Utils;
use tool\form\FormForm;
use tool\lib\Helper;
use tool\lib\MakeForm;

class Form extends BaseController
{


    public function indexAction()
    {
        $appId = $this->get('appId:s', '');
        if ($appId === '') {
            $appId = DB::getOne('select id from @pf_tool_app order by isDefault desc,id desc limit 0,1');
            if ($appId == null) {
                $appId = 0;
            }
        }
        $appId = intval($appId);
        $this->assign('appId', $appId);
        if ($this->isAjax()) {
            $selector = new SqlSelector('@pf_tool_form');
            $name = $this->get('name', '');
            if ($name) {
                $selector->where("(`key` LIKE CONCAT('%',?,'%') or `title` LIKE CONCAT('%',?,'%'))", [$name, $name]);
            }
            if ($appId) {
                $selector->where("appId=?", $appId);
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
                case 'key-asc':
                    $selector->order('`key` asc');
                    break;
                case 'key-desc':
                    $selector->order('`key` desc');
                    break;
                default:
                    $selector->order('updateTime desc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageData = $plist->getInfo();
            $data = [];
            $data['list'] = $plist->getList();
            $data['pageInfo'] = $pageData;
            foreach ($data['list'] as &$datum) {
                $datum['appName'] = DB::getOne('select name from @pf_tool_app where id=?', $datum['appId']);
            }
            $data['list'] = $this->hook('Form.hook.tpl', $data['list'], ['appName']);
            $this->success('获取数据成功', $data);
        }
        $appList = DB::getList('select * from @pf_tool_app');
        $this->assign('appId', $appId);
        $this->assign('applist', $appList);
        $this->display('Form');
    }

    public function checkKeyAction()
    {
        $form = new FormForm();
        $username = $this->param('key', '');
        $validFunc = $form->getField('key')->getFunc('valid');
        if ($validFunc && $validFunc($username) === null) {
            $this->success('表单标识可以使用');
        }
        $this->error('表单标识已经存在');
    }

    private function getImport($file)
    {
        if (empty($file)) {
            $this->error('导入文件名不能为空');
        }
        if (!preg_match('@^[0-9]+\.form$@', $file)) {
            $this->error('导入文件名不正确');
        }
        $path = Utils::path(ROOT_DIR, 'runtime/temp', $file);
        if (!file_exists($path)) {
            $this->error('导入文件名不存在');
        }
        $json = file_get_contents($path);
        if (!Utils::isJson($json)) {
            unlink($path);
            $this->error('导入文件数据格式不正确');
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            unlink($path);
            $this->error('导入文件数据格式不正确');
        }
        if (empty($data['tool']) || $data['tool'] != '2.1') {
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

    public function addImportAction(string $import = '')
    {
        $data = $this->getImport($import);
        $form = new FormForm('add');
        if ($this->isGet()) {
            $form->setValues($data['form'], true);
            $this->displayForm($form);
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select `namespace` from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['proId' => '不存在的项目']);
            }
            //扩展模式不需要创建表
            if ($values['extMode'] == 1) {
                $values['tbCreate'] = false;
            }
            if ($values['extMode'] == 4) {
                $values['tbCreate'] = false;
                $values['tbName'] = $form->getField('tbNameEx')->value;
            }
            //创建表
            if ($values['tbCreate']) {
                try {
                    if (empty($values['tbName'])) {
                        $this->error(['tbName' => '数据库表名没有填写']);
                    }
                    if (empty($values['tbEngine'])) {
                        $this->error(['tbEngine' => '数据库表引擎没有选择']);
                    }
                    DB::createTable('@pf_' . $values['tbName'], ['engine' => $values['tbEngine']]);
                } catch (MysqlException $exception) {
                    $this->error(['tbName' => '创建数据库表失败']);
                }
            }
            $values['updateTime'] = time();
            DB::insert('@pf_tool_form', $values);
            $id = DB::lastInsertId();
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
                $this->importField($id, $field);
            }
            MakeForm::make($id);
            $path = Utils::path(ROOT_DIR, 'runtime/temp', $import);
            if (file_exists($path)) {
                unlink($path);
            }
            $this->success('添加' . $form->title . '成功');
        }
    }

    /**
     * 添加表单
     * @param int $copyId
     * @throws MysqlException
     */
    public function addAction(int $copyId = 0)
    {
        $form = new FormForm('add');
        if ($this->isGet()) {
            if ($copyId) {
                $row = DB::getRow('select * from @pf_tool_form where id=?', $copyId);
                if (!$row) {
                    $this->error('表单信息不存在');
                }
                unset($row['title']);
                unset($row['key']);
                unset($row['tbName']);
                $form->setValues($row, true);
            }
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select `namespace` from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['proId' => '不存在的项目']);
            }
            //扩展模式不需要创建表
            if ($values['extMode'] == 1) {
                $values['tbCreate'] = false;
            }
            //继承表模式
            if ($values['extMode'] == 4) {
                $values['tbCreate'] = false;
                $values['tbName'] = $form->getField('tbNameEx')->value;
            }
            //创建表
            if ($values['tbCreate']) {
                try {
                    if (empty($values['tbName'])) {
                        $this->error(['tbName' => '数据库表名没有填写']);
                    }
                    if (empty($values['tbEngine'])) {
                        $this->error(['tbEngine' => '数据库表引擎没有选择']);
                    }
                    DB::createTable('@pf_' . $values['tbName'], ['engine' => $values['tbEngine']]);
                } catch (MysqlException $exception) {
                    $this->error(['tbName' => '创建数据库表失败']);
                }
            }
            $values['updateTime'] = time();
            DB::insert('@pf_tool_form', $values);
            $id = DB::lastInsertId();
            if ($copyId != 0) {
                $fieldList = DB::getList('select id from @pf_tool_field where formId=? order by sort asc', $copyId);
                foreach ($fieldList as $field) {
                    $this->copyField($id, $field['id']);
                }
            }
            MakeForm::make($id);
            $this->success('添加' . $form->title . '成功');
        }
    }

    /**
     * 编辑表单
     * @param int $id
     * @throws MysqlException
     */
    public function editAction(int $id = 0)
    {
        $row = DB::getRow('select * from @pf_tool_form where id=?', $id);
        if (!$row) {
            $this->error('表单信息不存在');
        }
        $form = new FormForm('edit');
        if ($this->isGet()) {
            $row['tbNameEx'] = $row['tbName'];
            $form->setValues($row);
            $this->displayForm($form);
            return;
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select `namespace` from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['proId' => '不存在的项目']);
            }
            $values['updateTime'] = time();
            if ($row['extMode'] != 1) {
                if ($row['extMode'] == 4) {
                    $values['tbCreate'] = false;
                    $values['tbName'] = $form->getField('tbNameEx')->value;
                }
                $tbName = '@pf_' . $values['tbName'];
                $oldName = '@pf_' . $row['tbName'];
                if ($row['tbCreate']) {
                    try {
                        if (empty($row['tbEngine'])) {
                            $this->error(['tbEngine' => '数据库表引擎没有选择']);
                        }
                        if (empty($values['tbName'])) {
                            $this->error(['tbName' => '数据库表名没有填写']);
                        }

                        if ($oldName != $tbName) {
                            #存在旧表
                            if (DB::existsTable($oldName)) {
                                DB::exec('ALTER TABLE ' . $oldName . ' RENAME TO ' . $tbName . ';');
                            } else {
                                #不存在旧表
                                DB::createTable($tbName, ['engine' => $row['tbEngine']]);
                                $fieldList = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $id);
                                foreach ($fieldList as $field) {
                                    $this->addDbField($tbName, $field);
                                }
                            }
                        } else {
                            #新表不存在
                            if (!DB::existsTable($tbName)) {
                                DB::createTable($tbName, ['engine' => $row['tbEngine']]);
                                $fieldList = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $id);
                                foreach ($fieldList as $field) {
                                    $this->addDbField($tbName, $field);
                                }
                            }
                        }
                    } catch (MysqlException $exception) {
                        $this->error(['tbName' => '创建数据库表失败']);
                    }
                }
            }
            DB::update('@pf_tool_form', $values, $id);
            MakeForm::make($id);
            $this->success('编辑' . $form->title . '成功');
        }
    }

    public function delAction(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        $row = DB::getRow('select * from @pf_tool_form where id=?', $id);
        if ($row == null) {
            $this->error('表单不存在');
        }
        if ($row['extMode'] == 0 || $row['extMode'] == 2) {
            $oldName = '@pf_' . $row['tbName'];
            if (DB::existsTable($oldName)) {
                DB::exec('ALTER TABLE ' . $oldName . ' RENAME TO `__' . $oldName . '`;');
            }
        }
        DB::delete('@pf_tool_form', $id);
        DB::delete('@pf_tool_field', 'formId=?', $id);
        $this->success('删除表单成功');
    }

    private function addDbField($tbName, array &$values)
    {
        if ($values['names']) {
            $values['names'] = Helper::convertArray($values['names']);
            $values['extend'] = Helper::convertArray($values['extend'], []);
        }
        try {
            DB::beginTransaction();
            if ($values['dbtype'] != 'null' && $values['dbfield'] == 1) {
                $idx = 1;
                $name = $values['name'];
                while (DB::existsField($tbName, $values['name'])) {
                    $values['name'] = $name . $idx;
                    $idx++;
                }
                DB::addField($tbName, $values['name'], [
                    'type' => $values['dbtype'],
                    'len' => $values['dblen'],
                    'scale' => $values['dbpoint'],
                    'comment' => empty($values['dbcomment']) ? $values['label'] : $values['dbcomment'],
                ]);
            }
            if (!empty($values['names'])) {
                foreach ($values['names'] as &$item) {
                    $idx = 1;
                    $name = $item['field'];
                    while (DB::existsField($tbName, $item['field'])) {
                        $item['field'] = $name . $idx;
                        $idx++;
                    }
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
                    } elseif ($item['type'] == 'bool') {
                        $option['type'] = 'tinyint';
                        $option['len'] = 1;
                    } else {
                        $option['type'] = 'varchar';
                        $option['len'] = 250;
                    }
                    DB::addField($tbName, $item['field'], $option);
                }
                $values['extend']['names'] = json_encode($values['names'], JSON_UNESCAPED_UNICODE);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return;
        }
    }

    public function copyField(int $formId, int $id)
    {
        $form = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs from @pf_tool_form where id=?', $formId);
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
            $this->addDbField($tbName, $values);
        }
        DB::insert('@pf_tool_field', $values);
    }

    //导入节点
    public function importField(int $formId, $values)
    {
        $form = DB::getRow('select tbName,extMode,tbCreate,viewUseTab,viewTabs from @pf_tool_form where id=?', $formId);
        if ($form == null) {
            return;
        }
        if ($values == null) {
            return;
        }
        unset($values['id']);
        $values['sort'] = intval(DB::getMax('@pf_tool_field', 'sort', 'formId=?', $formId)) + 10;
        $values['formId'] = $formId;
        $tbName = '@pf_' . $form['tbName'];
        if ($form['tbCreate'] == 1 && $form['extMode'] != 4) {
            $this->addDbField($tbName, $values);
        }
        DB::insert('@pf_tool_field', $values);
    }

    //导出表单
    public function exportAction(int $formId)
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
        $data['tool'] = '2.1';
        $data['type'] = 'form';
        unset($form['id']);
        unset($form['appId']);
        $data['form'] = $form;
        $data['fields'] = $fields;
        $out = json_encode($data, JSON_UNESCAPED_UNICODE);
        $filename = '表单-' . $form['title'] . '.form';
        $this->setHeader('Content-type', 'text/plain');
        $this->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        echo $out;
        exit;
    }

    public function importAction()
    {
        $this->display('ImportForm.tpl');
    }
}