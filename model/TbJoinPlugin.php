<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: 'Join设置', template: 'form/tb_join.plugin.tpl')]
class TbJoinPlugin
{
    #[Text(
        label: '附加表',
        validRule: ['r' => '附加表不能为空']
    )]
    public string $name = '';
    #[Select(
        label: 'JOIN',
        options: [['inner join', 'inner join | 交集'], ['left join', 'left join | 左集合'], ['right join', 'right join | 右集合']]
    )]
    public string $join = '';
    #[TextArea(
        label: 'ON',
        validRule: ['r' => 'JSON条件不能为空'],
        attrs: ['style' => 'width:200px; height:18px;vertical-align: middle;', 'spellcheck' => 'false']
    )]
    public string $on = '';
}