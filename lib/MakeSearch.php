<?php

namespace tool\lib;

use beacon\DB;
use beacon\Utils;

class MakeSearch
{
    private $appId = 0;
    private $list = null;
    private $fields = null;
    private $namespace = null;
    private $_use = [];
    private $out = [];
    private $classFullName = '';
    private $className = '';

    private static $remove = ['id', 'name', 'listId', 'sort', 'tbWhere', 'tbWhereType'];
    private static $bool = ['close', 'viewClose', 'offEdit', 'hideBox', 'viewAsterisk', 'dataValDisabled'];
    private static $integer = ['viewMerge'];
    private static $array = ['dataValRule', 'dataValMessage', 'dynamic'];

    public function __construct(int $listId = 0, string $namespace = null)
    {
        $this->list = DB::getRow('select * from @pf_tool_list where id=?', $listId);
        if ($this->list == null) {
            throw new \Exception('生成错误');
        }
        $this->appId = $this->list['appId'];
        if (isset($this->list['withSearch']) && $this->list['withSearch'] == 1) {
            $this->fields = DB::getList('select * from @pf_tool_search where listId=? order by sort asc', $listId);
            if (empty($namespace)) {
                $this->namespace = trim($this->list['namespace'], '\\');
            } else {
                $temp = str_replace('/', '\\', $namespace);
                $this->namespace = trim($temp, '\\');
            }
            $this->namespace = $this->namespace . '\\zero\\search';
            $className = 'Zero' . $this->list['key'] . 'Search';
            $this->className = $className;
            $this->classFullName = $this->namespace . '\\' . $className;
            if (count($this->fields) > 0) {
                $this->createClass();
            }
        }
    }

    public function use(string $name)
    {
        $this->_use[$name] = $name;
    }

    //导出默认值
    private function exportDefault($default = '', $varType = 'string')
    {
        if (!Utils::isJson($default)) {
            return null;
        }
        $data = json_decode($default, true);
        switch (intval($data['type'])) {
            case 1:
                if ($varType == 'int') {
                    return intval($data['value']);
                } else if ($varType == 'bool') {
                    return boolval($data['value']);
                } else if ($varType == 'float') {
                    return floatval($data['value']);
                }
                return $data['value'];
            case 2:
                return Helper::convertArray($data['json'], []);
            case 3:
                if ($data['inner'] == 'date') {
                    $value = new CodeItem();
                    $code = 'function(){ return date(\'Y-m-d\');}';
                    $value->setCode($code);
                } elseif ($data['inner'] == 'datetime') {
                    $value = new CodeItem();
                    $code = 'function(){ return date(\'Y-m-d H:i:s\');}';
                    $value->setCode($code);
                } elseif ($data['inner'] == 'maxSort') {
                    $value = new CodeItem();
                    $value->use('beacon\DB');
                    $code = 'function(){ return DB::getMax($this->tbName,\'sort\');}';
                    $value->setCode($code);
                } elseif ($data['inner'] == 'minSort') {
                    $value = new CodeItem();
                    $value->use('beacon\DB');
                    $code = 'function(){ return  DB::getMin($this->tbName,\'sort\');}';
                    $value->setCode($code);
                } else {
                    return null;
                }
                return $value;
            case 4:
                $data['param'] = trim($data['param']);
                if (empty($data['param'])) {
                    return null;
                }
                $value = new CodeItem();
                $value->use('beacon\Request');
                if ($data['method'] == 'post') {
                    $code = 'Request::post(' . var_export($data['param'], true) . ')';
                } elseif ($data['method'] == 'get') {
                    $code = 'Request::get(' . var_export($data['param'], true) . ')';
                } elseif ($data['method'] == 'req') {
                    $code = 'Request::param(' . var_export($data['param'], true) . ')';
                } else {
                    return null;
                }
                $value->setCode($code);
                return $value;
            case 5:
                $value = new CodeItem();
                $code = [];
                $code[] = 'function(){';
                $method = '';
                $data['args'] = trim($data['args']);
                if (!empty($data['args'])) {
                    $value->use('beacon\Request');
                    $param = explode(',', $data['args']);
                    if ($data['method'] == 'post') {
                        $method = 'post';
                    } elseif ($data['method'] == 'get') {
                        $method = 'get';
                    } elseif ($data['method'] == 'req') {
                        $method = 'param';
                    }
                    $code[] = '    $param=[];';
                    foreach ($param as $item) {
                        $item = trim($item);
                        $code[] = '    $param[]= Request::' . $method . '(' . var_export($item, true) . ');';
                    }
                    $code[] = '    return DB::getOne(' . var_export(trim($data['sql']), true) . ',$param);';
                } else {
                    $code[] = '    return DB::getOne(' . var_export(trim($data['sql']), true) . ');';
                }
                $code[] = '}';
                $value->setCode(join("\n", $code));
                return $value;
            case 6:
                if (!empty($data['func'])) {
                    $value = new CodeItem();
                    $code = 'function(){ if(is_callable(' . var_export($data['func'], true) . ')){return ' . $data['func'] . '();} return null;}';
                    $value->setCode($code);
                    return $value;
                } else {
                    return null;
                }
            default:
                return null;
        }
    }

