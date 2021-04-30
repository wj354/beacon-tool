<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Check;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: 'Tinymce编辑器设置', template: 'form/field_support.tpl')]
class Tinymce
{
    #[Text(
        label: '图片上传路径',
        prompt: '如果不允许上传图片请留空',
        attrs: [
            'placeholder' => '如:/service/tiny_upload'
        ]
    )]
    public string $imagesUploadUrl = '';

    #[Select(
        label: '按钮风格',
        options: [
        ['value' => 'full', 'text' => 'full'],
        ['value' => 'basic', 'text' => 'basic'],
        ['value' => 'mini', 'text' => 'mini'],
    ],
    )]
    public string $typeMode = 'basic';

    #[Check(
        label: '状态栏',
        after: '勾选显示状态栏',
    )]
    public bool $statusbar = false;

    #[Check(
        label: '节点路径',
        after: '勾选显示节点路径',
    )]
    public bool $elementPath = false;


    public function export(): array
    {
        return [
            'imagesUploadUrl' => $this->imagesUploadUrl,
            'typeMode' => $this->typeMode,
            'statusbar' => $this->statusbar,
            'elementPath' => $this->elementPath,
        ];
    }

}