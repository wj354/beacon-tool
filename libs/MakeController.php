<?php


namespace tool\libs;

use beacon\core\DB;
use beacon\core\Util;


class MakeController
{

    private int $appId = 0;
    private array|null $list;
    private array|null $form = null;
    private string $namespace = '';
    private string $appSpace = '';
    private string $className = '';
    private string $classKeyName = '';
    private string $baseController = 'ZeroController';

    private array $_use = [];
    private array $out = [];

    /**
     * MakeController constructor.
     * @param int $listId
     * @param string $namespace
     * @throws \beacon\core\DBException
     * @throws \Exception
     */
    public function __construct(int $listId = 0, string $namespace = '')
    {
        $this->list = DB::getRow('select * from @pf_tool_list where id=?', $listId);
        if ($this->list == null) {
            throw new \Exception('生成错误');
        }
        $this->appId = $this->list['appId'];
        if ($this->list['withCtl'] != 1) {
            return;
        }
        $className = $this->classKeyName = $this->list['key'];
        if (empty($namespace)) {
            $this->appSpace = Helper::fixNamespace($this->list['namespace']);
        } else {
            $this->appSpace = Helper::fixNamespace($namespace);
        }
        $baseControllerFullName = $this->appSpace . '\\controller\\ZeroController';
        if (!empty($this->list['baseController'])) {
            $baseControllerFullName = $this->list['baseController'];
        }
        $this->use($baseControllerFullName);
        $temp = explode('\\', $baseControllerFullName);
        $this->baseController = end($temp);
        $this->className = 'Zero' . $this->list['key'];
        $this->namespace = $this->appSpace . '\\zero\\controller';
        $this->createController();
    }

    /**
     * 添加使用
     * @param string|array $name
     */
    public function use(string|array $name)
    {
        if (is_string($name)) {
            $this->_use[$name] = $name;
        }
        if (is_array($name)) {
            foreach ($name as $item) {
                $this->_use[$item] = $item;
            }
        }
    }

    /**
     * 获取控件的表单
     * @throws \beacon\core\DBException
     * @throws \Exception
     */
    private function createForm()
    {
        if ($this->form == null) {
            $this->form = DB::getRow('select * from @pf_tool_form where id=?', $this->list['formId']);
            if (!$this->form) {
                throw new \Exception('没选择对应的表单模型');
            }
        }
        $form = $this->form;
        //如果是插件
        if ($form['extMode'] == 1) {
            $formClassName = $form['key'] . 'Plugin';
        } else {
            $formClassName = $form['key'] . 'Model';
        }
        $formFullClass = $this->appSpace . '\\zero\\model\\' . $formClassName;
        $this->use('beacon\\core\\Form');
        $this->use($formFullClass);
        $this->out[] = '    /**
     * 获取表单
     * @param string $type
     * @return Form
     */';
        $this->out[] = '    protected function getForm(string $type=\'\'):Form{';
        $this->out[] = '        return Form::create(' . $formClassName . '::class,$type);';
        $this->out[] = '    }';
    }

    /**
     * @throws \beacon\core\DBException
     */
    private function createSearchForm()
    {
        $row = DB::getRow('select id from @pf_tool_search where listId=? limit 0,1', $this->list['id']);
        if ($row) {
            $className = $this->classKeyName;
            $searchForm = trim($this->appSpace . '\\zero\\search\\' . $className . 'Search');
            $this->use($searchForm);
            $temp = explode('\\', $searchForm);
            $searchForm = end($temp);
            $this->out[] = '    /**
     * 获取搜索表单
     * @return Form|null
     */';
            $this->out[] = '    protected function getSearchForm():?Form{ ';
            $this->out[] = '        return Form::create(' . $searchForm . '::class,\'search\');';
            $this->out[] = '    }';
        } else {
            $this->out[] = '    /**
     * 获取搜索表单
     * @return Form|null
     */';
            $this->out[] = '    protected function getSearchForm():?Form{';
            $this->out[] = '        return null;';
            $this->out[] = '    }';
        }
    }

