<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 下午5:46
 */

namespace tool\plugin;


use beacon\Form;

class DefaultSet extends Form
{
    public $template = 'plugin/Default.plugin.tpl';

    protected function load()
    {
        return [
            'type' => [
                'label' => '默认值类型',
                'type' => 'radio-group',
                'options' => [
                    [1, '直填值'],
                    [2, '数组值'],
                    [3, '内置方法'],
                    [4, '参数传递'],
                    [5, 'SQL查询'],
                    [6, 'PHP函数'],
                ],
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'value',
                        'hide' => 'json,inner,method,param,sql,args,func',
                    ],
                    [
                        'eq' => 2,
                        'show' => 'json',
                        'hide' => 'value,inner,method,param,sql,args,func',
                    ],
                    [
                        'eq' => 3,
                        'show' => 'inner',
                        'hide' => 'json,value,method,param,sql,args,func',
                    ],
                    [
                        'eq' => 4,
                        'show' => 'method,param',
                        'hide' => 'json,value,inner,sql,args,func',
                    ],
                    [
                        'eq' => 5,
                        'show' => 'method,args,sql',
                        'hide' => 'json,value,func,param',
                    ],
                    [
                        'eq' => 6,
                        'show' => 'func',
                        'hide' => 'json,value,inner,method,param,args,sql',
                    ],
                ],
                'default' => 1,
                'forceDefault' => true,
            ],
            'value' => [
                'label' => '默认值',
                'type' => 'textarea',
                'box-class' => 'form-inp mf textarea',
            ],
            'json' => [
                'label' => '默认值',
                'type' => 'textarea',
                'box-placeholder' => '如：[1,2,3] 或者 {"a":1,"b":2,"c":3}',
                'tips' => '请书写json格式',
                'box-class' => 'form-inp mf textarea',
                'view-hide' => true,
            ],
            'inner' => [
                'label' => '内置函数名：',
                'type' => 'select',
                'options' => [
                    ['date', '当前日期'],
                    ['datetime', '当前时间'],
                    ['maxSort', '最大排序值'],
                    ['minSort', '最小排序值'],
                ],
                'view-hide' => true,
            ],
            'method' => [
                'label' => '请求类型：',
                'type' => 'select',
                'options' => [
                    ['req', 'REQUEST'],
                    ['get', 'GET'],
                    ['post', 'POST'],
                ],
                'view-hide' => true,
            ],
            'param' => [
                'label' => '请求参数',
                'type' => 'text',
                'box-style' => 'width:260px;',
                'box-placeholder' => '如：name:s ',
                'view-merge' => -1,
                'box-class' => 'form-inp mf',
                'view-hide' => true,
            ],
            'args' => [
                'label' => '请求参数',
                'type' => 'text',
                'box-style' => 'width:260px;',
                'box-placeholder' => '多个用","隔开,如：name:s,pid:i',
                'view-merge' => -1,
                'box-class' => 'form-inp mf',
                'view-hide' => true,
            ],
            'sql' => [
                'label' => 'SQL查询语句',
                'type' => 'textarea',
                'box-placeholder' => '如：select name from table where id=?',
                'tips' => '使用查询的第1个字段作为值的文本',
                'box-class' => 'form-inp mf textarea',
                'view-hide' => true,
            ],
            'func' => [
                'label' => 'PHP函数',
                'type' => 'text',
                'box-style' => 'width:260px;',
                'box-placeholder' => '如:\\lib\\MyClass::MyFunc',
                'box-class' => 'form-inp mf',
                'view-hide' => true,
            ],
        ];
    }
}