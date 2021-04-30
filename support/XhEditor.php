<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: 'XhEditor编辑器设置', template: 'form/field_support.tpl')]
class XhEditor
{
    #[Text(
        label: '图片上传路径',
        prompt: '如果不允许上传图片请留空',
        attrs: [
            'placeholder' => '如:/service/tiny_upload'
        ]
    )]
    public string $upLinkUrl = '/service/xh_upload?immediate=1';
    #[Text(
        label: '支持的文件类型',
        attrs: [
            'style' => 'width:400px'
        ]
    )]
    public string $upLinkExt = 'txt,doc,docx,zip,rar,xls,xlsx,pdf';

    #[Text(
        label: '图片上传路径',
        prompt: '如果不允许上传图片请留空',
        attrs: [
            'placeholder' => '如:/service/tiny_upload'
        ]
    )]
    public string $upImgUrl = '/service/xh_upload?immediate=1';

    #[Text(
        label: '支持的图片类型',
        attrs: [
            'style' => 'width:400px'
        ]
    )]
    public string $upImgExt = 'jpg,jpeg,bmp,gif,png';

    #[Select(label: '选择皮肤',
        options: [
            ['value' => 'default', 'text' => 'default'],
            ['value' => 'vista', 'text' => 'vista'],
            ['value' => 'o2007blue', 'text' => 'o2007blue'],
            ['value' => 'o2007silver', 'text' => 'o2007silver'],
            ['value' => 'nostyle', 'text' => 'nostyle'],
        ]
    )]
    public string $skin = 'default';

    #[Select(label: '按钮风格',
        options: [
            ['value' => 'full', 'text' => 'full'],
            ['value' => 'mfull', 'text' => 'mfull'],
            ['value' => 'simple', 'text' => 'simple'],
            ['value' => 'mini', 'text' => 'mini'],
        ]
    )]
    public string $tools = 'full';


    public function export(): array
    {
        return [
            'upLinkUrl' => $this->upLinkUrl,
            'upLinkExt' => $this->upLinkExt,
            'upImgUrl' => $this->upImgUrl,
            'upImgExt' => $this->upImgExt,
            'skin' => $this->skin,
            'tools' => $this->tools,
        ];
    }
}