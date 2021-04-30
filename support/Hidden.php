<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Select;

#[Form(title: '隐藏域设置', template: 'form/field_support.tpl')]
class Hidden
{
    #[Select(label: '值类型', options: [
        ['string', 'string'],
        ['integer', 'integer'],
        ['float', 'float'],
        ['array', 'array'],
        ['bool', 'bool'],
    ])]
    public string $varType = 'string';

    public function export(): array
    {
        return [
            'varType' => $this->varType,
        ];
    }

}