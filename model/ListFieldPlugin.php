<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\DelaySelect;
use beacon\widget\Integer;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '列表字段修饰', template: 'form/list_field.plugin.tpl')]
class ListFieldPlugin
{
    #[Text(
        label: '标题',
        validRule: ['r' => '标题不能为空'],
        attrs: ['style' => 'width:145px;', 'placeholder' => '字段标题']
    )]
    public string $title = '';

    #[Text(
        label: '排序字段',
        attrs: ['style' => 'max-width:100px;', 'placeholder' => '排序字段名']
    )]
    public string $orderName = '';
    #[Select(
        label: 'Th属性',
        options: [['center', 'center'], ['left', 'left'], ['right', 'right']],
    )]
    public string $thAlign = 'center';

    #[Integer(
        label: '宽',
        attrs: ['style' => 'width:50px;', 'placeholder' => '宽']
    )]
    public int $thWidth = 80;

    #[Text(
        label: '其他属性',
        attrs: ['style' => 'max-width:100px;', 'placeholder' => '其他属性']
    )]
    public string $thAttrs = '';

    #[Select(
        label: 'TD对齐',
        options: [['center', 'center'], ['left', 'left'], ['right', 'right']],
    )]
    public string $tdAlign = '';
    #[Text(
        label: '其他属性',
        attrs: ['style' => 'max-width:100px;', 'placeholder' => '其他属性']
    )]
    public string $tdAttrs = '';

    #[Text(
        label: '指定键名',
        attrs: ['style' => 'width:150px;', 'placeholder' => '不指定系统自动分配']
    )]
    public string $keyName = '';

    #[DelaySelect(
        label: '数据库字段',
        header: ['', '选择字段'],
    )]
    public string $field = '';

    #[Textarea(
        label: '值',
        attrs: ['style' => 'width: 450px; height:60px;', 'placeholder' => '值变量修饰']
    )]
    public string $code = '';
}