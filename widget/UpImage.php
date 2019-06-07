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

class UpImage extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {

        return [

            'dataType' => [
                'label' => '上传类型',
                'type' => 'radio-group', // 单选组
                'options' => [
                    ['image', '单图'],
                    ['imgGroup', '多图'],
                ],
                'default' => 'image',
                'dynamic' => [
                    [
                        'eq' => 'image',
                        'hide' => 'dataSize',
                    ],
                    [
                        'eq' => 'imgGroup',
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
                'default' => 'jpg,jpeg,bmp,gif,png'
            ],
            'dataFieldName' => [
                'label' => '上传域名称',
                'default' => 'filedata'
            ],

            'dataBtnWidth' => [
                'label' => '显示区宽',
                'type' => 'integer',
                'default' => '400'
            ],
            'dataBtnHeight' => [
                'label' => '高',
                'type' => 'integer',
                'viewMerge' => -1,
                'default' => '300'
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
        $field['dataFieldName'] = intval($extend['dataFieldName']);
        $field['dataBtnWidth'] = intval($extend['dataBtnWidth']);
        $field['dataBtnHeight'] = intval($extend['dataBtnHeight']);
        $field['dataSize'] = intval($extend['dataSize']);
        $field['dataUrl'] = Helper::convertUrl($extend['dataUrl']);
    }
}