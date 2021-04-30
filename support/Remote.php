<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: '远程文本设置', template: 'form/field_support.tpl')]
class Remote
{
    #[Text(
        label: '检查地址',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => '如：^/admin/MyCtl/mydata'
        ]
    )]
    public string $url = '';

    #[Select(label: '请求方式', options: [
        ['get', 'GET'],
        ['post', 'POST'],
    ])]
    public string $method = 'get';

    #[Text(
        label: '携带参数',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => 'id,pid'
        ]
    )]
    public string $carry = '';


    public function export(): array
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'carry' => $this->carry,
        ];
    }

}