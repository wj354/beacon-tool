<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 下午8:38
 */

namespace tool\lib;


use beacon\DB;
use beacon\Logger;
use beacon\Utils;

class MakeController
{
    private $list = null;
    private $form = null;
    private $namespace = null;
    private $appSpace = null;

    private $_use = [];
    private $out = [];
    private $classFullName = '';
    private $className = '';
    private $classKeyName = '';
    private $baseController = 'ZeroController';
    private $zeroConfig = null;

    public function __construct(int $listId = 0, string $namespace = null)
    {
        $this->list = DB::getRow('select * from @pf_tool_list where id=?', $listId);
        if ($this->list == null) {
            throw new \Exception('生成错误');
        }
        if (isset($this->list['withCtl']) && $this->list['withCtl'] == 1) {
            if (empty($namespace)) {
                $this->appSpace = trim($this->list['namespace'], '\\');
            } else {
                $temp = str_replace('/', '\\', $namespace);
                $this->appSpace = trim($temp, '\\');
            }
            $className = $this->classKeyName = $this->list['key'];
            $baseControllerFullName = $this->appSpace . '\\controller\\ZeroController';
            if (!empty($this->list['baseController'])) {
                $baseControllerFullName = $this->list['baseController'];
            }
            $this->use($baseControllerFullName);
            $temp = explode('\\', $baseControllerFullName);
            $this->baseController = end($temp);
            $this->className = 'Zero' . $this->list['key'];
            $this->namespace = $this->appSpace . '\\zero\\controller';
            $this->classFullName = $this->namespace . '\\Zero' . $className;
            $this->createClass();
        }
    }

    public function use(string $name)
    {
        $this->_use[$name] = $name;
    }

