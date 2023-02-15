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
use tool\libs\MakeSearch;
use tool\libs\Helper;
use tool\model\AppSearchModel;

/**
 * Class AppSearch
 * @property array $list
 * @property int $listId
 * @package tool\controller
 */
class AppSearch extends AppBase
{
    private int $_listId = 0;
    private ?array $_list = null;

    /**
     * @param string $name
     * @return array|bool|float|int|mixed|string|null
     * @throws DBException
     */
    public function __get(string $name)
    {
        if ($name == 'listId') {
            if ($this->_listId == 0) {
                $this->_listId = $this->param('listId:i', 0);
                if ($this->_listId == 0) {
                    $this->error('缺少参数', ['back' => App::url('~/AppList')]);
                }
            }
            return $this->_listId;
        }
        if ($name == 'list') {
            if ($this->_list === null) {
                $this->_list = DB::getRow('select * from @pf_tool_list where id=?', $this->listId);
                if ($this->_list == null) {
                    $this->error('添加失败,表单不存在');
                }
            }
            return $this->_list;
        }
    }

    /**
     * @throws DBException
     */
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index()
    {
        if (!$this->isAjax()) {
            $this->appId = intval($this->list['appId']);
            $listRow = $this->list;
            $appRow = DB::getRow('select `module` from @pf_tool_app where id=?', $this->appId);
            if ($appRow) {
                $listRow['testUrl'] = App::url('^/' . $appRow['module'] . '/' . $listRow['key']);
            } else {
                $listRow['testUrl'] = '#';
            }
            $this->assign('listRow', $listRow);
            $this->display('list/app_search.tpl');
            return;
        }
        $name = $this->get('name', '');
        $selector = new DBSelector('@pf_tool_search');
        $selector->where('listId=?', $this->listId);
        $selector->search("(`name` LIKE CONCAT('%',?,'%') or `label` LIKE CONCAT('%',?,'%'))", $name);
        $selector->search('tabIndex=?', $this->get('tabIndex', ''));
        $sort = $this->get('sort:s', 'sort-asc');
        $selector->sort($sort,['id','name','sort']);
        $data['pageInfo'] = ['recordsCount' => $selector->getCount()];
        $data['list'] = $selector->getList();
        $data['list'] = $this->hookData($data['list'], 'hook/app_search.tpl');
        $this->success('获取数据成功', $data);
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
        if (!empty($input['extend'])) {
            $extend = $input['extend'];
            if (isset($extend['names'])) {
                $input['names'] = Helper::convertArray($extend['names']);
            }
        }
        if ($input['type'] != 'Hidden') {
            $input['hidden'] = false;
        }
    }

    /**
     * @param string $type
     * @return Form
     */
    private function getForm(string $type): Form
    {
        $form = Form::create(AppSearchModel::class, $type);
        if ($this->isPost()) {
            $name = $this->post('type:s', '');
            $this->setSupport($form, $name);
        }
        return $form;
    }

