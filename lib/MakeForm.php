<?php

namespace tool\lib;

use beacon\DB;
use beacon\Request;
use beacon\Utils;

class MakeForm
{
    private $appId = 0;
    private $form = null;
    private $fields = null;
    private $namespace = null;
    private $_use = [];
    private $out = [];
    private $classFullName = '';
    private $className = '';
    private $template = '';

    private static $remove = ['id', 'name', 'formId', 'dbfield', 'dbtype', 'dblen', 'dbpoint', 'dbcomment', 'sort'];
    private static $bool = ['close', 'viewClose', 'offEdit', 'hideBox', 'viewAsterisk', 'dataValDisabled'];
    private static $integer = ['viewMerge'];
    private static $array = ['dataValRule', 'dataValMessage', 'dynamic'];

    public function __construct(int $formId = 0, string $namespace = null)
    {
        $this->form = DB::getRow('select * from @pf_tool_form where id=?', $formId);
        if ($this->form == null) {
            throw new \Exception('生成错误');
        }
        $this->appId = intval($this->form['appId']);
        if (isset($this->form['withForm']) && $this->form['withForm'] == 1) {
            $this->fields = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $formId);
            if (empty($namespace)) {
                $this->namespace = trim($this->form['namespace'], '\\');
            } else {
                $temp = str_replace('/', '\\', $namespace);
                $this->namespace = trim($temp, '\\');
            }
            if ($this->form['extMode'] == 1) {
                $this->namespace = $this->namespace . '\\zero\\plugin';
                $className = 'Zero' . $this->form['key'] . 'Plugin';
                $this->template = 'plugin/Zero' . $this->form['key'] . '.plugin.tpl';
            } else {
                $this->namespace = $this->namespace . '\\zero\\form';
                $className = 'Zero' . $this->form['key'] . 'Form';
                $this->template = 'form/Zero' . $this->form['key'] . '.form.tpl';
            }
            $this->className = $className;
            $this->classFullName = $this->namespace . '\\' . $className;
            $this->form['template'] = trim($this->form['template']);
            if (!empty($this->form['template'])) {
                $this->template = $this->form['template'];
            }
            $this->createClass();
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
                if ($data['value'] === null || $data['value'] === '') {
                    return $data['value'];
                }
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
                    $code = 'function(){ return intval(DB::getMax($this->tbName,\'sort\'))+10;}';
                    $value->setCode($code);
                } elseif ($data['inner'] == 'minSort') {
                    $value = new CodeItem();
                    $value->use('beacon\DB');
                    $code = 'function(){ return  intval(DB::getMin($this->tbName,\'sort\'))-10;}';
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
                $name = ucfirst(Utils::attrToCamel($item['name']));
                $field['box' . $name] = $item['value'];
            }
            unset($field['boxAttrs']);
        }
        //自定义属性
        if (!empty($field['custom'])) {
            $custom = json_decode($field['custom'], true);
            foreach ($custom as $item) {
                $nKey = lcfirst(Utils::attrToCamel($item['name']));
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
        //转换动态
        if (!empty($field['dynamic'])) {
            $dynamic = Helper::convertArray($field['dynamic']);
            $temp = [];
            foreach ($dynamic as $item) {
                $data = [];
                $name = isset($item['name']) ? $item['name'] : '';
                $value = isset($item['value']) ? $item['value'] : '';
                $d1 = isset($item['d1']) ? $item['d1'] : '';
                $d2 = isset($item['d2']) ? $item['d2'] : '';
                $r1 = isset($item['r1']) ? $item['r1'] : '';
                $r2 = isset($item['r2']) ? $item['r2'] : '';
                if (empty($name)) {
                    continue;
                }
                $data[$name] = $value;
                if (!empty($d1) && !empty($r1)) {
                    $data[$d1] = $r1;
                }
                if (!empty($d2) && !empty($r2)) {
                    $data[$d2] = $r2;
                }
                $temp[] = $data;
            }
            $field['dynamic'] = $temp;
        }

        $out = [];
        $out[] = '                ' . var_export($field['name'], true) . ' => [';
        if ((!$field['dbfield']) || $field['dbtype'] == 'none') {
            $field['offSave'] = true;
        }

        if (empty($field['varType'])) {
            if (isset($field['dbtype'])) {
                switch ($field['dbtype']) {
                    case 'int':
                        $field['varType'] = 'int';
                        break;
                    case 'tinyint':
                        $field['varType'] = 'bool';
                        break;
                    case 'float':
                    case 'decimal':
                    case 'double':
                        $field['varType'] = 'float';
                        break;
                    case 'json':
                        $field['varType'] = 'array';
                        break;
                    default:
                        $field['varType'] = 'string';
                        break;
                }
            }
        }

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

        if (intval($field['unique'])) {
            $this->use('beacon\Request');
            $this->use('beacon\DB');
            $out[] = '    ' . var_export('valid-func', true) . ' => function ($v){';
            $out[] = '        $id = Request::param(\'id:i\', 0);';
            $out[] = '        $row = DB::getRow(\'select id from `@pf_' . $this->form['tbName'] . '` where `' . $field['name'] . '`=? and id<>?\', [$v, $id]);';
            $out[] = '        if($row){ return ' . var_export($field['remoteError'], true) . ';}';
            $out[] = '        return null;';
            $out[] = '     },';
        }
        if (intval($field['remoteUrl'])) {
            $out[] = '    ' . var_export('yee-module', true) . ' => \'remote\'';
            $out[] = '    ' . var_export('data-url', true) . ' =>Route::url(' . var_export($field['remoteUrl'], true) . ')';
        }
        $out[] = '],';
        return $out;
    }

    private function createClass()
    {
        $this->use('beacon\Form');
        $this->out[] = "class {$this->className} extends Form";
        $this->out[] = '{';
        $this->out[] = '    public $title=' . var_export($this->form['title'], true) . ';';
        if ($this->form['extMode'] != 1) {
            $this->out[] = '    public $tbName=' . var_export('@pf_' . $this->form['tbName'], true) . ';';
        }
        $this->out[] = '    public $template=' . var_export($this->template, true) . ';';

        //构造函数
        if ($this->form['extMode'] != 1) {
            $this->out[] = '';
            $this->out[] = '    public function __construct(string $type = \'\'){';
            $this->out[] = '        parent::__construct($type);';
            $this->out[] = '        if($this->isEdit()){';
            if ($this->form['extMode'] != 1) {
                $this->use('beacon\Request');
                $this->out[] = '            $this->addHideBox(\'id\', Request::get(\'id:i\', 0));';
            }
            $this->out[] = '        }';
            $this->out[] = '    }';
        }
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
        $code[] = '* ' . $this->form['title'];
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
        if (isset($this->form['withForm']) && $this->form['withForm'] == 1) {
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

    public static function make(int $formId = 0)
    {
        $maker = new MakeForm($formId);
        $maker->makeFile();
        MakeFormTemplate::make($formId);
    }

}