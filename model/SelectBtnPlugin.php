<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Textarea;

#[Form(title: '全选按钮设置', template: 'form/select_btn.plugin.tpl')]
class SelectBtnPlugin
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