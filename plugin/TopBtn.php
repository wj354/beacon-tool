<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-29
 * Time: 上午3:02
 */

namespace tool\plugin;


use beacon\Form;

class TopBtn extends Form
{
    public $template = 'plugin/TopBtn.plugin.tpl';

    protected function load()
    {
        return [
            'code' => [
                'label' => '模板代码',
                'type' => 'textarea',
                'box-style' => 'width:700px; height:64px;',
                'box-class' => 'form-inp mf navy',
                'box-spellcheck' => 'false',
            ],
        ];
    }
}