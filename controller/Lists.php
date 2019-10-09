<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-28
 * Time: 下午10:01
 */

namespace tool\controller;


use beacon\DB;
use beacon\Route;
use beacon\Utils;
use beacon\SqlSelector;
use tool\form\ListForm;
use tool\lib\MakeController;
use tool\lib\MakeSearch;

class Lists extends BaseController
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
            $selector = new SqlSelector('@pf_tool_list');
            $title = $this->get('title', '');
            if ($title) {
                if (preg_match('#@pf_(\w+)#', $title, $data)) {
                    $selector->where("`tbName` LIKE CONCAT('%',?,'%')", [$data[1]]);
                } else {
                    $selector->where("(`title` LIKE CONCAT('%',?,'%') or `key` LIKE CONCAT('%',?,'%'))", [$title, $title]);
                }
            }
            if ($appId) {
                $selector->where("appId=?", $appId);
            }
            $sort = $this->get('sort', '');
            switch ($sort) {
                case 'id-asc':
                    $selector->order('id asc');
                    break;
                case 'id-desc':
                    $selector->order('id desc');
                    break;
                case 'title-asc':
                    $selector->order('title asc');
                    break;
                case 'title-desc':
                    $selector->order('title desc');
                    break;
                default:
                    $selector->order('updateTime desc');
                    break;
            }
            $plist = $selector->getPageList();
            $pageInfo = $plist->getInfo();
            $list = $plist->getList();
            $appRow = [];
            foreach ($list as &$item) {
                $fRow = DB::getRow('select `key`,tbName from @pf_tool_form where id=?', $item['formId']);
                if ($fRow) {
                    $item['formKey'] = $fRow['key'];
                } else {
                    $item['formKey'] = '-';
                }
                $appId = $item['appId'];
                if (!isset($appRow[$appId])) {
                    $appRow[$appId] = DB::getOne('select `module` from @pf_tool_app where id=?', $appId);
                }
                $item['testUrl'] = Route::url('^/' . $appRow[$appId] . '/' . $item['key']);
            }
            $this->assign('list', $list);
            $this->assign('pageInfo', $pageInfo);
            $data = $this->getAssign();
            $data['list'] = $this->hook('List.hook.tpl', $data['list']);
            $this->success('获取数据成功', $data);
        }

        $appList = DB::getList('select * from @pf_tool_app');
        $this->assign('appId', $appId);
        $this->assign('applist', $appList);
        $this->display('List');
    }

    public function checkKeyAction()
    {
        $form = new ListForm();
        $key = $this->param('key', '');
        $validFunc = $form->getField('key')->getFunc('valid');
        if ($validFunc && $validFunc($key) === null) {
            $this->success('列表标识可以使用');
        }
        $this->error('列表标识已经存在');
    }

    private function getImport($file)
    {
        if (empty($file)) {
            $this->error('导入文件名不能为空');
        }
        if (!preg_match('@^[0-9]+\.list$@', $file)) {
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
        if (empty($data['type']) || $data['type'] != 'list') {
            unlink($path);
            $this->error('无效的文件数据');
        }
        if (empty($data['list']) || !is_array($data['list'])) {
            unlink($path);
            $this->error('无效的文件数据');
        }
        if (empty($data['search']) || !is_array($data['search'])) {
            unlink($path);
            $this->error('无效的文件数据');
        }
        return $data;
    }

    public function addImportAction(string $import = '')
    {
        $data = $this->getImport($import);
        $form = new ListForm('add');
        if ($this->isGet()) {
            $form->setValues($data['list'], true);
            $this->displayForm($form);
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select * from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['appId' => '不存在的项目']);
            }
            $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $values['formId']);
            if ($fRow) {
                $values['tbName'] = $fRow['tbName'];
            } else {
                $this->error(['formId' => '不存在的表单']);
            }
            $values['updateTime'] = time();
            $id = $form->insert($values);
            $fieldList = $data['search'];
            $dbField = DB::getFields('@pf_tool_search');
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
                $field['listId'] = $id;
                if (empty($field['tabIndex'])) {
                    $field['tabIndex'] = 'base';
                }
                DB::insert('@pf_tool_search', $field);
            }
            MakeSearch::make($id);
            $path = Utils::path(ROOT_DIR, 'runtime/temp', $import);
            if (file_exists($path)) {
                unlink($path);
            }
            $this->success('添加' . $form->title . '成功');
        }
    }

    public function addAction($copyId = 0)
    {
        $form = new ListForm('add');
        if ($this->isGet()) {
            if ($copyId) {
                $row = $form->getRow($copyId);
                $row['formId'] = '';
                $row['title'] = '';
                $row['key'] = '';
                $form->setValues($row, true);
            }
            $this->displayForm($form);
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select * from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['appId' => '不存在的项目']);
            }
            $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $values['formId']);
            if ($fRow) {
                $values['tbName'] = $fRow['tbName'];
            } else {
                $this->error(['formId' => '不存在的表单']);
            }
            $values['updateTime'] = time();
            $id = $form->insert($values);
            //拷贝添加
            if (!empty($copyId)) {
                $slist = DB::getList('select * from @pf_tool_search where listId=?', $copyId);
                foreach ($slist as $field) {
                    unset($field['id']);
                    $field['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $id)) + 10;
                    $field['listId'] = $id;
                    if (empty($field['tabIndex'])) {
                        $field['tabIndex'] = 'base';
                    }
                    DB::insert('@pf_tool_search', $field);
                }
                MakeSearch::make($id);
            } else {
                MakeController::make($id);
            }
            $this->success('添加' . $form->title . '成功');
        }
    }

    public function editAction(int $id = 0)
    {
        $form = new ListForm('edit');
        if ($id == 0) {
            $this->error('参数有误');
        }
        $row = $form->getRow($id);
        $form->setValues($row);
        if ($this->isGet()) {
            $this->displayForm($form);
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            $app = DB::getRow('select * from @pf_tool_app where id=?', $values['appId']);
            if ($app) {
                $values['namespace'] = $app['namespace'];
            } else {
                $this->error(['appId' => '不存在的项目']);
            }
            $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $values['formId']);
            if ($fRow) {
                $values['tbName'] = $fRow['tbName'];
            } else {
                $this->error(['formId' => '不存在的表单']);
            }
            $values['updateTime'] = time();
            $form->update($id, $values);
            MakeController::make($id);
            $this->success('编辑' . $form->title . '成功');
        }
    }

    //获取字段
    public function getFieldAction(int $formId = 0)
    {
        $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $formId);
        if ($fRow == null) {
            $this->success('ok', []);
        }
        $fields = DB::getList('select `name`,`label` from @pf_tool_field where formId=?', $formId);
        $temp = [];
        foreach ($fields as $field) {
            $temp[$field['name']] = $field['label'];
        }
        $options = [];
        $list = DB::getFields('@pf_' . $fRow['tbName']);
        foreach ($list as $item) {
            $field = $item['Field'];
            if (isset($temp[$field])) {
                $comment = $temp[$field];
            } else {
                $comment = !empty($item['Comment']) ? $item['Comment'] : $field;
            }
            $options[] = [$field, $comment . ' | ' . $field];
        }
        $this->success('ok', ['tbName' => '@pf_' . $fRow['tbName'], 'options' => $options]);
    }

    public function delAction(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        DB::delete('@pf_tool_list', $id);
        DB::delete('@pf_tool_search', 'listId=?', $id);
        $this->success('删除账号成功');
    }

    public function operateAction()
    {
        $this->display('List.operate.tpl');
    }

    public function dbfieldAction(int $formId = 0, string $pname = '')
    {
        $orgFields = '';
        if (!empty($pname)) {
            $orgFields = $this->get($pname, '');
        }
        $select = explode(',', $orgFields);
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
        $fields = DB::getList('select `name`,`label` from @pf_tool_field where formId=?', $formId);
        $temp = [];
        foreach ($fields as $field) {
            $temp[$field['name']] = $field['label'];
        }
        $list = DB::getFields('@pf_' . $fRow['tbName']);
        foreach ($list as &$item) {
            $field = $item['Field'];
            if (isset($selectMap[$field])) {
                $item['checked'] = true;
            } else {
                $item['checked'] = false;
            }
            if (isset($temp[$field])) {
                $item['Comment'] = $temp[$field];
            } else {
                $item['Comment'] = !empty($item['Comment']) ? $item['Comment'] : $field;
            }
        }
        $this->assign('list', $list);
        $this->display('List.dbfield.tpl');
    }

    //导出列表
    public function exportAction(int $listId)
    {
        $list = DB::getRow('select * from @pf_tool_list where id=?', $listId);
        if ($list == null) {
            $this->error('列表不存在');
        }
        $search = DB::getList('select * from @pf_tool_search where listId=?', $listId);
        foreach ($search as &$field) {
            unset($field['id']);
            unset($field['listId']);
        }
        $data = [];
        $data['tool'] = '2.1';
        $data['type'] = 'list';
        unset($list['id']);
        unset($list['appId']);
        unset($list['formId']);
        $data['list'] = $list;
        $data['search'] = $search;
        $out = json_encode($data, JSON_UNESCAPED_UNICODE);
        $filename = '列表-' . $list['title'] . '.list';
        $this->setHeader('Content-type', 'text/plain');
        $this->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        echo $out;
        exit;
    }

    public function importAction()
    {
        $this->display('ImportList.tpl');
    }
}