<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '标签插件', template: 'form/dynamic.plugin.tpl')]
class DynamicPlugin
{
    #[Select(
        label: '条件',
        validRule: ['r' => '条件不能为空'],
        options: [['value' => 'eq', 'text' => '相等'], ['value' => 'neq', 'text' => '不等']],
    )]
    public string $name = '';

    #[Text(
        label: '值',
        validRule: ['r' => '值不能为空'],
        attrs: ['style' => 'width:100px;']
    )]
    public string $value = '';

    #[Select(
        label: '处理1',
        validRule: ['r' => '条件不能为空'],
        options: [['value' => 'show', 'text' => '显示'], ['value' => 'on', 'text' => '启用验证']],
    )]
    public string $d1 = '';
    #[Text(
        label: '呈现1',
        attrs: ['style' => 'width:400px;']
    )]
    public string $r1 = '';

    #[Select(
        label: '处理2',
        options: [['value' => 'hide', 'text' => '隐藏'], ['value' => 'off', 'text' => '禁用验证']],
    )]
    public string $d2 = '';
    #[Text(
        label: '呈现2',
        attrs: ['style' => 'width:400px;']
    )]
    public string $r2 = '';


}