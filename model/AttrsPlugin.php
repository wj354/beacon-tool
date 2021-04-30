<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '属性插件', template: 'form/attrs.plugin.tpl')]
class AttrsPlugin
{
    #[Text(
        label: '属性名',
        validRule: ['r' => '属性名不能为空'],
        attrs: ['style' => 'width:120px;']
    )]
    public string $name = '';

    #[Textarea(
        label: '属性值',
        validRule: ['r' => '属性值不能为空'],
        attrs: ['style' => 'width:260px;height:17px']
    )]
    public string $value = '';

}