<?php


namespace tool\model;

use beacon\core\App;
use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\core\Request;
use beacon\core\Util;
use beacon\widget\Button;
use beacon\widget\Check;
use beacon\widget\Container;
use beacon\widget\DelaySelect;
use beacon\widget\Hidden;
use beacon\widget\Integer;
use beacon\widget\Line;
use beacon\widget\RadioGroup;
use beacon\widget\Select;
use beacon\widget\Single;
use beacon\widget\Text;
use beacon\widget\Textarea;
use tool\libs\Support;

/**
 * Class AppFieldModel
 * @package tool\model
 */
#[Form(title: '字段管理', table: '@pf_tool_field', template: 'form/app_field.tpl')]
class AppFieldModel
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

    #[Text(
        label: '输入框名称 [box-name]',
        prompt: '输入框的 name 属性值,如果不填则与字段名称一致',
        star: true,
        attrs: ['style' => 'width:120px;'],
        tabIndex: 'base'
    )]
    public string $boxName = '';

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

    #[Check(
        label: '是否数据库字段',
        after: '是否同步创建数据库字段',
        tabIndex: 'base'
    )]
    public bool $dbField = true;

    #[DelaySelect(
        label: '数据库字段类型',
        validRule: ['r' => '请选择字段类型'],
        header: ['', '数据库字段类型'],
        prompt: '在数据库中的字段类型',
        tabIndex: 'base'
    )]
    public string $dbType = '';

    #[Integer(
        label: '长度',
        viewMerge: -1,
        tabIndex: 'base',
        attrs: ['style' => 'width: 80px']
    )]
    public ?int $dbLen = null;

    #[Integer(
        label: '小数点',
        viewMerge: -1,
        tabIndex: 'base',
        attrs: ['style' => 'width: 80px']
    )]
    public ?int $dbPoint = null;

    #[Text(
        label: '字段备注',
        tabIndex: 'base',
    )]
    public string $dbComment = '';

    #[Select(
        label: '默认值',
        options: [
        ['value' => 'null', 'text' => 'NULL'],
        ['value' => 'empty', 'text' => '空字符串'],
        ['value' => 'value', 'text' => '值'],
    ],
        dynamic: [
        [
            'eq' => 'value',
            'show' => ['dbDefValue'],
        ],
        [
            'neq' => 'value',
            'hide' => ['dbDefValue'],
        ],
    ],
        tabIndex: 'base',
    )]
    public string $dbDefType = 'null';

    #[Text(
        label: '!值',
        tabIndex: 'base',
        viewMerge: -1,
        attrs: ['style' => 'width: 80px']
    )]
    public string $dbDefValue = '';

    #[Check(
        label: '是否唯一',
        after: '勾选后数据索引唯一',
        tabIndex: 'base'
    )]
    public bool $dbUnique = false;

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
        close: true
    )]
    public string $tabIndex = '';


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

    #[Check(
        label: '关闭视图',
        prompt: '仅关闭视图，保存入库时使用默认值',
        after: '勾选关闭视图',
        tabIndex: 'base'
    )]
    public bool $viewClose = false;

    #[Check(
        label: '编辑状态只读',
        prompt: '在Form为编辑状态时，不可编辑',
        after: '勾选编辑只读',
        tabIndex: 'base'
    )]
    public bool $offEdit = false;

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

    #[Textarea(label: '提示信息 [prompt]', tabIndex: 'extend')]
    public string $prompt = '';

    #[Line(
        label: '动态呈现',
        tabIndex: 'extend',
    )]
    public string $dynamicLine = '';

    #[Container(label: '动态呈现控制 [dynamic]', tabIndex: 'extend', itemClass: DynamicPlugin::class)]
    public array $dynamic = [];


    #[Textarea(label: '验证配置', tabIndex: 'valid', attrs: ['yee-module' => 'valid-rule'])]
    public string $validRule = '';

    #[Textarea(label: '默认提示内容', tabIndex: 'valid')]
    public string $validDefault = '';

    #[Textarea(label: '正确提示内容', tabIndex: 'valid')]
    public string $validCorrect = '';

    #[Text(label: '呈现内容的标签ID', tabIndex: 'valid', prompt: '用于呈现正确或者错误信息的HTML标签，如：#test-validation 或者 .test-validation')]
    public string $validDisplay = '';

    #[Check(
        label: '关闭验证',
        prompt: '关闭认证后，前后台不再验证数据，如需要开启可在JS和PHP控制器代码中取消',
        after: '勾选关闭验证',
        tabIndex: 'valid'
    )]
    public bool $validDisabled = false;

    #[Check(
        label: '是否标星',
        prompt: '在Label前标红*',
        after: '勾选标星',
        tabIndex: 'base'
    )]
    public bool $star = false;

    #[Text(
        label: '验证数值的函数',
        tabIndex: 'valid',
        attrs: [
        'style' => 'width:260px;',
        'placeholder' => '如:\\lib\\MyClass::MyFunc'
    ],
        validRule: ['regex' => ['^\w+(\\\\\w+)*::\w+$', '格式不正确']]
    )]
    public string $validFunc = '';

    #[Hidden(
        label: '表单ID',
        defaultFromParam: 'formId:i',
        hidden: true,
        tabIndex: 'valid',
    )]
    public ?int $formId = null;

    public static function typeOptions(): array
    {
        $data = [
            'Text' => ['text' => '文本框 Text', 'data-types' => ['varchar(200)', 'text']],
            'Hidden' => ['text' => '隐藏域 Hidden', 'data-types' => ['int(11)', 'tinyint(4)', 'varchar(200)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json'], 'data-default' => 'int'],
            'Check' => ['text' => '是否 Check', 'data-types' => ['tinyint(1)']],
            'Integer' => ['text' => '整数 Integer', 'data-types' => ['int(11)']],
            'Number' => ['text' => '小数 Number', 'data-types' => ['decimal(18,2)', 'float(18,2)', 'double(18,2)']],
            'Password' => ['text' => '密码框 Password', 'data-types' => ['varchar(200)']],
            'Color' => ['text' => '颜色选择 Color', 'data-types' => ['varchar(200)']],
            'Date' => ['text' => '日期格式 Date', 'data-types' => ['date', 'datetime', 'int(11)']],
            'Datetime' => ['text' => '时间格式 Datetime', 'data-types' => ['datetime', 'int(11)']],
            'Select' => ['text' => '下拉框 Select', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'DelaySelect' => ['text' => '异步下拉 DelaySelect', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'RadioGroup' => ['text' => '单选组 RadioGroup', 'data-types' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            'CheckGroup' => ['text' => '多选组 CheckGroup', 'data-types' => ['varchar(200)', 'int(11)', 'text', 'longtext', 'json'], 'data-default' => 'json'],
            'Remote' => ['text' => '远程验证输入框 Remote', 'data-types' => ['varchar(200)', 'text']],
            'Linkage' => ['text' => '联动下拉 Linkage', 'data-types' => ['varchar(200)', 'text', 'json'], 'data-default' => 'json'],
            'Textarea' => ['text' => '备注型 Textarea', 'data-types' => ['text', 'varchar(200)', 'longtext'], 'data-default' => 'text'],
            'UpFile' => ['text' => '文件上传 UpFile', 'data-types' => ['varchar(200)', 'text', 'json']],
            'UpImage' => ['text' => '图片上传 UpImage', 'data-types' => ['varchar(300)', 'text', 'json']],
            'XhEditor' => ['text' => 'Xh编辑器 XhEditor', 'data-types' => ['text', 'longtext']],
            'Tinymce' => ['text' => 'tiny编辑器 Tinymce', 'data-types' => ['text', 'longtext']],
            'SelectDialog' => ['text' => '选择对话框 SelectDialog', 'data-types' => ['int(11)', 'varchar(200)','json'], 'data-default' => 'int'],
            'MultiDialog' => ['text' => '多选对话框 MultiDialog', 'data-types' => ['text', 'varchar(200)', 'json']],
            'Line' => ['text' => '分割行 Line', 'data-types' => ['none']],
            'Label' => ['text' => '标签 Label', 'data-types' => ['none']],
            'Button' => ['text' => '按钮 Button', 'data-types' => ['none']],
            'Container' => ['text' => '多行容器 Container', 'data-types' => ['text', 'json']],
            'Single' => ['text' => '单行容器 Single', 'data-types' => ['text', 'json']],
            'Transfer' => ['text' => '穿梭框 Transfer', 'data-types' => ['int(11)', 'tinyint(4)', 'varchar(200)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json']],
        ];
        $paths = [];
        $paths[] = Util::path(TOOL_DIR, 'support');
        //扫描wj008目录,插件会装到这里
        $tempPath = Util::path(TOOL_DIR, '../../', 'wj008');
        if (is_dir($tempPath)) {
            $directory = new \RecursiveDirectoryIterator($tempPath, \RecursiveDirectoryIterator::SKIP_DOTS);
            foreach ($directory as $dir) {
                if ($dir->isDir()) {
                    $baseName = $dir->getBasename();
                    if ($baseName == 'beacon' || $baseName == 'sdopx'|| $baseName == 'install') {
                        continue;
                    }
                    $temp = Util::path($dir->getPathname(), 'tool/support');
                    if (is_dir($temp)) {
                        $paths[] = $temp;
                    }
                }
            }
        }
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
                /**@var \SplFileInfo $fileInfo */
                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile() && $fileInfo->getExtension() == 'php') {
                        $name = $fileInfo->getBasename('.php');
                        if (isset($data[$name])) {
                            continue;
                        }
                        try {
                            $class = 'tool\\support\\' . $name;
                            $refClass = new \ReflectionClass($class);
                            $temp = $refClass->getAttributes(Support::class);
                            if (isset($temp[0])) {
                                /**@var Support $supper */
                                $supper = $temp[0]->newInstance();
                                $data[$name] = ['text' => $supper->name, 'data-types' => $supper->types];
                            }
                        } catch (\ReflectionException) {
                            continue;
                        }
                    }
                }
            }
        }
        foreach ($data as $key => &$opt) {
            $opt['value'] = $key;
        }
        $data = array_values($data);
        return $data;
    }

    /**
     * @return int
     * @throws DBException
     */
    public static function sortDefaultFunc(): int
    {
        $formId = Request::get('formId:i', 0);
        $num = DB::getMax('@pf_tool_field', 'sort', 'formId=?', $formId);
        if (empty($num)) {
            return 10;
        }
        return intval($num) + 10;
    }

    /**
     * @param string $value
     * @return array
     * @throws DBException
     */
    public static function nameValidFunc(string $value)
    {
        $id = Request::param('id:i', 0);
        $formId = Request::param('formId:i', 0);
        $row = DB::getRow('select id from @pf_tool_field where `name`=? and id<>? and formId=?', [$value, $id, $formId]);
        if ($row) {
            return [false, '字段名称已经存在'];
        }
        return [true, '字段名称可以使用'];
    }

}