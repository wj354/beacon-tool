<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 上午3:39
 */

namespace tool\widget;


use beacon\Form;
use tool\lib\Helper;

class UpFile extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {

        return [
            'dataType' => [
                'label' => '上传类型',
                'type' => 'radio-group', // 单选组
                'options' => [
                    ['file', '单文件'],
                    ['fileGroup', '多文件'],
                ],
                'default' => 'file',
                'dynamic' => [
                    [
                        'eq' => 'file',
                        'hide' => 'dataSize',
                    ],
                    [
                        'eq' => 'fileGroup',
                        'show' => 'dataSize',
                    ],
                ],
            ],
            'dataUrl' => [
                'label' => '上传路径',
                'default' => '/service/upload'
            ],
            'dataExtensions' => [
                'label' => '允许上传的类型',
                'box-style' => 'width:300px',
                'default' => 'txt,doc,docx,zip,rar,jpg,jpeg,png,bmp,gif,xls,xlsx,pdf'
            ],
            'dataFieldName' => [
                'label' => '上传域名称',
                'default' => 'filedata'
            ],
            'dataSize' => [
                'label' => '上传最大数量',
                'type' => 'integer',
                'default' => '0',
                'tips' => '0为不限制数量',
            ],
        ];

    }

    public static function export(array &$field, array $extend)
    {
        $field['dataType'] = $extend['dataType'];
        $field['dataExtensions'] = $extend['dataExtensions'];
        $field['dataFieldName'] = $extend['dataFieldName'];
        $field['dataSize'] = intval($extend['dataSize']);
        $field['dataUrl'] = Helper::convertUrl($extend['dataUrl']);
    }
}