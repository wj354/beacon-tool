<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;

#[Form(title: '拆分名称', template: 'form/names.plugin.tpl')]
class NamesPlugin
{
    #[Text(
        label: '字段名',
        validRule: ['r' => '字段名不能为空'],
        attrs: ['style' => 'width:120px;']
    )]
    public string $field = '';

    #[Select(
        label: '字段类型',
        options: [
            ['int', '整数 int(11)'],
            ['varchar', '字符串 varchar(200)'],
            ['tinyint', 'Bool值 tinyint(1)'],
            ['decimal', '小数 decimal(18,2)'],
        ]
    )]
    public string $type = '';
}