<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Textarea;

#[Form(title: '顶部按钮设置', template: 'form/top_btn.plugin.tpl')]
class TopBtnPlugin
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