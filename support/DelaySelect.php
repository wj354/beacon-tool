<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: '动态下拉框设置', template: 'form/field_support.tpl')]
class DelaySelect
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

    #[Text(
        label: '数据源地址',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => '如：^/admin/MyCtl/mydata'
        ]
    )]
    public string $source = '';

    #[Select(label: '请求方式', options: [
        ['get', 'GET'],
        ['post', 'POST'],
    ])]
    public string $method = '';

    public function export(): array
    {
        $header = null;
        if (!(empty($this->headerValue) && empty($this->headerText))) {
            $header = [$this->headerValue, $this->headerText];
        }
        return [
            'header' => $header,
            'source' => $this->source,
            'method' => $this->method,
        ];
    }


}