<?php


namespace tool\libs;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\core\Util;


class MakeModel
{
    private int $appId = 0;
    private array|null $form;
    private array|null $fields = null;
    private string $namespace = '';
    private array $_use = [];
    /**
     * @var FuncItem[]
     */
    private array $_func = [];
    private array $out = [];
    private string $className = '';
    private string $template = '';

    /**
     * ModelMaker constructor.
     * @param int $formId
     * @param string|null $namespace
     * @throws DBException
     * @throws \Exception
     */
    public function __construct(int $formId = 0, string $namespace = '')
    {
        $this->form = DB::getRow('select * from @pf_tool_form where id=?', $formId);
        if ($this->form == null) {
            throw new \Exception('生成错误');
        }
        $this->appId = intval($this->form['appId']);
        if (intval($this->form['withForm']) != 1) {
            return;
        }

        if (empty($namespace)) {
            $this->namespace = Helper::fixNamespace($this->form['namespace']);
        } else {
            $this->namespace = Helper::fixNamespace($namespace);
        }
        if ($this->form['extMode'] == 1) {
            $this->namespace = $this->namespace . '\\zero\\plugin';
            $className = $this->form['key'] . 'Plugin';
            $this->template = 'plugin/' . Util::toUnder($this->form['key']) . '.plugin.tpl';
        } else {
            $this->namespace = $this->namespace . '\\zero\\model';
            $className = $this->form['key'] . 'Model';
            $this->template = 'form/' . Util::toUnder($this->form['key']) . '.form.tpl';
        }
        $this->className = $className;
        $this->form['template'] = trim($this->form['template']);
        if (!empty($this->form['template'])) {
            $this->template = $this->form['template'];
        }
        $this->fields = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $formId);
        $this->createModel();
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

    public function func(FuncItem $item)
    {
        $name = $item->getName();
        $this->_func[$name] = $item;
    }

