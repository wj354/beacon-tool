<?php


namespace tool\model;


use beacon\core\Form;
use beacon\widget\Button;
use beacon\widget\Check;
use beacon\widget\Container;
use beacon\widget\Hidden;
use beacon\widget\Integer;
use beacon\widget\Line;
use beacon\widget\RadioGroup;
use beacon\widget\Select;
use beacon\widget\Single;
use beacon\widget\Text;
use beacon\widget\Textarea;
use beacon\core\DBException;
use beacon\core\DB;
use beacon\core\Request;

#[Form(title: '搜索字段管理', table: '@pf_tool_search', template: 'form/app_search.tpl')]
class AppSearchModel
{
    #[Text(
        label: '字段标题 [label]',
        validRule: ['r' => '请输入字段标题'],
        prompt: '提示：如果标题需要隐藏 可在标题前加 ! 号 ',
        star: true,
        tabIndex: 'base'
    )]
    public string $label = '';

    #[Button(
        label: '翻译',
        attrs: [
        'href' => '~/translate/index',
        'yee-module' => 'ajax',
        'on-before' => 'data.param[\'text\']=$(\'#label\').val()||\'\';',
        'on-success' => 'if(ret){$(\'#name\').val(ret.camel2);}',
    ],
        viewMerge: -1,
        tabIndex: 'base',
    )]
    public string $btn1 = '';

    #[Text(
        label: '字段名称 [name]',
        validRule: ['r' => '没有填写模型关键字', 'regex' => ['^[a-z][A-Za-z0-9_]+$', '字段标识只能使用小写字母开头的数字及字母组合']],
        star: true,
        prompt: '字段名称将作为表字段名称',
        attrs: ['yee-module' => 'remote', 'data-url' => '~/app_field/check_name', 'data-carry' => 'formId,id'],
        validFunc: [self::class, 'nameValidFunc'],
        tabIndex: 'base'
    )]
    public string $name = '';

    #[RadioGroup(
        label: '字段类型 [type]',
        star: true,
        attrs: ['style' => 'min-width:170px;display:inline-block;'],
        optionFunc: [self::class, 'typeOptions'],
        dynamic: [
        [
            'eq' => 'Hidden',
            'show' => 'hidden',
        ],
        [
            'neq' => 'Hidden',
            'hide' => 'hidden',
        ]],
        tabIndex: 'base'
    )]
    public string $type = 'Text';

    #[Check(
        label: '底部隐藏输入框 [hidden]',
        after: '勾选后直接在底部添加隐藏输入框',
        tabIndex: 'base'
    )]
    public bool $hidden = false;

    #[Select(
        label: '值类型 [var-type]',
        options: [
        ['value' => 'string', 'text' => 'string'],
        ['value' => 'integer', 'text' => 'integer'],
        ['value' => 'boolean', 'text' => 'boolean'],
        ['value' => 'float', 'text' => 'float'],
        ['value' => 'array', 'text' => 'array'],
    ],
        tabIndex: 'base',
        prompt: '选择值的变量类型'
    )]
    public string $varType = '';

    #[Text(
        label: '前置文本(小标题)',
        tabIndex: 'base',
    )]
    public string $before = '';

    #[Text(
        label: '尾随文本(单位等)',
        tabIndex: 'base',
    )]
    public string $after = '';

    #[Integer(
        label: '字段排序',
        tabIndex: 'base',
        defaultFunc: [self::class, 'sortDefaultFunc']
    )]
    public ?int $sort = null;

    #[Select(
        label: '选择所属Tab',
        tabIndex: 'base',
        prompt: '选择所属TAB标签',
        options: [
        ['value' => 'base', 'text' => '基本栏目'],
        ['value' => 'senior', 'text' => '高级搜索'],
    ],
    )]
    public string $tabIndex = '';

    #[Textarea(
        label: 'SQL查询代码',
        tabIndex: 'base',
        prompt: '如 (模糊查找) `name` like concat(\'%\',?,\'%\') 或者 type=? 或者 datetime > ?'
    )]
    public string $tbWhere = '';

    #[Select(
        label: '加入条件',
        tabIndex: 'base',
        options: [
            [0, '为[空,0,null]不加入'],
            [1, '为[null]不加入'],
            [2, '为[空,null]不加入'],
            [3, '为[0,null]不加入'],
            [-1, '直接加入'],
        ]
    )]
    public string $tbWhereType = '';

    #[Single(
        label: '默认值 [default]',
        itemClass: DefaultPlugin::class,
        tabIndex: 'base'
    )]
    public array $default = [];


    #[RadioGroup(
        label: '合并字段',
        options: [
        ['value' => 0, 'text' => '不合并'],
        ['value' => 1, 'text' => '向下合并'],
        ['value' => -1, 'text' => '向上合并']
    ],
        tabIndex: 'base',
    )]
    public int $viewMerge = 0;

    #[Check(
        label: '关闭控件',
        prompt: '被关闭的控件不会输出任何HTML代码，也不会保存入库',
        after: '勾选关闭控件',
        tabIndex: 'base'
    )]
    public bool $close = false;

    #[Single(
        label: '高级设置',
        tabIndex: 'extend',
        attrs: ['data-url' => '~/app_field/support']
    )]
    public array $extend = [];

    #[Line(
        label: '自定义扩展属性',
        tabIndex: 'extend',
    )]
    public string $customLine = '';

    #[Text(label: '控件CSS类名', tabIndex: 'extend', prompt: '默认系统会指定为 "form-inp 控件类型"')]
    public string $attrClass = '';

    #[Textarea(label: '内联style样式', tabIndex: 'extend', prompt: '控件的内联样式')]
    public string $attrStyle = '';

    #[Text(label: '输入框内提示文本(placeholder)', tabIndex: 'extend', prompt: '直接在输入框内的提示文本')]
    public string $attrPlaceholder = '';

    #[Container(label: '其他属性', tabIndex: 'extend', itemClass: AttrsPlugin::class)]
    public array $attrs = [];

    #[Hidden(
        label: '表单ID',
        defaultFromParam: 'listId:i',
        hidden: true,
        tabIndex: 'valid',
    )]
    public ?int $listId = null;

    /**
     * @return array
     */
    public static function typeOptions(): array
    {
        $data = [
            'Text' => ['text' => '文本框 Text', 'data-types' => ['varchar(200)', 'text']],
            'Hidden' => ['text' => '隐藏域 Hidden', 'data-types' => ['int(11)', 'tinyint(4)', 'varchar(200)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json'], 'data-default' => 'int'],
            'Check' => ['text' => '是否 Check', 'data-types' => ['tinyint(1)']],
            'Integer' => ['text' => '整数 Integer', 'data-types' => ['int(11)']],
            'Number' => ['text' => '小数 Number', 'data-types' => ['decimal(18,2)', 'float(18,2)', 'double(18,2)']],
            'Password' => ['text' => '密码框 Password', 'data-types' => ['varchar(200)']],
            'Date' => ['text' => '日期格式 Date', 'data-types' => ['date', 'datetime', 'int(11)']],
            'Datetime' => ['text' => '时间格式 Datetime', 'data-types' => ['datetime', 'int(11)']],
            'Select' => ['text' => '下拉框 Select', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'DelaySelect' => ['text' => '异步下拉 DelaySelect', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'RadioGroup' => ['text' => '单选组 RadioGroup', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'CheckGroup' => ['text' => '多选组 CheckGroup', 'data-types' => ['varchar(200)', 'int(11)', 'text', 'longtext', 'json'], 'data-default' => 'json'],
            'Linkage' => ['text' => '联动下拉 Linkage', 'data-types' => ['varchar(200)', 'text', 'json'], 'data-default' => 'json'],
            'SelectDialog' => ['text' => '选择对话框 SelectDialog', 'data-types' => ['int(11)', 'varchar(200)'], 'data-default' => 'int'],
            'Button' => ['text' => '按钮 Button', 'data-types' => ['none']],
        ];
        foreach ($data as $key => &$opt) {
            $opt['value'] = $key;
        }
        return array_values($data);
    }

    /**
     * @return int
     * @throws DBException
     */
    public static function sortDefaultFunc(): int
    {
        $listId = Request::get('listId:i', 0);
        $num = DB::getMax('@pf_tool_search', 'sort', 'listId=?', $listId);
        if (empty($num)) {
            return 10;
        }
        return intval($num) + 10;
    }
}