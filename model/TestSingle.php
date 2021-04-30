<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Container;
use beacon\widget\Single;

#[Form(title: '测试模型', template: 'test/test.form.tpl')]
class TestSingle
{
    #[Single(
        label: '简单模式'
    )]
    public array $single = [];
}