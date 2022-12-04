<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午3:58
 */

namespace tool\widget;


use beacon\Form;
use beacon\Route;
use tool\lib\CodeItem;
use tool\lib\Helper;

class MultipleDialog extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [

            'dataUrl' => [
                'label' => '对话框地址',
                'box-placeholder' => '如：^/admin/MyCtl/myact',
            ],

            'dataWidth' => [
                'label' => '对话框宽',
                'type' => 'integer',
                'default' => 0,
            ],

            'dataHeight' => [
                'label' => '对话框高',
                'type' => 'integer',
                'default' => 0,
                'view-merge' => -1,
            ],

            'type' => [
                'label' => '选项值类型',
                'type' => 'radio-group',
                'options' => [
                    ['sql', 'SQL查询'],
                    ['func', 'PHP函数'],
                ],
                'dynamic' => [
                    [
                        'eq' => 'sql',
                        'show' => 'sql',
                        'hide' => 'func',
                    ],
                    [
                        'eq' => 'func',
                        'show' => 'func',
                        'hide' => 'sql',
                    ],
                ],
                'default' => 'sql',
            ],

            'sql' => [
                'label' => 'SQL查询语句',
                'type' => 'textarea',
                'box-placeholder' => '如：select name from table where id=?',
                'tips' => '使用查询的第1个字段作为值的文本'
            ],

            'func' => [
                'label' => 'PHP函数',
                'type' => 'text',
                'box-style' => 'width:260px;',
                'box-placeholder' => '如:\\lib\\MyClass::MyFunc',
            ],

            'dataBtnText' => [
                'label' => '按钮文本',
                'type' => 'text',
                'default' => '选择',
            ],
            'dataClearBtn' => [
                'label' => '清除按钮',
                'type' => 'check',
                'afterText' => '勾选显示清除按钮'
            ],
            'itemType' => [
                'label' => 'item值类型',
                'type' => 'select',
                'options' => [
                    ['string', 'string'],
                    ['integer', 'integer'],
                    ['float', 'float'],
                ],
            ],

        ];
    }


    public static function export(array &$field, array $extend)
    {
        $field['itemType'] = isset($extend['itemType']) ? $extend['itemType'] : 'string';
        $field['dataWidth'] = intval($extend['dataWidth']);
        $field['dataHeight'] = intval($extend['dataHeight']);
        $field['dataBtnText'] = $extend['dataBtnText'];
        $field['dataClearBtn'] = $extend['dataClearBtn'];
        $field['dataUrl'] = Helper::convertUrl($extend['dataUrl']);

        $type = isset($extend['type']) ? $extend['type'] : '';
        $extend['sql'] = trim($extend['sql']);
        if ($type == 'sql' && !empty($extend['sql'])) {
            $codeItem = new CodeItem();
            $codeItem->use('beacon\DB');
            $code = [];
            $code[] = 'function($value=0){';
            $code[] = '    $row = DB::getRow(' . var_export(trim($extend['sql']), true) . ',$value);';
            $code[] = '    if($row!==null){ return current($row);}';
            $code[] = '    return null;';
            $code[] = '}';
            $codeItem->setCode($code);
            $field['textFunc'] = $codeItem;
            return;
        }
        $extend['func'] = trim($extend['func']);
        if ($type == 'func' && !empty($extend['func'])) {
            $codeItem = new CodeItem();
            $codeItem = new CodeItem();
            $code = 'function($value=0){ if(is_callable(' . var_export($extend['func'], true) . ')){return ' . $extend['func'] . '($value);} return null;}';
            $codeItem->setCode($code);
            $field['textFunc'] = $codeItem;
        }
    }
}