    //导出字段
    private function exportField($field)
    {
        //专有扩展属性
        if (!empty($field['extend'])) {
            $extend = Helper::convertArray($field['extend'], []);
            if ($field) {
                $type = $field['type'];
                $typeClass = Helper::getWidgetClassName($type);
                if (class_exists($typeClass) && is_callable($typeClass . '::export')) {
                    call_user_func_array($typeClass . '::export', [&$field, $extend]);
                }
            }
            unset($field['extend']);
        }
        //添加BOX属性
        if (!empty($field['boxAttrs'])) {
            $boxAttrs = Helper::convertArray($field['boxAttrs'], []);
            foreach ($boxAttrs as $item) {
                $field['box' . Utils::toCamel($item['name'])] = $item['value'];
            }
            unset($field['boxAttrs']);
        }
        //自定义属性
        if (!empty($field['custom'])) {
            $custom = json_decode($field['custom'], true);
            foreach ($custom as $item) {
                $nKey = lcfirst(Utils::toCamel($item['name']));
                switch ($item['type']) {
                    case 'int';
                        $field[$nKey] = intval($item['value']);
                        break;
                    case 'float';
                        $field[$nKey] = floatval($item['value']);
                        break;
                    case 'bool';
                        $field[$nKey] = boolval($item['value']);
                        break;
                    case 'array';
                        $value = Helper::convertArray($item['value']);
                        $field[$nKey] = $value;
                        break;
                    default:
                        $field[$nKey] = $item['value'];
                        break;
                }
            }
            unset($field['custom']);
        }
        $out = [];
        $out[] = '                ' . var_export($field['name'], true) . ' => [';
        foreach ($field as $key => $value) {
            if (in_array($key, self::$remove)) {
                continue;
            }
            if (in_array($key, self::$bool)) {
                $value = boolval($value);
            }
            if (in_array($key, self::$integer)) {
                $value = intval($value);
            }
            if (in_array($key, self::$array)) {
                $value = Helper::convertArray($value);
            }
            if ($key == 'default') {
                $value = $this->exportDefault($value, $field['varType']);
                if (($value === null || $value === '')) {
                    continue;
                }
            } elseif (empty($value)) {
                continue;
            }
            $key = Utils::camelToAttr($key);
            if ($value instanceof CodeItem) {
                $code = $value->getCode();
                $code = join("\n                    ", explode("\n", $code));
                $out[] = '    ' . var_export($key, true) . ' => ' . $code . ',';
                foreach ($value->getUse() as $use) {
                    $this->use($use);
                }
            } else {
                if (is_array($value)) {
                    $out[] = '    ' . var_export($key, true) . ' => ' . Helper::export($value, '                        ') . ',';
                } else {
                    $out[] = '    ' . var_export($key, true) . ' => ' . var_export($value, true) . ',';
                }
            }

        }
        $out[] = '],';
        return $out;
    }

    private function createClass()
    {
        $this->use('beacon\Form');
        $this->out[] = "class {$this->className} extends Form";
        $this->out[] = '{';
        $this->out[] = '    public $title=' . var_export($this->list['title'], true) . ';';
        //加载函数代码
        $fields = $this->fields;
        $this->out[] = '';
        $this->out[] = '    protected function load(){';
        $this->out[] = '        return [';
        foreach ($fields as $item) {
            $field = self::exportField($item);
            $this->out[] = join("\n                ", $field);
        }
        $this->out[] = '        ];';
        $this->out[] = '    }';
        //class 闭合
        $this->out[] = '}';
    }

    public function getCode()
    {
        $code = [];
        $code[] = '<?php';
        $code[] = '';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';
        $code[] = '/**';
        $code[] = '* ' . $this->list['title'];
        $code[] = '* Created by Beacon AI Tool2.1.';
        $code[] = '* User: wj008';
        $code[] = '* Web: www.wj008.net';
        $code[] = '* Date: ' . date('Y/m/d');
        $code[] = '* Time: ' . date('H:i:s');
        $code[] = '* 注意：该代码由工具生成，不要在此处修改任何代码，将会被覆盖，如要修改请在应用 form目录中创建同名类并继承该生成类进行调整';
        $code[] = '*/';
        $code[] = '';
        foreach ($this->_use as $item) {
            $code[] = 'use ' . $item . ';';
        }
        $code[] = '';
        $code[] = join("\n", $this->out);
        return join("\n", $code);
    }

    public function makeFile()
    {
        if (isset($this->list['withSearch']) && $this->list['withSearch'] == 1) {
            if (count($this->fields) > 0) {
                $rootDir = ROOT_DIR;
                $app = DB::getRow('select dirName from @pf_tool_app where id=?', $this->appId);
                if ($app && !empty($app['dirName'])) {
                    if (is_dir($app['dirName'])) {
                        $rootDir = $app['dirName'];
                    }
                }
                $path = Utils::path($rootDir, $this->namespace);
                Utils::makeDir($path);
                $code = $this->getCode();
                file_put_contents(Utils::path($path, $this->className . '.php'), $code);
            }
        }
    }

    public static function make(int $listId = 0)
    {
        $maker = new MakeSearch($listId);
        $maker->makeFile();
        MakeController::make($listId);
    }

}