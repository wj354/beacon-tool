<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-4
 * Time: 上午4:11
 */

namespace tool\widget;


use beacon\Form;
use tool\lib\Helper;

class Tinymce extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
            'dataImagesUploadUrl' => [
                'label' => '文件上传路径',
                'default' => '/service/tiny_upload',
                'tips' => '如果不允许上传图片请留空'
            ],
            'dataTypeMode' => [
                'label' => '按钮风格',
                'type' => 'select',
                'options' => [
                    ['value' => 'full', 'text' => 'full'],
                    ['value' => 'basic', 'text' => 'basic'],
                    ['value' => 'mini', 'text' => 'mini'],
                ],
            ],
            'dataStatusbar' => [
                'label' => '状态栏',
                'type' => 'check',
            ],
            'dataElementpath' => [
                'label' => '节点路径',
                'type' => 'check',
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {
        $field['dataImagesUploadUrl'] = Helper::convertUrl($extend['dataImagesUploadUrl']);
        $field['dataTypeMode'] = $extend['dataTypeMode'];
        $field['dataStatusbar'] = $extend['dataStatusbar'];
        $field['dataElementpath'] = $extend['dataElementpath'];
    }
}