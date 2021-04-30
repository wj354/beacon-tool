<?php


namespace tool\support;


use tool\model\OptionPlugin;
use beacon\core\Form;
use beacon\widget\Container;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '下拉框设置', template: 'form/field_support.tpl')]
class Select
{
    #[Text(
        label: '选项头(文本)',
        prompt: '下拉框的选项头',
        attrs: [
            'style' => 'width:120px;'
        ]
    )]
    public string $headerText = '';

    #[Text(
        label: '选项头(值)',
        viewMerge: -1,
        attrs: [
            'style' => 'width:120px;'
        ]
    )]
    public string $headerValue = '';

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
        ]
    )]
    public string $optionSql = '';


    /**
     * 导出数据
     * @return array
     */
    public function export(): array
    {
        $header = null;
        if (!(empty($this->headerValue) && empty($this->headerText))) {
            $header = [$this->headerValue, $this->headerText];
        }
        return [
            'header' => $header,
            'options' => $this->options,
            'optionFunc' => $this->optionFunc,
            'optionSql' => $this->optionSql,
        ];
    }
}