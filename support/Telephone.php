<?php


namespace tool\support;

use tool\libs\Support;
use tool\model\NamesPlugin;
use beacon\core\Form;
use beacon\widget\Container;
use beacon\widget\Number;
use beacon\widget\Select;

#[Support(name: '电话号码 Telephone', types: ['varchar(200)'])]
#[Form(title: '电话号码设置', template: 'form/field_support.tpl')]
class Telephone
{
    #[Select(
        label: '格式选择',
        options: [
        [2, '区号-电话号码'],
        [3, '区号-电话号码-分机号'],
        [4, '国区 区号-电话号码'],
        [5, '国区 区号-电话号码-分机号'],
    ],
        dynamic: [
            [
                'eq' => 2,
                'hide' => 'gw,fw',
            ],
            [
                'eq' => 3,
                'hide' => 'gw',
                'show' => 'fw',
            ],
            [
                'eq' => 4,
                'hide' => 'fw',
                'show' => 'gw',
            ],
            [
                'eq' => 5,
                'show' => 'gw,fw',
            ],
        ]
    )]
    public int $mode = 2;

    #[Number(
        label: '国区输入框宽',
    )]
    public int $gw = 0;

    #[Number(
        label: '区号输入框宽',
    )]
    public int $qw = 0;

    #[Number(
        label: '分机输入框宽',
    )]
    public int $fw = 0;

    #[Container(
        label: '拆分字段保存',
        itemClass: NamesPlugin::class
    )]
    public array $names = [];


    public function export(): array
    {
        return [
            'mode' => $this->mode,
            'gw' => $this->gw,
            'qw' => $this->qw,
            'fw' => $this->fw,
            'names' => $this->names,
        ];
    }
}