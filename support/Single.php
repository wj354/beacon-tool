<?php


namespace tool\support;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\widget\Select;

#[Form(title: '单行容器设置', template: 'form/field_support.tpl')]
class Single
{
    #[Select(
        label: '插件名',
        validRule: ['r' => '插件名不能为空'],
        optionFunc: [self::class, 'itemClassOptions'],
        header: '选择插件'
    )]
    public string $itemClass = '';

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
        ];
    }
}