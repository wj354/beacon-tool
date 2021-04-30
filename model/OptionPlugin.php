<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Text;

#[Form(title: '选项插件', template: 'form/option.plugin.tpl')]
class OptionPlugin
{
    #[Text(
        label: '值',
        validRule: ['r' => '选项值不能为空'],
        attrs: ['style' => 'width:120px;']
    )]
    public string $value = '';

    #[Text(
        label: '文本',
        attrs: ['style' => 'width:120px;']
    )]
    public string $text = '';

    #[Text(
        label: '提示',
        attrs: ['style' => 'width:120px;']
    )]
    public string $tips = '';

}