    /**
     * 创建数据查询器
     */
    private function createSelector()
    {

        $zero = [];
        $zero['tbName'] = '@pf_' . trim($this->list['tbName']);
        if ($this->list['tbAlias']) {
            $zero['tbAlias'] = $this->list['tbAlias'];
        }
        if (!empty($this->list['tbField'])) {
            $zero['tbField'] = $this->list['tbField'];
        }
        if (Util::isJson($this->list['tbJoin'])) {
            $this->list['tbJoin'] = json_decode($this->list['tbJoin'], true);
        }
        if (!empty($this->list['tbJoin'])) {
            $temps = [];
            foreach ($this->list['tbJoin'] as $item) {
                $temps[] = $item; // "{$item['join']} `{$item['name']}` {$item['alias']} on {$item['on']}";
            }
            $zero['tbJoin'] = $this->list['tbJoin'];
        }
        if (!empty($this->list['tbOrder'])) {
            $zero['tbOrder'] = $this->list['tbOrder'];
        }

        $this->out[] = '';
        $this->out[] = '    /**
     * 获取查询器
     * @return DBSelector
     */';
        $this->out[] = '    protected function getSelector():DBSelector{ ';

        $this->use('beacon\\core\\DBSelector');
        $tbAlias = isset($zero['tbAlias']) ? trim($zero['tbAlias']) : null;
        $tbName = trim($zero['tbName']);

        if (empty($tbAlias)) {
            $this->out[] = '        $selector = new DBSelector(' . var_export($tbName, true) . ');';
        } else {
            $this->out[] = '        $selector = new DBSelector(' . var_export($tbName . ' ' . $tbAlias, true) . ');';
        }

        $tbField = isset($zero['tbField']) ? trim($zero['tbField']) : null;
        if (!empty($tbField)) {
            $this->out[] = '        $selector->field(' . var_export($tbField, true) . ');';
        }
        $tbJoin = $zero['tbJoin'] ?? [];
        if (count($tbJoin) > 0) {
            foreach ($zero['tbJoin'] as $item) {
                $item['join'] = trim($item['join']);
                switch ($item['join']) {
                    case 'inner':
                        $this->out[] = '        $selector->innerJoin(' . var_export("`{$item['name']}` {$item['alias']}", true) . ')->joinOn(' . var_export($item['on'], true) . ');';
                        break;
                    case 'outer':
                        $this->out[] = '        $selector->outerJoin(' . var_export("`{$item['name']}` {$item['alias']}", true) . ')->joinOn(' . var_export($item['on'], true) . ');';
                        break;
                    case 'left':
                        $this->out[] = '        $selector->leftJoin(' . var_export("`{$item['name']}` {$item['alias']}", true) . ')->joinOn(' . var_export($item['on'], true) . ');';
                        break;
                    case 'right':
                        $this->out[] = '        $selector->rightJoin(' . var_export("`{$item['name']}` {$item['alias']}", true) . ')->joinOn(' . var_export($item['on'], true) . ');';
                        break;
                    case 'full':
                        $this->out[] = '        $selector->fullJoin(' . var_export("`{$item['name']}` {$item['alias']}", true) . ')->joinOn(' . var_export($item['on'], true) . ');';
                        break;
                }
            }
        }

        $this->createWhere();

        $sortItem = [];
        $fields = Helper::convertArray($this->list['fields'], []);
        foreach ($fields as $field) {
            if (!empty($field['orderName'])) {
                $sortItem[] = $field['orderName'];
            }
        }
        if (count($sortItem)) {
            $this->out[] = '        $sort = $this->param(\'sort:s\');';
            $this->out[] = '        $selector->sort($sort);';
        }
        $tbOrder = isset($zero['tbOrder']) ? trim($zero['tbOrder']) : null;
        if (!empty($tbOrder)) {
            $this->out[] = '        $selector->order(' . var_export($tbOrder, true) . ');';
        }

        $this->out[] = '        return $selector;';
        $this->out[] = '    }';


    }

