<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-30
 * Time: 下午4:21
 */

namespace tool\plugin;


use beacon\Form;

class Dynamic extends Form
{
    public $template = 'plugin/Dynamic.plugin.tpl';

    protected function load()
    {
        return [

            'name' => [
                'label' => '条件',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '条件不能为空'],
                'type' => 'select',
                'header' => '选择条件',
                'options' => [['value' => 'eq', 'text' => '相等'], ['value' => 'neq', 'text' => '不等']],
            ],

            'value' => [
                'label' => '值',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '值不能为空'],
                'box-style' => 'width:100px;'
            ],
            'd1' => [
                'label' => '!处理1',
                'type' => 'select',
                'options' => [['value' => 'show', 'text' => '显示'], ['value' => 'on', 'text' => '启用验证']],
            ],
            'r1' => [
                'label' => '呈现',
                'box-style' => 'width:400px;'
            ],
            'd2' => [
                'label' => '!处理2',
                'type' => 'select',
                'options' => [['value' => 'hide', 'text' => '隐藏'], ['value' => 'off', 'text' => '禁用验证']],
            ],
            'r2' => [
                'label' => '呈现',
                'box-style' => 'width:400px;'
            ],

        ];
    }
}