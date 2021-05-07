<?php


namespace tool\controller;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\DBSelector;
use beacon\core\Form;
use beacon\core\Method;
use beacon\core\Request;
use beacon\core\Util;
use beacon\core\App;
use tool\libs\ToolDB;
use tool\model\AppListModel;
use tool\libs\MakeSearch;

/**
 * Class AppField
 * @package tool\controller
 */
class AppList extends AppBase
{
    /**
     * @param int $formId
     * @return string
     * @throws DBException
     */
    protected function getFormKey(int $formId = 0): string
    {
        static $cache = [];
        if (isset($cache[$formId])) {
            return $cache[$formId];
        }
        $form = DB::getRow('select `key` from @pf_tool_form where id=?', $formId);
        if ($form) {
            $cache[$formId] = $form['key'];
        } else {
            $cache[$formId] = '-';
        }
        return $cache[$formId];
    }

    /**
     * @throws DBException
     */
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index()
    {
        if (!$this->isAjax()) {
            $appList = DB::getList('select * from @pf_tool_app');
            $this->assign('apps', $appList);
            $this->display('list/app_list.tpl');
            return;
        }
        $title = $this->get('title', '');
        $selector = new DBSelector('@pf_tool_list');
        if ($title) {
            if (preg_match('#@pf_(\w+)#', $title, $data)) {
                $selector->where("`tbName` LIKE CONCAT('%',?,'%')", [$data[1]]);
            } else {
                $selector->where("(`key` LIKE CONCAT('%',?,'%') or `title` LIKE CONCAT('%',?,'%'))", [$title, $title]);
            }
        }
        if ($this->appId) {
            $selector->where("appId=?", $this->appId);
        }
        $sort = $this->get('sort:s', 'updateTime-desc');
        $selector->sort($sort);
        $data = $selector->pageData();
        foreach ($data['list'] as &$datum) {
            $datum['formKey'] = $this->getFormKey($datum['formId']);
            $appId = $datum['appId'];
            $appRow = DB::getRow('select `module` from @pf_tool_app where id=?', $appId);
            if ($appRow) {
                $datum['testUrl'] = App::url('^/' . $appRow['module'] . '/' . $datum['key']);
            } else {
                $datum['testUrl'] = '#';
            }
        }
        $data['list'] = $this->hookData($data['list'], 'hook/app_list.tpl');
        $this->success('获取数据成功', $data);
    }


    /**
     * @param string $key
     * @throws DBException
     */
    #[Method(act: 'check_key', method: Method::GET | Method::POST)]
    public function checkKey(string $key = '')
    {
        [$ret, $msg] = AppListModel::validKeyFunc($key);
        if ($ret) {
            $this->success($msg);
        }
        $this->error($msg);
    }