    private function createZeroLoad()
    {
        $className = $this->classKeyName;
        $zeroConfig = [];
        if ($this->form == null) {
            $this->form = DB::getRow('select * from @pf_tool_form where id=?', $this->list['formId']);
            if (!$this->form) {
                throw new \Exception('没选择对应的表单模型');
            }
        }
        $form = $this->form;
        $zeroConfig['table'] = '@pf_' . trim($form['tbName']);
        if ($this->list['usePageList']) {
            $zeroConfig['pageSize'] = intval($this->list['pageSize']);
        }
        if ($this->list['useCustomTemplate']) {
            $zeroConfig['template'] = $this->list['template'];
            $zeroConfig['hookTemplate'] = $this->list['templateHook'];
            if (empty($zeroConfig['template'])) {
                $zeroConfig['template'] = 'list/' . Util::toUnder($className) . '.tpl';
            }
            if (empty($zeroConfig['hookTemplate'])) {
                $zeroConfig['templateHook'] = 'hook/' . Util::toUnder($className) . '.hook.tpl';
            }
        } else {
            $zeroConfig['template'] = 'list/' . Util::toUnder($className) . '.tpl';
            $zeroConfig['hookTemplate'] = 'hook/' . Util::toUnder($className) . '.hook.tpl';
        }
        //加载函数代码
        $this->out[] = '';
        $this->out[] = '    /**
     * ZeroController所需的配置信息
     * @return array
     */';
        $this->out[] = '    protected function zeroConfig():array{ ';
        $this->out[] = '        return ' . Helper::var($zeroConfig, '        ') . ';';
        $this->out[] = '    }';
    }

    /**
     * 创建查询条件的方法
     * @throws \beacon\core\DBException
     */
    private function createWhere()
    {

        $tbWhereTemps = [];
        $this->list['tbWhere'] = trim($this->list['tbWhere']);
        if (!empty($this->list['tbWhere'])) {
            $tbWhereTemps[] = ['sql' => $this->list['tbWhere'], 'param' => null, 'type' => -1];;
        }

        $searchFields = DB::getList('select `name`,tbWhere,tbWhereType from @pf_tool_search where listId=?', $this->list['id']);
        foreach ($searchFields as $field) {
            $field['tbWhere'] = trim($field['tbWhere']);
            if (!empty($field['tbWhere'])) {
                if (empty($field['name'])) {
                    $tbWhereTemps[] = ['sql' => $field['tbWhere'], 'param' => null, 'type' => -1];
                } else {
                    $tbWhereTemps[] = ['sql' => $field['tbWhere'], 'param' => $field['name'], 'type' => intval($field['tbWhereType'])];
                }
            }
        }
        if (count($tbWhereTemps) == 0) {
            return;
        }

        $useSearchForm = false;
        $row = DB::getRow('select id from @pf_tool_search where listId=? limit 0,1', $this->list['id']);
        //加入搜索表单
        if (count($tbWhereTemps) > 0 && $row != null) {
            $this->out[] = '          //获取搜索表单';
            $this->out[] = '        $req = $this->getSearchForm()->autoComplete(\'request\');';
            $useSearchForm = true;
        }

        $code1 = [];
        $code2 = [];
        foreach ($tbWhereTemps as $item) {
            if (empty($item['sql'])) {
                continue;
            }
            $pSql = var_export($item['sql'], true);
            if ($item['param'] !== null) {
                $keyName = trim($item['param']);
                if (empty($keyName)) {
                    continue;
                }
                $argName = $keyName;
                if (preg_match('@^(.*):([abfis])@', $keyName, $m)) {
                    $argName = $m[1];
                }
                $tempValue = '$tempValue';
                if (preg_match('@^\w+$@', $argName)) {
                    $tempValue = '$' . $argName;
                }
                $pName = var_export($argName, true);
                if ($useSearchForm) {
                    $code1[] = '        ' . $tempValue . '  = $req[' . $pName . '] ?? $this->param(' . $pName . ');';
                } else {
                    $code1[] = '        ' . $tempValue . '  =  $this->param(' . $pName . ');';
                }
                if ($item['type'] == -1) {
                    $code2[] = '        $selector->where(' . $pSql . ', ' . $tempValue . ' );';
                } else {
                    $code2[] = '        $selector->search(' . $pSql . ', ' . $tempValue . ' , ' . var_export($item['type'], true) . ');';
                }
            } elseif ($item['param'] === null || count($item['param']) == 0) {
                $code2[] = '        $selector->where(' . $pSql . ');';
            }
        }
        if (count($code1) > 0) {
            $this->out[] = '';
            $this->out[] = join("\n", $code1);
        }

        if (count($code2) > 0) {
            $this->out[] = '';
            $this->out[] = join("\n", $code2);
        }

    }


