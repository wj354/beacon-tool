<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 2018/2/19
 * Time: 13:33
 */

namespace tool\widget;


use beacon\Form;
use tool\lib\CodeItem;
use tool\lib\Helper;
use tool\plugin\Option;

class RadioGroup extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
            'type' => [
                'label' => '选项类型',
                'type' => 'radio-group', // 单选组
                'options' => [
                    [1, '直填值'],
                    [2, 'SQL查询'],
                    [3, '混合'],
                    [4, '配置项'],
                    [5, 'PHP函数'],
                ],
                'default' => 1,
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'options',
                        'hide' => 'method,param,sql,field,config,func',
                    ],
                    [
                        'eq' => 2,
                        'show' => 'method,param,sql,field',
                        'hide' => 'options,config,func',
                    ],
                    [
                        'eq' => 3,
                        'show' => 'options,method,param,sql,field',
                        'hide' => 'config,func',
                    ],
                    [
                        'eq' => 4,
                        'show' => 'config',
                        'hide' => 'options,method,param,sql,field,func',
                    ],
                    [
                        'eq' => 5,
                        'show' => 'func',
                        'hide' => 'options,method,param,sql,field,config',
                    ],
                ],
            ],
            'options' => [
                'label' => '选项 [options]',
                'type' => 'container',
                'mode' => 'multiple',
                'plug-name' => Option::class,
            ],
            'method' => [
                'label' => '请求方式',
                'type' => 'select',
                'options' => [
                    ['req', 'REQUEST'],
                    ['get', 'GET'],
                    ['post', 'POST'],
                ],
            ],
            'param' => [
                'label' => '参数字段',
                'type' => 'text',
                'tips' => '多个请用,隔开',
                'box-placeholder' => '如：id:i,name:s',
                'box-style' => 'width:220px;',
                'viewMerge' => -1,
            ],
            'sql' => [
                'label' => '查询语句(表名)',
                'type' => 'textarea',
                'box-placeholder' => '如：select id,name from @pf_table where pid=? and type=? 或者 @pf_table',
            ],
            'field' => [
                'label' => '查询字段',
                'box-style' => 'width:320px;',
                'box-placeholder' => '如：id,name',
                'tips' => '多个请用,隔开,如果为空，第1项作为值，第2项作为文本'
            ],
            'config' => [
                'label' => '配置项名称',
                'type' => 'text',
                'box-style' => 'width:200px;',
                'box-placeholder' => '如：mycfg.option',
                'tips' => '从配置文件中读取选项信息',
            ],
            'func' => [
                'label' => 'PHP函数',
                'type' => 'text',
                'box-style' => 'width:260px;',
                'box-placeholder' => '如:\\lib\\MyClass::MyFunc',
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {

        $type = isset($extend['type']) ? intval($extend['type']) : 1;
        $options = [];
        //填值 1,3
        if ($type == 1 || $type == 3) {
            if (isset($extend['options'])) {
                $options = Helper::convertArray($extend['options'], []);
            }
        }
        //查询sql 2,3
        $extend['sql'] = isset($extend['sql']) ? trim($extend['sql']) : '';
        if (($type == 2 || $type == 3) && !empty($extend['sql'])) {
            $code = [];
            $codeItem = new CodeItem();
            $code[] = 'function(){';
            if ($type == 3) {
                $code[] = '    $options=' . var_export($options, true) . ';';
            } else {
                $code[] = '    $options=[];';
            }
            if (preg_match('/^@pf_(\w+)$/', $extend['sql'])) {
                if (!empty($extend['field'])) {
                    $dbFields = explode(',', $extend['field']);
                    foreach ($dbFields as &$item) {
                        $item = '`' . trim($item) . '`';
                    }
                    $extend['sql'] = 'select ' . join(',', $dbFields) . ' form `' . $extend['sql'] . '`';
                } else {
                    $extend['sql'] = 'select * from `' . $extend['sql'] . '`';
                }
            }
            if (!empty($extend['method']) && !empty($extend['param'])) {
                $codeItem->use('beacon\Request');
                $codeItem->use('beacon\DB');
                $param = explode(',', $extend['param']);
                $code[] = '    $param=[];';
                foreach ($param as $item) {
                    if ($extend['method'] == 'post') {
                        $code[] = '    $param[]= Request::post(' . var_export(trim($item), true) . ');';
                    } else if ($extend['method'] == 'get') {
                        $code[] = '    $param[]= Request::get(' . var_export(trim($item), true) . ');';
                    } else {
                        $code[] = '    $param[]= Request::param(' . var_export(trim($item), true) . ');';
                    }
                }
                $code[] = '    $rows = DB::getList(' . var_export($extend['sql'], true) . ',$param);';
            } else {
                $codeItem->use('beacon\DB');
                $code[] = '    $rows = DB::getList(' . var_export($extend['sql'], true) . ');';
            }

            $code[] = '    foreach($rows as $rs){';
            $code[] = '        $item=[];';

            if (!empty($extend['field'])) {
                $optFields = explode(',', $extend['field']);
                foreach ($optFields as $opt) {
                    $code[] = '        $item[] = isset($rs[' . var_export(trim($opt), true) . ']) ? $rs[' . var_export(trim($opt), true) . '] : \'\';';
                }
            } else {
                $code[] = '        $rs = array_values($rs);';
                $code[] = '        $item[] = isset($rs[0]) ? $rs[0]: \'\';';
                $code[] = '        $item[] = isset($rs[1]) ? $rs[1]: \'\';';
            }
            $code[] = '        $options[] = $item;';
            $code[] = '    }';
            $code[] = '    return $options;';
            $code[] = '}';
            $codeItem->setCode($code);
            $field['options'] = $codeItem;
            return;
        }
        //配置项 4
        if ($type == 4) {
            if (isset($extend['config'])) {
                $field['options'] = Helper::convertFunc($extend['config']);
            }
            return;
        }
        //函数 5
        if ($type == 5) {
            if (isset($extend['func'])) {
                $field['options'] = Helper::convertFunc($extend['func']);
            }
            return;
        }
        $field['options'] = $options;
    }
}