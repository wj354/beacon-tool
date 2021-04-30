<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Integer;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: '穿梭框设置', template: 'form/field_support.tpl')]
class Transfer
{
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
    public string $method = 'get';

    #[Text(
        label: '选项标题',
    )]
    public string $caption = '';

    #[Integer(
        label: '显示区宽',
    )]
    public int $width = 0;
    #[Integer(
        label: '高',
        viewMerge: -1
    )]
    public int $height = 0;


    public function export(): array
    {
        return [
            'source' => $this->source,
            'method' => $this->method,
            'caption' => $this->caption,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

}