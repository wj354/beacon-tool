<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午3:58
 */

namespace tool\widget;


use beacon\Form;
use beacon\Route;
use tool\lib\Helper;

class DelaySelect extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [

            'headerText' => [
                'label' => '选项头(文本)',
                'box-style' => 'width:200px;',
                'tips' => '下拉框的选项头'
            ],
            'headerValue' => [
                'label' => '选项头(值)',
                'box-style' => 'width:120px;',
                'viewMerge' => -1,
            ],
            'dataSource' => [
                'label' => '数据源地址',
                'box-placeholder' => '如：^/admin/MyCtl/mydata',
            ],
            'dataMethod' => [
                'label' => '请求方式',
                'type' => 'select',
                'options' => [
                    ['get', 'GET'],
                    ['post', 'POST'],
                ],
            ],
        ];
    }


    public static function export(array &$field, array $extend)
    {
        if (!empty($extend['headerText'])) {
            if (isset($extend['headerValue']) && $extend['headerValue'] !== '' && $extend['headerValue'] !== null) {
                $field['header'] = [$extend['headerValue'], $extend['headerText']];
            } else {
                $field['header'] = ['', $extend['headerText']];
            }
        }
        $field['dataSource'] = Helper::convertUrl($extend['dataSource']);
        $field['dataMethod'] = $extend['dataMethod'];
    }
}