    //创建加载配置的方法
    private function createZeroLoadMethod()
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
        //actionForm
        //如果是插件
        if ($form['extMode'] == 1) {
            $formClassName = 'Zero' . $form['key'] . 'Plugin';
        } else {
            $formClassName = 'Zero' . $form['key'] . 'Form';
        }
        $zeroConfig['actionForm'] = $this->appSpace . '\\zero\\form\\' . $formClassName;
        $zeroConfig['tbName'] = '@pf_' . trim($form['tbName']);
        if ($this->list['usePageList']) {
            $zeroConfig['pageSize'] = intval($this->list['pageSize']);
        } else {
            $zeroConfig['pageSize'] = 0;
        }
        if ($this->list['useCustomTemplate']) {
            $zeroConfig['template'] = $this->list['template'];
            $zeroConfig['templateHook'] = $this->list['templateHook'];
            if (empty($zeroConfig['template'])) {
                $zeroConfig['template'] = 'Zero' . $className . '.tpl';
            }
            if (empty($zeroConfig['templateHook'])) {
                $zeroConfig['templateHook'] = 'hook/Zero' . $className . '.hook.tpl';
            }
        } else {
            $zeroConfig['template'] = 'Zero' . $className . '.tpl';
            $zeroConfig['templateHook'] = 'hook/Zero' . $className . '.hook.tpl';
        }
        //列表中附加的原始字段
        $strOrgFields = trim($this->list['orgFields']);
        if (!empty($strOrgFields)) {
            $orgFields = explode(',', $strOrgFields);
            try {
                $dbFieldList = DB::getFields($zeroConfig['tbName']);
                $dbFields = [];
                foreach ($dbFieldList as $item) {
                    if (!empty($item['Field'])) {
                        $dbFields[$item['Field']] = 1;
                    }
                }
            } catch (\Exception $exception) {
                $dbFields = [];
            }
            $temp = [];
            foreach ($orgFields as $item) {
                $item = trim($item);
                if (isset($dbFields[$item])) {
                    $temp[] = $item;
                }
            }
            if (count($temp) > 0) {
                $zeroConfig['origFields'] = $temp;
            }
        }
        //加载函数代码
        $this->out[] = '';
        $this->out[] = '    //为ZeroController所需的配置信息';
        $this->out[] = '    protected function zeroLoad(){ ';
        $this->out[] = '        return ' . Helper::export($zeroConfig, '        ') . ';';
        $this->out[] = '    }';
        $this->zeroConfig = $zeroConfig;
        return $zeroConfig;
    }

    //创建查询条件的方法
    private function createZeroWhereMethod()
    {
        $zeroConfig = $this->zeroConfig;
        $zeroSelectorData = [];
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
        if (count($tbWhereTemps) > 0) {
            $temps = [];
            foreach ($tbWhereTemps as $item) {
                if ($item['param'] === '' || $item['param'] === null) {
                    $item['type'] = -1;
                    $item['param'] = null;
                } else {
                    $params = explode(',', $item['param']);
                    foreach ($params as $idx => $param) {
                        $params[$idx] = trim($param);
                    }
                    $item['param'] = $params;
                }
                $temps[] = $item;
            }
            $tbWhereTemps = $temps;
        }

        //生成 where查询行数
        $this->out[] = '';
        $this->use('beacon\SqlSelector');
        $this->out[] = '    //为ZeroController所需的条件查询';
        $this->out[] = '    protected function zeroWhere(SqlSelector $selector){ ';
        $useSearchForm = false;
        $searchForm = null;
        $searchFormNames = null;
        $sCount = DB::getOne('select count(1) from @pf_tool_search where listId=?', $this->list['id']);
        if ($sCount && $sCount > 0) {
            $className = $this->classKeyName;
            $searchFormNames = trim($this->appSpace . '\\zero\\search\\Zero' . $className . 'Search');
            $temp = explode('\\', $searchFormNames);
            $searchForm = end($temp);
        }
        //加入搜索表单
        if (count($tbWhereTemps) > 0 && !empty($searchForm)) {
            $this->use($searchFormNames);
            $this->out[] = '          //从搜索表单获取数据';
            $this->out[] = '        $search = new ' . $searchForm . '();';
            $this->out[] = '        $req = $search->autoComplete(\'request\');';
            $useSearchForm = true;
        }
        foreach ($tbWhereTemps as $item) {
            if (empty($item['sql'])) {
                continue;
            }
            if ($item['param'] !== null && count($item['param']) > 1) {
                $this->out[] = '        $args = [];';
                foreach ($item['param'] as $keyName) {
                    $keyName = trim($keyName);
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
                    if ($useSearchForm) {
                        $this->out[] = '        ' . $tempValue . ' = isset($req[' . var_export($argName, true) . ']) ? $req[' . var_export($argName, true) . '] : $this->param(' . var_export($keyName, true) . ');';
                    } else {
                        $this->out[] = '        ' . $tempValue . ' = $this->param(' . var_export($keyName, true) . ');';
                    }
                    $this->out[] = '        $args[] = ' . $tempValue . ';';
                }
                if ($item['type'] == -1) {
                    $this->out[] = '        $selector->where(' . var_export($item['sql'], true) . ', $args);';
                } else {
                    if (isset($args[0])) {
                        $this->out[] = '        if(isset($args[0])){';
                        $this->out[] = '            $selector->search(' . var_export($item['sql'], true) . ', $args[0] , ' . var_export($item['type'], true) . ');';
                        $this->out[] = '        }';
                    }
                }
            } elseif ($item['param'] !== null && count($item['param']) == 1) {
                $keyName = trim($item['param'][0]);
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
                if ($useSearchForm) {
                    $this->out[] = '        ' . $tempValue . '  = isset($req[' . var_export($argName, true) . ']) ? $req[' . var_export($argName, true) . '] : $this->param(' . var_export($keyName, true) . ');';
                } else {
                    $this->out[] = '        ' . $tempValue . '  =  $this->param(' . var_export($keyName, true) . ');';
                }
                if ($item['type'] == -1) {
                    $this->out[] = '        $selector->where(' . var_export($item['sql'], true) . ', ' . $tempValue . ' );';
                } else {
                    $this->out[] = '        $selector->search(' . var_export($item['sql'], true) . ', ' . $tempValue . ' , ' . var_export($item['type'], true) . ');';
                }
            } elseif ($item['param'] === null || count($item['param']) == 0) {
                $this->out[] = '        $selector->where(' . var_export($item['sql'], true) . ');';
            }
            $this->out[] = '';
        }
        $this->out[] = '        return $selector;';
        $this->out[] = '    }';
        return $zeroSelectorData;
    }

    //创建查询器的方法
    private function createZeroSelectorMethod()
    {
        $zeroSelectorData = [];
        $zeroSelectorData['tbName'] = '@pf_' . trim($this->list['tbName']);
        if ($this->list['tbAlias']) {
            $zeroSelectorData['tbAlias'] = $this->list['tbAlias'];
        }
        //没有使用模板
        if (empty($this->list['useSqlTemplate'])) {
            if (!empty($this->list['tbField'])) {
                $zeroSelectorData['tbField'] = $this->list['tbField'];
            }
            if (Utils::isJson($this->list['tbJoin'])) {
                $this->list['tbJoin'] = json_decode($this->list['tbJoin'], true);
            }
            if (!empty($this->list['tbJoin'])) {
                $temps = [];
                foreach ($this->list['tbJoin'] as $item) {
                    $temps[] = "{$item['join']} `{$item['tbName']}` {$item['alias']} on {$item['on']}";
                }
                $zeroSelectorData['tbJoin'] = $temps;
            }
            if (!empty($this->list['tbOrder'])) {
                $zeroSelectorData['tbOrder'] = $this->list['tbOrder'];
            }
        } else {
            $zeroSelectorData['sqlTemplate'] = $this->list['sqlTemplate'];
        }

        $this->out[] = '';
        $this->out[] = '    //为ZeroController所需的自动查询器';
        $this->out[] = '    protected function zeroSelector(){ ';

        $this->use('beacon\SqlSelector');
        $tbAlias = isset($zeroSelectorData['tbAlias']) ? trim($zeroSelectorData['tbAlias']) : null;
        $tbName = trim($zeroSelectorData['tbName']);
        if (empty($tbAlias)) {
            $this->out[] = '        $selector = new SqlSelector(' . var_export($tbName, true) . ');';
        } else {
            $this->out[] = '        $selector = new SqlSelector(' . var_export($tbName, true) . ' , ' . var_export($tbAlias, true) . ');';
        }
        if (!empty($zeroSelectorData['sqlTemplate'])) {
            $this->out[] = '        $param = $this->param();';
            $this->out[] = '        $selector->setTemplate(' . var_export(trim($zeroSelectorData['sqlTemplate']), true) . ', $param);';
        } else {

            $tbField = isset($zeroSelectorData['tbField']) ? trim($zeroSelectorData['tbField']) : null;
            if (!empty($tbField)) {
                $this->out[] = '        $selector->field(' . var_export($tbField, true) . ');';
            }
            $tbJoin = isset($zeroSelectorData['tbJoin']) ? $zeroSelectorData['tbJoin'] : [];
            //join 表
            if (count($tbJoin) > 0) {
                foreach ($zeroSelectorData['tbJoin'] as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $this->out[] = '        $selector->join(' . var_export($item, true) . ');';
                    }
                }
            }

            $sortItem = [];
            $fields = Helper::convertArray($this->list['fields'], []);
            foreach ($fields as $field) {
                if (!empty($field['orderName'])) {
                    $sortItem[] = $field['orderName'];
                }
            }
           // Logger::log('sortItem', $sortItem);
            //排序
            if (count($sortItem)) {
                $this->out[] = '        //自动按列设置排序';
                $this->out[] = '        $tempSort = $this->param(\'sort:s\');';
                $this->out[] = '        if (preg_match(\'@^(\w+)-(asc|desc)$@\', $tempSort, $match)) {';
                $this->out[] = '            if (in_array($match[1], ' . Helper::export($sortItem, 'c') . ')) {';
                $this->out[] = '                $selector->order($match[1] . \' \' . $match[2]);';
                $this->out[] = '            }';
                $this->out[] = '        }';
            }
            $tbOrder = isset($zeroSelectorData['tbOrder']) ? trim($zeroSelectorData['tbOrder']) : null;
            if (!empty($tbOrder)) {
                $this->out[] = '        $selector->order(' . var_export($tbOrder, true) . ');';
            }
        }
        $this->out[] = '        $this->zeroWhere($selector);';
        $this->out[] = '        return $selector;';
        $this->out[] = '    }';

    }

    //创建获取数据条数函数的方法
    private function createZeroCountMethod()
    {
        $zeroSelectorData = [];
        $zeroSelectorData['tbName'] = '@pf_' . trim($this->list['tbName']);
        if ($this->list['tbAlias']) {
            $zeroSelectorData['tbAlias'] = $this->list['tbAlias'];
        }
        if ($this->list['sqlCountTemplate']) {
            $zeroSelectorData['sqlCountTemplate'] = $this->list['sqlCountTemplate'];
        }
        $tbAlias = isset($zeroSelectorData['tbAlias']) ? trim($zeroSelectorData['tbAlias']) : null;
        $tbName = trim($zeroSelectorData['tbName']);
        //生成 where查询行数
        if (!empty($zeroSelectorData['sqlCountTemplate'])) {
            $this->out[] = '';
            $this->use('beacon\SqlSelector');
            $this->out[] = '    //为ZeroController 模板查询数量';
            $this->out[] = '    protected function zeroCount(){ ';
            $this->out[] = '        $param = $this->param();';
            if (empty($tbAlias)) {
                $this->out[] = '        $selector = new SqlSelector(' . var_export($tbName, true) . ');';
            } else {
                $this->out[] = '        $selector = new SqlSelector(' . var_export($tbName, true) . ' , ' . var_export($tbAlias, true) . ');';
            }
            $this->out[] = '        $selector->setTemplate(' . var_export(trim($zeroSelectorData['sqlTemplate']), true) . ', $param);';
            $this->out[] = '        $this->zeroWhere($selector);';
            $this->out[] = '        return $selector->getOne();';
            $this->out[] = '    }';
        }
    }

    private function createZeroSearchMethod()
    {
        $sCount = DB::getOne('select count(1) from @pf_tool_search where listId=?', $this->list['id']);
        if ($sCount && $sCount > 0) {
            $className = $this->classKeyName;
            $searchForm = trim($this->appSpace . '\\zero\\search\\Zero' . $className . 'Search');
            $this->use($searchForm);
            $temp = explode('\\', $searchForm);
            $searchForm = end($temp);
            $this->out[] = '    //注册搜索表单数据';
            $this->out[] = '    protected function zeroForSearch(){ ';
            $this->out[] = '        $search = new ' . $searchForm . '();';
            $this->out[] = '        $this->assign(\'search\',$search);';
            $this->out[] = '        return $search;';
            $this->out[] = '    }';
        } else {
            $this->out[] = '    //注册搜索表单数据';
            $this->out[] = '    protected function zeroForSearch(){';
            $this->out[] = '        return null;';
            $this->out[] = '    }';
        }
    }

    //创建控制器类
    private function createClass()
    {
        $className = $this->className;
        $baseController = $this->baseController;
        $this->out[] = "class {$className} extends " . $baseController;
        $this->out[] = '{';

        $this->out[] = '    public function indexAction(){';
        $this->out[] = '        return parent::indexAction();';
        $this->out[] = '    }';

        $zeroConfig = $this->createZeroLoadMethod();
        $this->createZeroWhereMethod($zeroConfig);
        $this->createZeroSelectorMethod();
        $this->createZeroCountMethod();
        $this->createZeroSearchMethod();
        //公开勾选的方法
        if (Utils::isJson($this->list['actions'])) {
            $actions = json_decode($this->list['actions'], true);
            foreach ($actions as $action) {
                $this->out[] = '';
                $this->out[] = '    //公开 ' . $action . ' 方法';
                $this->out[] = '    public function ' . $action . 'Action(){';
                $this->out[] = '        return parent::' . $action . 'Action();';
                $this->out[] = '    }';
            }
        }
        $this->out[] = '}';
    }

    //获取生成的代码
    public function getCode()
    {
        $code = [];
        $code[] = '<?php';
        $code[] = '';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';
        $code[] = '/**';
        $code[] = '* ' . $this->list['title'];
        $code[] = '* Created by Beacon AI Tool 2.1.';
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
            $path = Utils::path(ROOT_DIR, $this->namespace);
            Utils::makeDir($path);
            $code = $this->getCode();
            file_put_contents(Utils::path($path, $this->className . '.php'), $code);
        }
    }

    public static function make(int $listId = 0)
    {
        $maker = new MakeController($listId);
        $maker->makeFile();
        MakeListTemplate::make($listId);
    }

}