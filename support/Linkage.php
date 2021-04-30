<?php


namespace tool\support;


use tool\model\HeadersPlugin;
use tool\model\NamesPlugin;
use beacon\core\Form;
use beacon\widget\Container;
use beacon\widget\Integer;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '联动下拉菜单设置', template: 'form/field_support.tpl')]
class Linkage
{
    #[Text(
        label: '数据源地址',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => '如：^/admin/MyCtl/mydata'
        ]
    )]
    public string $source = '';

    #[Select(label: '请求方式', options: [
        ['get', 'GET'],
        ['post', 'POST'],
    ])]
    public string $method = '';

    #[Integer(
        label: '联动下拉级别',
        prompt: '联动下拉的层级深度 0 为按数据层级自动增长',
    )]
    public int $level = 0;

    #[Container(
        label: '选项头',
        itemClass: HeadersPlugin::class
    )]
    public array $headers = [];

    #[Container(
        label: '拆分字段保存',
        itemClass: NamesPlugin::class
    )]
    public array $names = [];


    #[Textarea(
        label: '验证配置 [valid-group]',
        prompt: '验证规则配置',
        attrs: ['yee-module' => 'valid-group']
    )]
    public string $validGroup = '';


    public function export(): array
    {
        $headers = [];
        foreach ($this->headers as $head) {
            $headers[] = $head['name'] ?? '';
        }
        return [
            'source' => $this->source,
            'method' => $this->method,
            'level' => $this->level,
            'headers' => $headers,
            'names' => $this->names,
        ];
    }
}