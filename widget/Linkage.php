<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 2018/2/19
 * Time: 13:33
 */

namespace tool\widget;

use beacon\Form;
use beacon\Utils;
use tool\lib\Helper;
use tool\plugin\LinkageHeader;
use tool\plugin\Names;

class Linkage extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
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
            'dataLevel' => [
                'label' => '联动下拉级别',
                'type' => 'integer',
                'default' => 0,
                'tips' => '联动下拉的层级深度 0 为按数据层级自动增长',
            ],
            'dataHeader' => [
                'label' => '选项头',
                'type' => 'container',
                'mode' => 'multiple',
                'plug-name' => LinkageHeader::class,
            ],
            'names' => [
                'label' => '拆分字段保存',
                'type' => 'container',
                'mode' => 'multiple',
                'plug-name' => Names::class,
            ],
            'dataValGroup' => [
                'label' => '验证配置 [data-val-group]',
                'type' => 'textarea',
                'tips' => '验证规则配置',
                'box-yee-module' => 'valid-group',
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {
        $temp = Helper::convertArray($extend['dataHeader'], []);
        $idx = 1;
        foreach ($temp as $header) {
            $field['dataHeader' . $idx] = $header['name'];
            $idx++;
        }
        $field['dataSource'] = Helper::convertUrl($extend['dataSource']);
        $field['names'] = Helper::convertArray($extend['names']);
        $field['dataValGroup'] = Helper::convertArray($extend['dataValGroup']);
        $field['dataLevel'] = intval($extend['dataLevel']);
        $field['dataMethod'] = $extend['dataMethod'];
    }

}