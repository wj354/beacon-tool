<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Container;

#[Form(title: '测试模型', template: 'test/test.form.tpl')]
class TestContainer
{
    #[Container(
        label: '批量模式'
    )]
    public array $container = [];
}