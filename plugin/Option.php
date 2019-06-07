<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-30
 * Time: 下午8:35
 */

namespace tool\plugin;


use beacon\Form;

class Option extends Form
{
    public $template = 'plugin/Option.plugin.tpl';

    protected function load()
    {
        return [
            'value' => [
                'label' => '值',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '选项值不能为空'],
                'box-style' => 'width:120px;'
            ],
            'text' => [
                'label' => '文本',
                'box-style' => 'width:120px;',
                'box-class' => 'form-inp mf',
            ],
            'tips' => [
                'label' => '提示',
                'box-style' => 'width:120px;'
            ],
        ];
    }
}