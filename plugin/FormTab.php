<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-28
 * Time: 上午4:51
 */

namespace tool\plugin;


use beacon\Form;

class FormTab extends Form
{
    public $template = 'plugin/FormTab.plugin.tpl';

    protected function load()
    {
        return [
            'key' => [
                'label' => '标识',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '标识不能为空'],
                'box-style' => 'width:120px;'
            ],
            'value' => [
                'label' => '文本',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '文本不能为空'],
            ],
        ];
    }
}