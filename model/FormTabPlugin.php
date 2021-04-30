<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Text;

#[Form(title: '标签插件',  template: 'form/form_tab.plugin.tpl')]
class FormTabPlugin
{
    #[Text(
        label: '标识',
        validRule: ['r' => '标识不能为空'],
        attrs: ['style'=>'width:120px;']
    )]
    public string $key = '';

    #[Text(
        label: '文本',
        validRule: ['r' => '文本不能为空'],
    )]
    public string $value = '';
}