    private function createController()
    {
        $className = $this->className;
        $baseController = $this->baseController;
        $this->out[] = "class {$className} extends " . $baseController;
        $this->out[] = '{';

        $this->createZeroLoad();
        $this->createForm();
        $this->createSearchForm();
        $this->createSelector();
        $this->out[] = '';

        $this->out[] = '    #[Method(act: \'index\', method: Method::GET | Method::POST)]';
        $this->out[] = '    public function index(){';
        $this->out[] = '        return parent::index();';
        $this->out[] = '    }';


        //公开勾选的方法
        if (Util::isJson($this->list['actions'])) {
            $actions = json_decode($this->list['actions'], true);
            foreach ($actions as $action) {
                $this->out[] = '';
                $this->out[] = '    #[Method(act: \'' . Util::toUnder($action) . '\', method: Method::GET | Method::POST)]';
                $this->out[] = '    public function ' . $action . '(){';
                $this->out[] = '        return parent::' . $action . '();';
                $this->out[] = '    }';
            }
        }


        $this->out[] = '}';
    }

    //获取生成的代码
    public function getCode(): string
    {
        $this->use('beacon\core\Method');
        $code = [];
        $code[] = '<?php';
        $code[] = '';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';
        $code[] = '/**';
        $code[] = '* ' . $this->list['title'];
        $code[] = '* Created by Beacon AI Tool 4.0.';
        $code[] = '* User: wj008';
        $code[] = '* Web: www.wj008.net';
        $code[] = '* Date: ' . date('Y/m/d');
        $code[] = '* Time: ' . date('H:i:s');
        $code[] = '* 注意：该代码由代码工具生成，不要在此处修改任何代码，将会被覆盖，如要修改请在应用 controller目录中创建同名类并继承该生成类进行调整';
        $code[] = '*/';
        $code[] = '';
        foreach ($this->_use as $item) {
            $code[] = 'use ' . $item . ';';
        }
        $code[] = '';
        $code[] = join("\n", $this->out);
        return join("\n", $code);
    }

    /**
     * 生成文件
     */
    public function makeFile()
    {
        if (isset($this->list['withCtl']) && $this->list['withCtl'] == 1) {
            $rootDir = ROOT_DIR;
            $app = DB::getRow('select dirName from @pf_tool_app where id=?', $this->appId);
            if ($app && !empty($app['dirName'])) {
                if (is_dir($app['dirName'])) {
                    $rootDir = $app['dirName'];
                }
            }
            $path = Util::path($rootDir, $this->namespace);
            Util::makeDir($path);
            $code = $this->getCode();
            file_put_contents(Util::path($path, $this->className . '.php'), $code);
        }
    }

    public static function make(int $listId = 0)
    {
        $maker = new static($listId);
        $maker->makeFile();
        MakeListTemplate::make($listId);
    }

}