    /**
     * 获取导入数据
     * @param string $file
     * @return mixed
     */
    private function getImport(string $file = '')
    {
        if (empty($file)) {
            $this->error('导入文件名不能为空');
        }
        if (!preg_match('@^[0-9]+\.list$@', $file)) {
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

    /**
     * @param array $input
     * @throws DBException
     */
    private function fixInput(array &$input)
    {
        $app = DB::getRow('select * from @pf_tool_app where id=?', $input['appId']);
        if ($app) {
            $input['namespace'] = $app['namespace'];
        } else {
            $this->error(['appId' => '不存在的项目']);
        }
        $fRow = DB::getRow('select tbName from @pf_tool_form where id=?', $input['formId']);
        if ($fRow) {
            $input['tbName'] = $fRow['tbName'];
        } else {
            $this->error(['formId' => '不存在的表单']);
        }
    }

    /**
     * @param int $copyId
     * @param string $import
     * @throws DBException
     */
    #[Method(act: 'add', method: Method::GET | Method::POST)]
    public function add(int $copyId = 0, string $import = '')
    {
        $form = Form::create(AppListModel::class, 'add');
        if ($this->isGet()) {
            if ($copyId) {
                $row = DB::getItem('@pf_tool_list', $copyId);
                $row['formId'] = '';
                $row['title'] = '';
                $row['key'] = '';
                $form->setData($row);
            } elseif (!empty($import)) {
                $data = $this->getImport($import);
                $form->setData($data['list']);
            }
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $this->fixInput($input);
        $input['updateTime'] = time();
        DB::insert('@pf_tool_list', $input);
        $listId = DB::lastInsertId();
        if (!empty($copyId)) {
            $sList = DB::getList('select * from @pf_tool_search where listId=?', $copyId);
            foreach ($sList as $field) {
                unset($field['id']);
                $field['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $listId)) + 10;
                $field['listId'] = $listId;
                if (empty($field['tabIndex'])) {
                    $field['tabIndex'] = 'base';
                }
                DB::insert('@pf_tool_search', $field);
            }
        } elseif (!empty($import)) {
            $data = $this->getImport($import);
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
                $field['listId'] = $listId;
                if (empty($field['tabIndex'])) {
                    $field['tabIndex'] = 'base';
                }
                DB::insert('@pf_tool_search', $field);
            }
            $path = Util::path(ROOT_DIR, 'runtime/temp', $import);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        MakeSearch::make($listId);
        $this->success('添加' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'edit', method: Method::GET | Method::POST)]
    public function edit(int $id = 0)
    {
        $row = DB::getItem('@pf_tool_list', $id);
        $form = Form::create(AppListModel::class, 'edit');
        if ($this->isGet()) {
            $form->setData($row);
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $this->fixInput($input);
        $input['updateTime'] = time();
        DB::update('@pf_tool_list', $input, $id);
        MakeSearch::make($id);
        $this->success('编辑' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'del', method: Method::GET | Method::POST)]
    public function delete(int $id = 0)
    {
        if ($id == 0) {
            $this->error('参数有误');
        }
        DB::delete('@pf_tool_list', $id);
        DB::delete('@pf_tool_search', 'listId=?', $id);
        $this->success('删除列表数据成功');
    }

    /**
     * @param int $formId
     * @throws DBException
     */
    #[Method(act: 'get_field', method: Method::GET | Method::POST)]
    public function getField(int $formId = 0)
    {
        $fRow = DB::getRow('select tbName,appId from @pf_tool_form where id=?', $formId);
        if ($fRow == null) {
            $this->success('ok', []);
        }
        $appId = intval($fRow['appId']);
        $fields = DB::getList('select `name`,`label` from @pf_tool_field where formId=?', $formId);
        $temp = [];
        foreach ($fields as $field) {
            $temp[$field['name']] = $field['label'];
        }
        $db = ToolDB::db($appId);
        $options = [];
        $list = $db->getFields('@pf_' . $fRow['tbName']);
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

    /**
     * @param int $formId
     * @throws DBException
     */
    #[Method(act: 'db_field', method: Method::GET | Method::POST)]
    public function dbField(int $formId = 0)
    {
        $orgFields = $this->get('tbField', '');
        $tbAlias = $this->get('tbAlias', '');
        $select = explode(',', $orgFields);
        $selectMap = [];
        foreach ($select as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $selectMap[$item] = 1;
            }
        }
        $fRow = DB::getRow('select appId,tbName from @pf_tool_form where id=?', $formId);
        if ($fRow == null) {
            $this->error('不存在模型');
        }
        $appId = intval($fRow['appId']);
        $fields = DB::getList('select `name`,`label` from @pf_tool_field where formId=?', $formId);
        $temp = [];
        foreach ($fields as $field) {
            $temp[$field['name']] = $field['label'];
        }
        $db = ToolDB::db($appId);
        $list = $db->getFields('@pf_' . $fRow['tbName']);
        foreach ($list as &$item) {
            if (!empty($tbAlias)) {
                $item['Field'] = $tbAlias . '.' . $item['Field'];
            }
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
        $this->display('list/list.dbfield.tpl');
    }

    #[Method(act: 'operate', method: Method::GET)]
    public function operate()
    {
        $this->display('form/list.operate.tpl');
    }

    #[Method(act: 'import', method: Method::GET | Method::POST)]
    public function import()
    {
        $this->display('form/list_import.tpl');
    }

    /**
     * @param int $listId
     * @throws DBException
     */
    #[Method(act: 'export', method: Method::GET | Method::POST)]
    public function export(int $listId)
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
        $data['tool'] = '4.0';
        $data['type'] = 'list';
        unset($list['id']);
        unset($list['appId']);
        unset($list['formId']);
        $data['list'] = $list;
        $data['search'] = $search;
        $out = json_encode($data, JSON_UNESCAPED_UNICODE);
        $filename = '列表-' . $list['title'] . '.list';
        Request::setHeader('Content-type', 'text/plain');
        Request::setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        echo $out;
        exit;
    }


}