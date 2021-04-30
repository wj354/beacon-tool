<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '默认值设置', template: 'form/default.plugin.tpl')]
class DefaultPlugin
{
    #[Select(
        label: '默认值类型',
        options: [
        [1, '直填值'],
        [2, '数组值'],
        [3, '参数传递'],
        [4, '内置方法'],
        [5, 'PHP函数']
    ],
        dynamic: [
            [
                'eq' => 1,
                'show' => 'value',
                'hide' => 'json,inner,param,func',
            ],
            [
                'eq' => 2,
                'show' => 'json',
                'hide' => 'value,inner,param,func',
            ],
            [
                'eq' => 3,
                'show' => 'param',
                'hide' => 'value,inner,json,func',
            ],
            [
                'eq' => 4,
                'show' => 'inner',
                'hide' => 'value,param,json,func',
            ],
            [
                'eq' => 5,
                'show' => 'func',
                'hide' => 'value,param,json,inner',
            ],
        ]
    )]
    public int $type = 1;

    #[Textarea(
        label: '默认值',
    )]
    public string $value = '';

    #[Textarea(
        label: '默认值',
        attrs: [
        'placeholder' => '如：[1,2,3] 或者 {"a":1,"b":2,"c":3}',
    ],
        prompt: '请书写json格式'
    )]
    public string $json = '';

    #[Select(
        label: '内置函数名',
        options: [
            ['date', '当前日期'],
            ['datetime', '当前时间'],
            ['maxSort', '最大排序值'],
            ['minSort', '最小排序值'],
        ]
    )]
    public string $inner = '';

    #[Text(
        label: '请求参数',
        prompt: '如：name:s'
    )]
    public string $param = '';

    #[Text(
        label: 'PHP函数',
        prompt: '如:\\lib\\MyClass::MyFunc'
    )]
    public string $func = '';

}