    //导出默认值
    private function exportDefault(array &$field)
    {

        $default = $field['default'];
        $varType = $field['varType'];
        if (!Util::isJson($default)) {
            return;
        }
        $data = json_decode($default, true);
        $type = intval($data['type']);
        switch ($type) {
            case 1:
                if ($data['value'] === null || $data['value'] === '') {
                    switch ($varType) {
                        case 'int':
                        case 'bool':
                        case 'float':
                            $field['default'] = null;
                            $field['varType'] = '?' . $varType;
                            break;
                        case 'array':
                            $field['default'] = [];
                            break;
                        default:
                            $field['default'] = '';
                            break;
                    }
                } else {
                    switch ($varType) {
                        case 'int':
                            $field['default'] = intval($data['value']);
                            break;
                        case 'bool':
                            $field['default'] = boolval($data['value']);
                            break;
                        case 'float':
                            $field['default'] = floatval($data['value']);
                            break;
                        case 'array':
                            $field['default'] = Helper::convertArray($data['value']);
                            break;
                        default:
                            $field['default'] = $data['value'];
                            break;
                    }
                }
                break;
            case 2:
                $field['default'] = Helper::convertArray($data['json'], []);
                break;
            case 4:
                if ($data['inner'] == 'date') {
                    $func = new FuncItem('currentDate');
                    $func->setCode('/**
    * 获取当前日期
    * @return string
    */
    public static function currentDate():string {
        return date(\'Y-m-d\');
    }');
                    $field['defaultFunc'] = $func;
                } elseif ($data['inner'] == 'datetime') {
                    $func = new FuncItem('currentDatetime');
                    $func->setCode('    /**
    * 获取当前时间
    * @return string
    */
    public static function currentDatetime():string {
        return date(\'Y-m-d H:i:s\');
    }');
                    $field['defaultFunc'] = $func;
                } elseif ($data['inner'] == 'maxSort') {
                    $func = new FuncItem('getMaxSort');
                    $func->use('beacon\core\DB');
                    $table = Helper::var('@pf_' . $this->form['tbName']);
                    $func->setCode('    /**
    * 获取当前表最大排序值
    * @return int
    * @throws \beacon\core\DBException
    */
    public static function getMaxSort():int {
        $value = DB::getMax(' . $table . ',\'sort\');
        if($value === null){
             return 10;
        }
        return intval($value)+10;
    }');
                    $field['defaultFunc'] = $func;
                } elseif ($data['inner'] == 'minSort') {
                    $func = new FuncItem('getMinSort');
                    $func->use('beacon\core\DB');
                    $table = Helper::var('@pf_' . $this->form['tbName']);

                    $func->setCode('    /**
    * 获取当前表最小排序值
    * @return int
    * @throws \beacon\core\DBException
    */
    public static function getMinSort():int {
        $value = DB::getMin(' . $table . ',\'sort\');
        if($value === null){
             return 0;
        }
        return intval($value)-10;
    }');
                    $field['defaultFunc'] = $func;
                }
                $field['default'] = null;
                $field['varType'] = '?' . $varType;
                break;
            case 3:
                $data['param'] = trim($data['param']);
                if (!empty($data['param'])) {
                    $field['defaultFromParam'] = $data['param'];
                }
                $field['default'] = null;
                $field['varType'] = '?' . $varType;
                break;
            case 5:
                $data['func'] = trim($data['func']);
                if (!empty($data['func'])) {
                    $field['defaultFunc'] = $data['func'];
                }
                $field['default'] = null;
                $field['varType'] = '?' . $varType;
                break;
            default:
                break;
        }
    }


    private function addField(array &$field): string
    {
        $dbType = $field['dbType'];
        $dbField = boolval($field['dbField']);
        unset($field['id']);
        unset($field['formId']);
        unset($field['dbField']);
        unset($field['dbType']);
        unset($field['dbLen']);
        unset($field['dbPoint']);
        unset($field['dbDefType']);
        unset($field['dbComment']);
        unset($field['dbUnique']);
        unset($field['sort']);
        $field['validRule'] = Helper::convertArray($field['validRule']);
        $field['validGroup'] = Helper::convertArray($field['validGroup']);
        $field['close'] = boolval($field['close']);
        $field['viewClose'] = boolval($field['viewClose']);
        $field['hidden'] = boolval($field['hidden']);
        $field['offEdit'] = boolval($field['offEdit']);
        $field['validDisabled'] = boolval($field['validDisabled']);
        $field['star'] = boolval($field['star']);
        $field['viewMerge'] = intval($field['viewMerge']);

        //处理扩展字段
        if (!empty($field['extend'])) {
            $extend = Helper::convertArray($field['extend'], []);
            $typeClass = Helper::getSupportClassName($field['type']);
            if (!empty($typeClass)) {
                $object = new $typeClass();
                if (is_callable([$object, 'export'])) {
                    $form = Form::create($object, 'edit');
                    $form->setData($extend);
                    $extend = $object->export();
                    foreach ($extend as $key => $value) {
                        $field[$key] = $value;
                    }
                }
            }
        }
        unset($field['extend']);

        if (!empty($field['names'])) {
            $field['names'] = Helper::convertArray($field['names']);
            $names = [];
            foreach ($field['names'] as $item) {
                if (is_string($item)) {
                    $names[] = $item;
                } else if (is_array($item) && isset($item['field'])) {
                    $names[] = $item['field'];
                }
            }
            $field['names'] = $names;
        }

        //字段属性
        $field['attrs'] = Helper::convertArray($field['attrs']);
        $attrs = [];
        foreach ($field['attrs'] as $item) {
            if (!empty($item['name'])) {
                $attrs[$item['name']] = $item['value'] ?? '';
            }
        }
        $field['attrs'] = $attrs;
        if (!empty($field['attrClass'])) {
            $field['attrs']['class'] = $field['attrClass'];
        }
        unset($field['attrClass']);
        if (!empty($field['attrStyle'])) {
            $field['attrs']['style'] = $field['attrStyle'];
        }
        unset($field['attrStyle']);
        if (!empty($field['attrPlaceholder'])) {
            $field['attrs']['placeholder'] = $field['attrPlaceholder'];
        }
        unset($field['attrPlaceholder']);
        //转换动态
        if (!empty($field['dynamic'])) {
            $dynamic = Helper::convertArray($field['dynamic']);
            $temp = [];
            foreach ($dynamic as $item) {
                $data = [];
                $name = $item['name'] ?? '';
                $value = $item['value'] ?? '';
                $d1 = $item['d1'] ?? '';
                $d2 = $item['d2'] ?? '';
                $r1 = $item['r1'] ?? '';
                $r2 = $item['r2'] ?? '';
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
        //字段类型
        if ((!$dbField) || $dbType == 'none') {
            $field['offJoin'] = true;
        }
        //字段类型
        if (empty($field['varType']) && !empty($dbType)) {
            switch ($dbType) {
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

        $this->exportDefault($field);
        $varType = $field['varType'];
        $name = $field['name'];
        $default = $field['default'];
        $type = $field['type'];
        unset($field['varType']);
        unset($field['name']);
        unset($field['default']);
        unset($field['type']);

        $attr = [];
        foreach ($field as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if ($value instanceof FuncItem) {
                $attr[] = "        {$key}: [self::class, '{$value->getName()}']";
                $this->func($value);
                $this->use($value->getUse());
            } else {
                $attr[] = "        {$key}: " . Helper::var($value, '        ');
            }
        }
        $code = [];
        $this->use('beacon\\widget\\' . $type);
        $code[] = '    #[' . $type . '(';
        $code[] = join(",\n", $attr);
        $code[] = "    )]";

        $default = Helper::var($default);
        if ($default == 'NULL') {
            $default = 'null';
        }
        $code[] = "    public {$varType} \${$name} = " . $default . ';';
        $code[] = "";
        return join("\n", $code);
    }


    private function createModel()
    {

        $this->use('beacon\core\Form');

        $title = Helper::var($this->form['title']);
        $table = Helper::var('@pf_' . $this->form['tbName']);
        $template = Helper::var($this->template);

        $this->out[] = "#[Form(title: {$title}, table: {$table}, template: {$template})]";
        $this->out[] = "class {$this->className}";
        $this->out[] = '{';
        foreach ($this->fields as $item) {
            $this->out[] = self::addField($item);
        }
        $this->out[] = '';
        foreach ($this->_func as $item) {
            $this->out[] = '';
            $this->out[] = $item->getCode();
        }
        //class 闭合
        $this->out[] = '}';
    }


    public function getCode(): string
    {
        if (intval($this->form['withForm']) != 1) {
            return '';
        }
        $code = [];
        $code[] = '<?php';
        $code[] = '';
        $code[] = 'namespace ' . $this->namespace . ';';
        $code[] = '';

        foreach ($this->_use as $item) {
            $code[] = 'use ' . $item . ';';
        }
        $code[] = '';

        $code[] = '/**';
        $code[] = '* ' . $this->form['title'];
        $code[] = '* Created by Beacon AI Tool4.0.';
        $code[] = '* User: wj008';
        $code[] = '* Web: www.wj008.net';
        $code[] = '* Date: ' . date('Y/m/d');
        $code[] = '* Time: ' . date('H:i:s');
        $code[] = '* 注意：该代码由工具生成，不要在此处修改任何代码，将会被覆盖，如要修改请在应用 model 目录中创建同名类并继承该生成类进行调整';
        $code[] = "* Class {$this->className}";
        $code[] = "* @package {$this->namespace}";
        $code[] = '*/';
        $code[] = '';

        $code[] = join("\n", $this->out);
        return join("\n", $code);

    }


    public function makeFile()
    {
        if (intval($this->form['withForm']) != 1) {
            return;
        }
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

    public static function make(int $formId = 0)
    {
        $maker = new static($formId);
        $maker->makeFile();
        MakeFormTemplate::make($formId);
    }
}