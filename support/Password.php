<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Text;

#[Form(title: '密码框设置', template: 'form/field_support.tpl')]
class Password
{
    #[Text(
        label: '加密函数',
        attrs: [
        'style' => 'width:260px;',
        'placeholder' => '如:\\lib\\MyClass::MyFunc'
    ],
        prompt: '如果需要加密保存，请填写加密函数，含命名空间',
    )]
    public string $encodeFunc = 'md5';


    public function export(): array
    {
        return [
            'encodeFunc' => $this->encodeFunc,
        ];
    }
}