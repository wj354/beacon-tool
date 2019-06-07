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

class XhEditor extends Form
{
    public $template = 'plugin/widget.tpl';

    protected function load()
    {
        return [
            'dataUpLinkUrl' => [
                'label' => '文件上传路径',
                'default' => '/service/xh_upload?immediate=1',
                'tips' => '如果不允许上传文件请留空'
            ],
            'dataUpLinkExt' => [
                'label' => '文件上传后缀',
                'box-style' => 'width:300px',
                'default' => 'txt,doc,docx,zip,rar,xls,xlsx,pdf'
            ],
            'dataUpImgUrl' => [
                'label' => '文件上传路径',
                'default' => '/service/xh_upload?immediate=1',
                'tips' => '如果不允许上传图片请留空'
            ],
            'dataUpImgExt' => [
                'label' => '图片上传后缀',
                'box-style' => 'width:300px',
                'default' => 'jpg,jpeg,bmp,gif,png'
            ],
            'dataSkin' => [
                'label' => '选择皮肤',
                'type' => 'select',
                'options' => [
                    ['value' => 'default', 'text' => 'default'],
                    ['value' => 'vista', 'text' => 'vista'],
                    ['value' => 'o2007blue', 'text' => 'o2007blue'],
                    ['value' => 'o2007silver', 'text' => 'o2007silver'],
                    ['value' => 'nostyle', 'text' => 'nostyle'],
                ],
            ],
            'dataTools' => [
                'label' => '按钮风格',
                'type' => 'select',
                'options' => [
                    ['value' => 'full', 'text' => 'full'],
                    ['value' => 'mfull', 'text' => 'mfull'],
                    ['value' => 'simple', 'text' => 'simple'],
                    ['value' => 'mini', 'text' => 'mini'],
                ],
            ],
        ];
    }

    public static function export(array &$field, array $extend)
    {
        $field['dataUpLinkUrl'] = Helper::convertUrl($extend['dataUpLinkUrl']);
        $field['dataUpImgUrl'] = Helper::convertUrl($extend['dataUpImgUrl']);
        $field['dataUpLinkExt'] = $extend['dataUpLinkExt'];
        $field['dataUpImgExt'] = $extend['dataUpImgExt'];
        $field['dataSkin'] = $extend['dataSkin'];
        $field['dataTools'] = $extend['dataTools'];
    }
}