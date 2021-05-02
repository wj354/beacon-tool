<?php


namespace tool\model;

use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Field;
use beacon\core\Form;
use beacon\core\Request;
use beacon\widget\Button;
use beacon\widget\Check;
use beacon\widget\CheckGroup;
use beacon\widget\Container;
use beacon\widget\RadioGroup;
use beacon\widget\Select;
use beacon\widget\SelectDialog;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '应用表单', table: '@pf_tool_form', template: 'form/app_form.tpl')]
class AppFormModel
{
    #[Text(
        label: '表单名称',
        validRule: ['r' => '请输入表单名称'],
        star: true,
        tabIndex: 'base'
    )]
    public string $title = '';

    #[Button(
        label: '翻译',
        attrs: [
        'href' => '~/translate/index',
        'yee-module' => 'ajax',
        'on-before' => 'data.param[\'text\']=$(\'#title\').val()||\'\';',
        'on-success' => 'if(ret){$(\'#key\').val(ret.camel);$(\'#tbName\').val(ret.under);}',
    ],
        viewMerge: -1,
        tabIndex: 'base',
    )]
    public string $btn1 = '';

    #[Text(
        label: '模型关键字',
        validRule: ['r' => '没有填写模型关键字', 'regex' => ['^[A-Z][A-Za-z0-9]+$', '模型标识只能使用大写字母开头的数字及字母组合']],
        star: true,
        prompt: '创建后不可更改，并具有唯一性，与文档的模板相关连，建议由英文、数字组成，因为部份Unix系统无法识别中文文件',
        attrs: ['yee-module' => 'remote', 'data-url' => '~/app_form/check_key'],
        validFunc: [self::class, 'validKeyFunc'],
        tabIndex: 'base'
    )]
    public string $key = '';

    #[SelectDialog(
        label: '所属项目',
        validRule: ['r' => '没有选择项目'],
        url: '~/index/select',
        star: true,
        attrs: ['yee-module' => 'remote', 'data-width' => 860, 'style' => 'width:350px'],
        textFunc: [self::class, 'keyTextFunc'],
        defaultFunc: [self::class, 'appIdDefaultFunc'],
        tabIndex: 'base'
    )]
    public ?int $appId = null;

    #[RadioGroup(
        label: '选择模式',
        offEdit: true,
        star: true,
        options: [
        ['value' => 0, 'text' => '普通模式', 'tips' => '，表单形式使用'],
        ['value' => 1, 'text' => '容器插件', 'tips' => '，即该表单仅作为容器插件使用'],
        ['value' => 4, 'text' => '继承表模式', 'tips' => '，使用继承的表'],
        //   ['value' => 2, 'text' => '分类层级', 'tips' => '，比如分类结构'],
    ],
        dynamic: [
        [
            'eq' => 0,
            'show' => 'tbName,tbCreate,tbEngine,useAjax,viewBtns,validateMode',
            'hide' => 'tbNameEx,extFields,plugMode,plugStyle,useWrap',
        ],
        [
            'eq' => 1,
            'show' => 'plugMode,plugStyle,useWrap',
            'hide' => 'tbNameEx,extFields,tbName,tbCreate,tbEngine,useAjax,viewBtns,validateMode',
        ],
        [
            'eq' => 2,
            'show' => 'tbName,tbCreate,tbEngine,useAjax,viewBtns,validateMode',
            'hide' => 'tbNameEx,extFields,plugMode,plugStyle,useWrap',
        ],
        [
            'eq' => 4,
            'show' => 'tbNameEx,useAjax,viewBtns,validateMode',
            'hide' => 'tbName,extFields,plugMode,plugStyle,useWrap,tbCreate,tbEngine',
        ],

    ],
        tabIndex: 'base'
    )]
    public int $extMode = 0;


    #[Select(
        label: '容器模板模式',
        star: true,
        options: [
        ['value' => 'container', 'text' => 'Container 多行模式'],
        ['value' => 'single', 'text' => 'Single 简单模式'],
    ],
        tabIndex: 'base'
    )]
    public string $plugMode = 'container';

    #[Select(
        label: '布局样式',
        star: true,
        options: [
        ['value' => 0, 'text' => '稀疏布局'],
        ['value' => 1, 'text' => '单行布局'],
        ['value' => 2, 'text' => '紧凑布局'],
    ],
        tabIndex: 'base'
    )]
    public int $plugStyle = 0;

    #[Check(
        label: '使用行外层包裹',
        after: '勾选后外层行包裹',
        tabIndex: 'base'
    )]
    public bool $useWrap = false;

    #[Text(
        label: '数据库表名称',
        validRule: ['r' => '没有填数据库名称', 'regex' => ['^[a-z][a-z0-9_]+$', '数据库名称为小写字母下划线数字组合']],
        star: true,
        tabIndex: 'base'
    )]
    public string $tbName = '';

    #[Select(
        label: '选择继承表',
        offEdit: true,
        star: true,
        header: '选择要继承的数据表',
        optionFunc: [self::class, 'tbNameExOptions'],
        prompt: '选择要继承的数据表',
        tabIndex: 'base'
    )]
    public string $tbNameEx = '';

    #[Check(
        label: '是否创建数据表',
        offEdit: true,
        after: '勾选后将会创建对应的数据库表',
        tabIndex: 'base'
    )]
    public int $tbCreate = 1;

    #[Select(
        label: '数据库存储引擎',
        offEdit: true,
        star: true,
        options: [
        ['value' => 'InnoDB', 'text' => 'InnoDB'],
        ['value' => 'MyISAM', 'text' => 'MyISAM'],
    ],
        tabIndex: 'base'
    )]
    public string $tbEngine = 'InnoDB';

    #[Check(
        label: '使用AJAX提交',
        after: '勾选使用AJAX提交表单',
        tabIndex: 'base'
    )]
    public int $useAjax = 1;

    #[CheckGroup(
        label: '表单其他按钮',
        options: [
        ['value' => 1, 'text' => '返回按钮'],
        ['value' => 2, 'text' => '关闭按钮'],
        ['value' => 3, 'text' => '重置按钮'],
    ],
        tabIndex: 'base'
    )]
    public array $viewBtns = [1];

    #[RadioGroup(
        label: '表单验证提示方式',
        options: [
        ['value' => 0, 'text' => '默认方式'],
        ['value' => 2, 'text' => '对话框(单条)'],
        ['value' => 1, 'text' => '对话框(多条)'],
    ],
        tabIndex: 'base'
    )]
    public int $validateMode = 0;

    #[Text(
        label: '模板文件',
        prompt: '如果不填写，则使用生成的模板',
        tabIndex: 'base'
    )]
    public string $template = '';

    #[Text(
        label: '皮肤文件Layout',
        prompt: '父页面皮肤文件，如果为空 默认使用系统皮肤文件 layout/form.tpl',
        tabIndex: 'base'
    )]
    public string $baseLayout = 'layout/form.tpl';

    #[Check(
        label: '生成模板',
        after: '勾选生成模板文件',
        dynamic: [
        [
            'eq' => 1,
            'show' => 'makeStatic',
        ],
        [
            'neq' => 1,
            'hide' => 'makeStatic',
        ],
    ],
        tabIndex: 'base'
    )]
    public int $withTpl = 1;

    #[Check(
        label: '生成表单',
        after: '勾选生成Form文件',
        dynamic: [
        [
            'eq' => 1,
            'show' => 'makeStatic',
        ],
        [
            'neq' => 1,
            'hide' => 'makeStatic',
        ],
    ],
        viewMerge: -1,
        tabIndex: 'base'
    )]
    public int $withForm = 1;

    #[RadioGroup(
        label: '静态生成',
        star: true,
        options: [['value' => 0, 'text' => '不生成静态'], ['value' => 1, 'text' => '尽可能生成静态'], ['value' => 2, 'text' => '部分生成静态']],
        tabIndex: 'base'
    )]
    public int $makeStatic = 1;

    #[Check(
        label: '是否分栏',
        after: '勾选开启分栏',
        dynamic: [
        [
            'eq' => 1,
            'show' => 'viewTabs',
        ],
        [
            'neq' => 1,
            'hide' => 'viewTabs',
        ],
    ],
        tabIndex: 'extend'
    )]
    public int $viewUseTab = 0;

    #[Container(label: '分栏栏目', itemClass: FormTabPlugin::class, tabIndex: 'extend')]
    public array $viewTabs = [];

    #[Textarea(
        label: '表单头',
        prompt: '不填写默认使用标题',
        tabIndex: 'extend'
    )]
    public string $caption = '';

    #[Textarea(
        label: '头部说明(介绍)',
        prompt: '在表单头部的说明文本',
        tabIndex: 'extend'
    )]
    public string $description = '';

    #[Textarea(
        label: '提示信息(提示)',
        prompt: '在底部的提示说明帮助',
        tabIndex: 'extend'
    )]
    public string $information = '';

    #[Textarea(
        label: '警告提示(警告)',
        prompt: '在底部的警告提示说明帮助',
        tabIndex: 'extend'
    )]
    public string $attention = '';

    #[Textarea(
        label: 'HEAD区',
        prompt: 'HTML 头部区，可放入JS CSS 等资源',
        tabIndex: 'extend'
    )]
    public string $head = '';

    #[Textarea(
        label: '脚本代码',
        prompt: '需要在页面中执行的JS代码',
        tabIndex: 'extend',
        attrs: ['style' => 'width:600px; height:200px;']
    )]
    public string $script = '';

    /**
     * @param $value
     * @return array
     * @throws DBException
     */
    public static function validKeyFunc($value): array
    {
        $id = Request::param('id:i', 0);
        $appId = Request::param('appId:i', 0);
        $row = DB::getRow('select id from @pf_tool_form where `key`=? and id<>? and appId=?', [$value, $id, $appId]);
        if ($row) {
            return [false, '表单标识符已经存在'];
        }
        return [true, '表单标识符可以使用'];
    }

    /**
     * @param $id
     * @return string
     * @throws DBException
     */
    public static function keyTextFunc($id): string
    {
        $row = DB::getRow('select `name`,`namespace` from @pf_tool_app where  id=?', $id);
        if ($row) {
            return $row['name'] . ' (' . $row['namespace'] . ')';
        }
        return '';
    }

    /**
     * @return int
     * @throws DBException
     */
    public static function appIdDefaultFunc(): int
    {
        $appId = Request::get('appId:i', 0);
        if (empty($appId)) {
            $appId = DB::getOne('select id from @pf_tool_app order by isDefault desc,id desc limit 0,1');
            if ($appId == null) {
                $appId = 0;
            }
        }
        return intval($appId);
    }

    /**
     * @return array
     * @throws DBException
     */
    public static function tbNameExOptions(): array
    {
        return DB::getList('select tbName as value,concat(title,\' | \',tbName) as text from @pf_tool_form where extMode=0 or extMode=2');
    }


}