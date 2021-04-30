<?php


namespace tool\support;


use beacon\core\Form;
use beacon\widget\Integer;
use beacon\widget\Select;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '多选对话框设置', template: 'form/field_support.tpl')]
class MultiDialog
{
    #[Text(
        label: '对话框地址',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => '如：^/admin/MyCtl/mydata'
        ]
    )]
    public string $url = '';

    #[Integer(
        label: '对话框宽',
    )]
    public int $width = 0;

    #[Integer(
        label: '对话框高',
        viewMerge: -1
    )]
    public int $height = 0;

    #[Text(
        label: '携带的参数',
        attrs: [
            'style' => 'width:320px;',
            'placeholder' => '如：id,name'
        ]
    )]
    public string $carry = '';

    #[Select(label: 'item值类型',
        options: [
            ['string', 'string'],
            ['integer', 'integer'],
            ['float', 'float'],
        ]
    )]
    public string $itemType = '';

    #[Select(label: '选项值类型',
        options: [
            ['sql', 'SQL查询'],
            ['func', 'PHP函数'],
        ],
        dynamic: [
            [
                'eq' => 'textSql',
                'show' => 'textSql',
                'hide' => 'textFunc',
            ],
            [
                'eq' => 'textFunc',
                'show' => 'textFunc',
                'hide' => 'textSql',
            ]
        ]
    )]
    public string $type = 'textSql';


    #[Textarea(label: 'SQL查询语句',
        attrs: [
            'placeholder' => '如：select name from table where id=?'
        ],
        prompt: '使用查询的第1个字段作为值的文本',
    )]
    public string $textSql = '';

    #[Text(
        label: 'PHP函数',
        attrs: [
            'style' => 'width:260px;',
            'placeholder' => '如:\\lib\\MyClass::MyFunc'
        ]
    )]
    public string $textFunc = '';

    #[Text(
        label: '按钮文本',
        attrs: [
            'style' => 'width:120px;',
            'placeholder' => '选择'
        ]
    )]
    public string $btnText = '';

    #[Text(
        label: '清除按钮',
        after: '勾选显示清除按钮',
        viewMerge: -1
    )]
    public int $clearBtn = 0;


    public function export(): array
    {
        $data = [
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'carry' => $this->carry,
            'itemType' => $this->itemType,
            'btnText' => $this->btnText,
            'clearBtn' => $this->clearBtn,
        ];
        if ($this->type = 'sql') {
            $data['textSql'] = $this->textSql;
        }
        if ($this->type = 'func') {
            $data['textFunc'] = $this->textFunc;
        }
        return $data;
    }
}