    /**
     * @param int $copyId
     * @throws DBException
     */
    #[Method(act: 'add', method: Method::GET | Method::POST)]
    public function add(int $copyId = 0)
    {
        $form = $this->getForm('add');
        if ($this->isGet()) {
            if ($copyId > 0) {
                $row = DB::getRow('select * from @pf_tool_search where id=?', $copyId);
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
        $input['listId'] = $this->listId;
        DB::insert('@pf_tool_search', $input);
        MakeSearch::make($this->listId);
        $this->success('添加' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    #[Method(act: 'edit', method: Method::GET | Method::POST)]
    public function edit(int $id = 0)
    {
        $form = $this->getForm('edit');
        if ($this->isGet()) {
            $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
            if ($row == null) {
                $this->error('不存在的数据');
            }
            $name = $row['type'];
            $this->setSupport($form, $name);
            $form->setData($row);
            $this->displayForm($form);
            return;
        }
        $input = $this->completeForm($form);
        $this->fixInput($input);
        $input['listId'] = $this->listId;
        DB::update('@pf_tool_search', $input, $id);
        MakeSearch::make($this->listId);
        $this->success('编辑' . $form->title . '成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    private function pasteField(int $id = 0)
    {
        $input = DB::getRow('select * from @pf_tool_search where id=?', $id);
        if ($input == null) {
            $this->error('不存在的数据');
        }
        unset($input['id']);
        $input['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $this->listId)) + 10;
        $this->fixInput($input);
        $input['listId'] = $this->listId;
        DB::insert('@pf_tool_search', $input);
    }

    #[Method(act: 'paste', method: Method::GET | Method::POST)]
    public function pasteAct($type = '', array $fields = [])
    {
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

    /**
     * @throws DBException
     */
    #[Method(act: 'select_field', method: Method::GET | Method::POST)]
    public function selectField()
    {
        $formId = $this->list['formId'];
        $this->assign('formId', $formId);
        if (!$this->isAjax()) {
            return $this->display('list/select_field.tpl');
        }
        $name = $this->get('name', '');
        $selector = new DBSelector('@pf_tool_field');
        $selector->where('formId=?', $formId);
        $selector->search("(`name` LIKE CONCAT('%',?,'%'))", $name);
        $sort = $this->get('sort:s', 'sort-asc');
        $selector->sort($sort,['id','name','sort']);
        $data = [];
        $data['pageInfo'] = ['recordsCount' => $selector->getCount()];
        $data['list'] = $selector->getList();
        $data['list'] = $this->hookData($data['list'], 'hook/select_field.tpl');
        $this->success('获取数据成功', $data);
    }

    /**
     * @param array $choice
     * @throws DBException
     */
    #[Method(act: 'copy_choice', method: Method::GET | Method::POST)]
    public function copyChoice(array $choice = [])
    {
        $search_type = AppSearchModel::typeOptions();
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
            $input = [];
            foreach (['name', 'label', 'type', 'hidden',
                         'before', 'after', 'viewMerge', 'default',
                         'extend', 'attrPlaceholder'
                         , 'attrClass'
                         , 'attrStyle'
                         , 'attrs'
                         , 'names'
                     ] as $key) {
                $input[$key] = $field[$key];
            }
            $input['tabIndex'] = 'base';
            $input['varType'] = 'string';

            switch ($field['dbType']) {
                case 'int':
                case 'tinyint':
                    $input['varType'] = 'int';
                    break;
                case 'decimal':
                case 'double':
                case 'float':
                    $input['varType'] = 'float';
                    break;
                default:
                    $input['varType'] = 'string';
                    break;
            }
            if ($input['varType'] == 'string') {
                $input['tbWhere'] = "`{$input['name']}` like concat('%',?,'%')";
            } else {
                $input['tbWhere'] = "`{$input['name']}` = ?";
            }
            if ($input['type'] == 'Linkage') {
                $input['tbWhere'] = '';
            }
            $input['tbWhereType'] = 2;
            $input['varType'] = 'string';
            $input['sort'] = intval(DB::getMax('@pf_tool_search', 'sort', 'listId=?', $this->listId)) + 10;
            $input['listId'] = $this->listId;
            if ($input['type'] == 'Check') {
                $input['type'] = 'Select';
                $extend = Helper::convertArray($input['extend']);
                $extend['headerText'] = '全部';
                $input['after'] = '';
                $input['default'] = '';
                if ($input['name'] == 'allow') {
                    $extend['options'] = [['value' => 1, 'text' => '启用'], ['value' => 0, 'text' => '禁用']];
                }
                if ($input['name'] == 'lock') {
                    $extend['options'] = [['value' => 1, 'text' => '锁定'], ['value' => 0, 'text' => '正常']];
                }
                $input['extend'] = $extend;
            }
            DB::insert('@pf_tool_search', $input);
        }
        MakeSearch::make($this->listId);
        $this->success('拷贝成功');
    }

    /**
     * @param int $id
     * @param int $sort
     * @throws DBException
     */
    #[Method(act: 'sort', method: Method::GET | Method::POST)]
    public function sort(int $id = 0, $sort = 0)
    {
        $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
        if ($row == null) {
            $this->error('不存在的数据');
        }
        $this->_listId = intval($row['listId']);
        DB::update('@pf_tool_search', ['sort' => $sort], $id);
        MakeSearch::make($this->listId);
        $this->success('更新排序成功');
    }

    /**
     * @param int $id
     * @throws DBException
     */
    private function remove(int $id = 0)
    {
        if ($id == 0) {
            return;
        }
        $row = DB::getRow('select * from @pf_tool_search where id=?', $id);
        $this->_listId = intval($row['listId']);
        DB::delete('@pf_tool_search', $id);
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
        MakeSearch::make($this->listId);
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
        MakeSearch::make($this->listId);
        $this->success('删除选中字段成功');
    }

}