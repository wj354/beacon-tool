<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午2:54
 */

namespace tool\plugin;


use beacon\Form;

class Names extends Form
{
    public $template = 'plugin/Option.plugin.tpl';

    protected function load()
    {
        return [
            'field' => [
                'label' => '字段名',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '选项值不能为空'],
                'box-style' => 'width:120px;'
            ],
            'type' => [
                'label' => '字段类型',
                'type' => 'select',
                'options' => [
                    ['int', '整数(int)'],
                    ['string', '字符串(string)'],
                    ['bool', 'Bool值(bool)'],
                ],
            ],
        ];
    }
}