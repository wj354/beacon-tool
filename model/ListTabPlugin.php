<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Check;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: 'ListTab设置', template: 'form/list_tab.plugin.tpl')]
class ListTabPlugin
{
    #[Text(
        label: '名称',
        validRule: ['r' => '标识不能为空'],
        attrs: ['style' => 'width:120px;']
    )]
    public string $name = '';


    #[Textarea(
        label: '链接',
        attrs: ['style' => 'width:300px;height:30px'],
        viewMerge: -1
    )]
    public string $url = '';

    #[Check(
        label: '是否代码',
        after: '勾选使用代码',
        dynamic: [
        [
            'eq' => 1,
            'show' => 'code',
        ],
        [
            'neq' => 1,
            'hide' => 'code',
        ],
    ],
        viewMerge: -1
    )]
    public bool $useCode = false;

    #[Textarea(
        label: '代码',
        attrs: ['style' => 'width:500px; height:20px;margin-top: 2px;','yee-module'=>'code-editor','class'=>'form-inp textarea code-editor','data-lang'=>'smarty'],
        viewMerge: -1
    )]
    public string $code = '';
}