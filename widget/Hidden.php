<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午3:39
 */

namespace tool\widget;


use beacon\Form;
use beacon\Logger;
use tool\lib\Helper;

class Hidden extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {

        return [
            'varType' => [
                'label' => '值类型',
                'type' => 'select', // 单选组

                'options' => [
                    ['string', 'string'],
                    ['integer', 'integer'],
                    ['float', 'float'],
                    ['array', 'array'],
                    ['bool', 'bool'],
                ],

            ],
        ];

    }

    public static function export(array &$field, array $extend)
    {
        if (!empty($extend['varType'])) {
            $field['varType'] = $extend['varType'];
        }
    }
}