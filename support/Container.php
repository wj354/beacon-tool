<?php


namespace tool\support;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\widget\Check;
use beacon\widget\Integer;
use beacon\widget\Select;

#[Form(title: '多行容器设置', template: 'form/field_support.tpl')]
class Container
{
    #[Select(
        label: '插件名',
        validRule: ['r' => '插件名不能为空'],
        optionFunc: [self::class, 'itemClassOptions'],
        header: '选择插件'
    )]
    public string $itemClass = '';

    #[Check(
        label: '移除按钮',
        after: '勾选显示移除按钮',
    )]
    public bool $removeBtn = true;

    #[Check(
        label: '插入按钮',
        after: '勾选显示插入按钮',
    )]
    public bool $insertBtn = false;

    #[Check(
        label: '排序按钮',
        after: '勾选显示排序按钮',
    )]
    public bool $sortBtn = false;

    #[Integer(
        label: '最小行数',
    )]
    public int $minSize = 0;

    #[Integer(
        label: '最大行数',
        viewMerge: -1
    )]
    public int $maxSize = 1000;

    #[Integer(
        label: '默认行数',
        viewMerge: -1
    )]
    public int $initSize = 0;


    /**
     * @return array
     * @throws DBException
     */
    public static function itemClassOptions(): array
    {
        $list = DB::getList('select `key`,title,namespace from @pf_tool_form where extMode=1');
        $out = [];
        foreach ($list as $item) {
            $name = $item['key'] . 'Plugin';
            $val = $item['namespace'] . '\\zero\\plugin\\' . $name;
            $out[] = ['value' => $val, 'text' => $item['title'] . ' | ' . $name];
        }
        return $out;
    }


    public function export(): array
    {
        return [
            'itemClass' => $this->itemClass,
            'removeBtn' => $this->removeBtn,
            'insertBtn' => $this->insertBtn,
            'sortBtn' => $this->sortBtn,
            'minSize' => $this->minSize,
            'maxSize' => $this->maxSize,
            'initSize' => $this->initSize,
        ];
    }
}