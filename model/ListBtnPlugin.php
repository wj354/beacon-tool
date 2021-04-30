<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Textarea;

#[Form(title: '列表按钮设置', template: 'form/list_btn.plugin.tpl')]
class ListBtnPlugin
{
    #[Textarea(
        label: '模板代码',
        attrs: [
            'style' => 'width:700px; height:64px;',
            'spellcheck' => 'false'
        ]
    )]
    public string $code = '';
}