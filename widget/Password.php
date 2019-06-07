<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午3:55
 */

namespace tool\widget;


use beacon\Form;
use tool\lib\CodeItem;

class Password extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
            'encodeFunc' => [
                'label' => '加密函数',
                'tips' => '如果需要加密保存，请填写加密函数，含命名空间'
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {

        $extend['encodeFunc'] = trim($extend['encodeFunc']);
        if (!empty($extend['encodeFunc'])) {
            $codeItem = new CodeItem();
            $code = 'function($value){ if(is_callable(' . var_export($extend['encodeFunc'], true) . ')){return ' . $extend['encodeFunc'] . '($value);} return null;}';
            $codeItem->setCode($code);
            $field['encodeFunc'] = $codeItem;
            return;
        }
    }
}