<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Text;

#[Form(title: '选项头名称', template: 'form/headers.plugin.tpl')]
class HeadersPlugin
{
    #[Text(
        label: '选项头',
        validRule: ['r' => '选项头不能为空'],
        attrs: ['style' => 'width:120px;']
    )]
    public string $name = '';
}