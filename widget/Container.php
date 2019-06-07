<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-4
 * Time: 上午4:53
 */

namespace tool\widget;


use beacon\DB;
use beacon\Form;

class Container extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
            'plugName' => [
                'label' => '插件名',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '插件名不能为空'],
                'type' => 'select',
                'header' => '选择插件',
                'options' => function () {
                    $list = DB::getList('select `key`,title,namespace from @pf_tool_form where extMode=1');
                    $out = [];
                    foreach ($list as $item) {
                        $val = $item['namespace'] . '\\zero\\plugin\\Zero' . $item['key'] . 'Plugin';
                        $out[] = [$val, $item['title'] . ' | ' . $val];
                    }
                    return $out;
                },
            ],
            'mode' => [
                'label' => '插件类型',
                'type' => 'radio-group',
                'options' => [
                    ['single', '单一(single)'],
                    ['multiple', '多行(multiple)'],
                ],
                'default' => 'multiple',
                'dynamic' => [
                    [
                        'eq' => 'single',
                        'hide' => 'dataMinSize,dataMaxSize,viewRemoveBtn,viewInsertBtn,viewSortBtn',
                    ],
                    [
                        'eq' => 'multiple',
                        'show' => 'dataMinSize,dataMaxSize,viewRemoveBtn,viewInsertBtn,viewSortBtn',
                    ],
                ],
            ],
            'viewRemoveBtn' => [
                'label' => '移除按钮',
                'type' => 'check',
                'afterText' => '勾选显示移除按钮',
                'default' => 1
            ],
            'viewInsertBtn' => [
                'label' => '插入按钮',
                'type' => 'check',
                'afterText' => '勾选显示插入按钮'
            ],
            'viewSortBtn' => [
                'label' => '排序按钮',
                'type' => 'check',
                'afterText' => '勾选显示排序按钮'
            ],
            'dataMinSize' => [
                'label' => '最小行数',
                'type' => 'integer',
                'default' => 0,

            ],
            'dataMaxSize' => [
                'label' => '最大行数',
                'type' => 'integer',
                'default' => 1000,
                'view-merge' => -1,
            ],
            'dataInitSize' => [
                'label' => '默认行数',
                'type' => 'integer',
                'default' => 0,
                'view-merge' => -1,
            ],
            'viewCustom' => [
                'label' => '自定义整行',
                'type' => 'check',
                'afterText' => '勾选自定义整行,既模板整行使用插件的模板渲染',
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {
        $field['plugName'] = $extend['plugName'];
        $field['mode'] = $extend['mode'];
        $field['dataMinSize'] = intval(isset($extend['dataMinSize']) ? $extend['dataMinSize'] : '0');
        $field['dataMaxSize'] = intval(isset($extend['dataMaxSize']) ? $extend['dataMaxSize'] : '0');
        $field['dataInitSize'] = intval(isset($extend['dataInitSize']) ? $extend['dataInitSize'] : '0');
        $field['viewCustom'] = boolval($extend['viewCustom']);
        $field['viewRemoveBtn'] = boolval($extend['viewRemoveBtn']);
        $field['viewInsertBtn'] = boolval($extend['viewInsertBtn']);
        $field['viewSortBtn'] = boolval($extend['viewSortBtn']);
    }
}