<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午2:54
 */

namespace tool\plugin;


use beacon\Form;

class LinkageHeader extends Form
{
    public $template = 'plugin/Option.plugin.tpl';

    protected function load()
    {
        return [
            'name' => [
                'label' => '选项头',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '选项值不能为空'],
                'box-style' => 'width:120px;',
                'box-class' => 'form-inp mf',
            ],
        ];
    }
}