<?php


namespace tool\support;

use tool\model\NamesPlugin;
use tool\model\OptionPlugin;
use beacon\core\Form;
use beacon\widget\Container;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '多选框设置', template: 'form/field_support.tpl')]
class CheckGroup
{

    #[Container(label: '选项 [options]', itemClass: OptionPlugin::class)]
    public array $options = [];

    #[Text(
        label: 'PHP函数',
        attrs: [
            'placeholder' => '如:\\lib\\MyClass::MyFunc',
            'style' => 'width:260px;'
        ]
    )]
    public string $optionFunc = '';

    #[Textarea(
        label: '查询语句(表名)',
        prompt: 'as value 是值，as text 是文本',
        attrs: [
            'placeholder' => '如：select id as value,name as text from @pf_table',
            'yee-module'=>'code-editor','class'=>'form-inp textarea code-editor','data-lang'=>'sql'
        ]
    )]
    public string $optionSql = '';

    #[Select(label: 'item值类型', options: [
            ['string', 'string'],
            ['integer', 'integer'],
            ['float', 'float'],
        ]
    )]
    public string $itemType = '';

    #[Container(
        label: '拆分字段保存',
        itemClass: NamesPlugin::class
    )]
    public array $names = [];

    public function export(): array
    {

        return [
            'options' => $this->options,
            'optionFunc' => $this->optionFunc,
            'optionSql' => $this->optionSql,
            'itemType' => $this->itemType,
            'names' => $this->names,
        ];
    }

}