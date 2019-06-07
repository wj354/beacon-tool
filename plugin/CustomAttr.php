<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-30
 * Time: 下午4:12
 */

namespace tool\plugin;


use beacon\Form;

class CustomAttr extends Form
{
    public $template = 'plugin/CustomAttr.plugin.tpl';

    protected function load()
    {
        return [

            'name' => [
                'label' => '属性名',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '属性名不能为空'],
                'box-style' => 'width:120px;'
            ],
            'type' => [
                'label' => '值类型',
                'type' => 'select',
                'options' => [
                    ['string', 'string'],
                    ['integer', 'integer'],
                    ['float', 'float'],
                    ['boolean', 'boolean'],
                    ['array', 'array'],
                ],
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '属性名不能为空'],
                'box-style' => 'width:120px;'
            ],
            'value' => [
                'label' => '属性值',
                'type' => 'textarea',
                'box-style' => 'width:200px;height:17px',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '属性值不能为空'],
                'box-class' => 'form-inp mf',
            ],
        ];
    }
}