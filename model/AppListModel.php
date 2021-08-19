<?php


namespace tool\model;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\core\Request;
use beacon\widget\Button;
use beacon\widget\Check;
use beacon\widget\CheckGroup;
use beacon\widget\Container;
use beacon\widget\Integer;
use beacon\widget\Line;
use beacon\widget\Number;
use beacon\widget\Select;
use beacon\widget\SelectDialog;
use beacon\widget\Text;
use beacon\widget\Textarea;

#[Form(title: '应用列表', table: '@pf_tool_list', template: 'form/app_list.tpl')]
class AppListModel
{
    #[Select(
        label: '选择表单模型',
        validRule: ['r' => '请选择表单模型'],
        header: '请选择表单模型',
        tabIndex: 'base',
        attrs: ['data-url' => '~/AppList/get_field'],
        optionFunc: [self::class, 'formIdOptions']
    )]
    public ?int $formId = null;

    #[Text(
        label: '列表标识符',
        validRule: ['r' => '没有填写列表标识符', 'regex' => ['^[A-Z][A-Za-z0-9]+$', '列表标识符只能使用大写字母开头的数字及字母组合']],
        star: true,
        prompt: '创建后不可更改，并具有唯一性，与文档的模板相关连，建议由英文、数字组成，因为部份Unix系统无法识别中文文件',
        attrs: ['yee-module' => 'remote', 'data-url' => '~/app_list/check_key'],
        validFunc: [self::class, 'validKeyFunc'],
        tabIndex: 'base'
    )]
    public string $key = '';

    #[Text(
        label: '列表标题名称',
        validRule: ['r' => '请输入列表名称'],
        star: true,
        tabIndex: 'base'
    )]
    public string $title = '';

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

    #[Container(
        label: '字段信息',
        itemClass: ListFieldPlugin::class,
        tabIndex: 'base'
    )]
    public array $fields = [];

    #[Check(
        label: '可调整列',
        after: '勾选拖动可调整列宽',
        tabIndex: 'base'
    )]
    public bool $listResize = false;

    #[Check(
        label: '改写地址栏',
        after: '勾选后改写地址栏，刷新可以直接定位页面',
        tabIndex: 'base'
    )]
    public bool $listRewrite = false;

    #[Check(
        label: '可固定列',
        after: '勾选可固定列',
        tabIndex: 'base',
        dynamic: [
            [
                'eq' => 1,
                'show' => 'leftFixed,rightFixed',
            ],
            [
                'neq' => 1,
                'hide' => 'leftFixed,rightFixed',
            ],
        ],
    )]
    public bool $fixed = false;

    #[Number(
        label: '左固定列数',
        tabIndex: 'base',
        viewMerge: -1,
        attrs: ['style' => 'width:60px']
    )]
    public int $leftFixed = 0;

    #[Number(
        label: '右固定列数',
        tabIndex: 'base',
        viewMerge: -1,
        attrs: ['style' => 'width:60px']
    )]
    public int $rightFixed = 0;

    #[Check(
        label: '是否使用分页',
        after: '勾选使用分页',
        tabIndex: 'base',
        dynamic: [
            [
                'eq' => 1,
                'show' => 'pageSize',
            ],
            [
                'neq' => 1,
                'hide' => 'pageSize',
            ],
        ]
    )]
    public bool $usePageList = true;

    #[Number(
        label: '每页记录数',
        tabIndex: 'base',
        viewMerge: -1
    )]
    public int $pageSize = 20;

    #[Text(
        label: '控制器继承于',
        prompt: '将会生托管生成一个同名控制器继承于此控制器',
        tabIndex: 'base',
    )]
    public string $baseController = '';

    #[Check(
        label: '使用自定义模板',
        after: '勾选使用自定义模板',
        tabIndex: 'base',
        dynamic: [
            [
                'eq' => 1,
                'show' => 'templateHook,template',
            ],
            [
                'neq' => 1,
                'hide' => 'templateHook,template',
            ],
        ]
    )]
    public bool $useCustomTemplate = false;

    #[Text(
        label: '数据修饰模板',
        prompt: '用于修饰数据格式的模板',
        tabIndex: 'base',
    )]
    public string $templateHook = '';
    #[Text(
        label: '列表模板',
        prompt: '列表使用的模板,使用模板后其他列表设置无效',
        tabIndex: 'base',
    )]
    public string $template = '';

    #[Text(
        label: '皮肤文件Layout',
        prompt: '父页面皮肤文件，如果为空 默认使用系统皮肤文件 layout/list.tpl',
        tabIndex: 'base',
    )]
    public string $baseLayout = '';

    #[Check(
        label: '生成模板',
        after: '勾选生成控制器文件',
        tabIndex: 'base'
    )]
    public bool $withTpl = true;
    #[Check(
        label: '生成控制器',
        after: '勾选生成控制器文件',
        tabIndex: 'base'
    )]
    public bool $withCtl = true;
    #[Check(
        label: '生成搜索',
        after: '勾选生成搜索表单',
        tabIndex: 'base'
    )]
    public bool $withSearch = true;

    #数据区================
    #[Text(
        label: '主表',
        tabIndex: 'data',
        attrs: ['disabled' => 'disabled'],
    )]
    public string $tbName = '';

    #[Text(
        label: '主表别名',
        validRule: ['regex' => ['^[A-Z]+$', '表别名只能是大写字母']],
        attrs: ['placeholder' => '别名', 'style' => 'width:100px;'],
        viewMerge: -1,
        tabIndex: 'data'
    )]
    public string $tbAlias = '';

    #[Textarea(
        label: '查询字段',
        attrs: ['placeholder' => '查询字段,为空则为 *', 'spellcheck' => 'false'],
        tabIndex: 'data'
    )]
    public string $tbField = '';

    #[Button(
        label: '选择',
        viewMerge: -1,
        attrs: [
            'yee-module' => 'dialog',
            'data-width' => 600,
            'data-height' => 800,
            'data-carry' => 'formId,tbField,tbAlias',
            'on-success' => "$('#tbField').val(ret);",
            'href' => '~/AppList/db_field'
        ],
        tabIndex: 'data'
    )]
    public string $button2 = '';

    #[Container(
        label: '附加表',
        itemClass: TbJoinPlugin::class,
        tabIndex: 'data'
    )]
    public array $tbJoin = [];

    #[Textarea(
        label: '查询条件',
        attrs: ['placeholder' => '如 and `name`=\'wj008\''],
        tabIndex: 'data'
    )]
    public string $tbWhere = '';

    #[Textarea(
        label: '数据排序',
        attrs: ['placeholder' => '如 sort desc,id asc', 'spellcheck' => 'false'],
        tabIndex: 'data'
    )]
    public string $tbOrder = 'id desc';
    #静态函数================

    #[Line(label: '路由控制器方法', tabIndex: 'operate')]
    public string $actionLine = '';


    #[CheckGroup(
        label: '选择创建方法',
        tabIndex: 'operate',
        options: [
            ['value' => 'add', 'text' => '添加 add'],
            ['value' => 'sort', 'text' => '排序 sort'],
            ['value' => 'toggleAllow', 'text' => '审核/禁用 toggleAllow'],
            ['value' => 'edit', 'text' => '编辑 edit'],
            ['value' => 'delete', 'text' => '删除 delete'],
            ['value' => 'deleteChoice', 'text' => '删除所选 deleteChoice'],
            ['value' => 'allowChoice', 'text' => '审核所选 allowChoice'],
            ['value' => 'revokeChoice', 'text' => '禁用所选 revokeChoice'],
        ]
    )]
    public array $actions = [];

    #[Line(label: '顶部操作区', tabIndex: 'operate')]
    public string $topLine = '';

    #[Container(
        label: '顶部右侧操作区域',
        tabIndex: 'operate',
        itemClass: TopBtnPlugin::class
    )]
    public array $topButtons = [];

    #[Line(label: '列表操作区', tabIndex: 'operate')]
    public string $listLine = '';

    #[Text(
        label: '操作区TH列标题',
        attrs: ['style' => 'width:160px;'],
        tabIndex: 'operate',
        prompt: '如果不需要列表操作区，此处留空'
    )]
    public string $thTitle = '操作';

    #[Text(
        label: '修饰参数名',
        attrs: ['style' => 'width:160px;'],
        viewMerge: -1,
        tabIndex: 'operate',
        prompt: '操作区修饰键名'
    )]
    public string $thOpName = '_operate';

    #[Select(
        label: 'TH对齐',
        options: [['center', 'center'], ['left', 'left'], ['right', 'right']],
        tabIndex: 'operate',
        prompt: '操作区修饰键名'
    )]
    public string $thAlign = 'right';

    #[Integer(
        label: '宽',
        attrs: ['style' => 'width:50px;'],
        viewMerge: -1,
        tabIndex: 'operate',
    )]
    public int $thWidth = 240;

    #[Text(
        label: '其他属性',
        viewMerge: -1,
        tabIndex: 'operate',
    )]
    public string $thAttrs = '';

    #[Select(
        label: 'TH对齐',
        options: [['center', 'center'], ['left', 'left'], ['right', 'right']],
        tabIndex: 'operate',
        prompt: '操作区修饰键名'
    )]
    public string $tdAlign = 'right';

    #[Text(
        label: '其他属性',
        viewMerge: -1,
        tabIndex: 'operate',
    )]
    public string $tdAttrs = '';

    #[Container(
        label: '顶部右侧操作区域',
        tabIndex: 'operate',
        itemClass: ListBtnPlugin::class
    )]
    public array $listButtons = [];

    #[Check(
        label: '是否支持全选',
        after: '勾选支持全选',
        tabIndex: 'operate',
    )]
    public bool $useSelect = false;

    #[Text(
        label: '全选checkbox项值',
        tabIndex: 'operate',
        viewMerge: -1,
    )]
    public string $selectValue = '{$rs.id}';

    #[Select(
        label: '操作按钮位置',
        tabIndex: 'operate',
        options: [['search', '搜索区右侧'], ['buttom', '列表底部'], ['top', '列表顶部']]
    )]
    public string $selectType = '';

    #[Text(
        label: '全选按钮域样式',
        tabIndex: 'operate',
        viewMerge: -1,
    )]
    public string $selectStyle = '';

    #[Container(
        label: '全选区域操作',
        tabIndex: 'operate',
        itemClass: SelectBtnPlugin::class
    )]
    public array $selectButtons = [];

    #[Textarea(
        label: '搜索区顶部代码',
        tabIndex: 'operate',
        attrs: ['style' => 'width:700px; height:120px;', 'yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $searchTop = '';

    #[Line(label: '模板其他', tabIndex: 'other')]
    public string $templateLine = '';

    #[Textarea(
        label: '标题代码',
        tabIndex: 'other',
        attrs: ['style' => 'width:700px; height:60px;', 'yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $caption = '';

    #[Check(
        label: '是否分栏',
        tabIndex: 'other',
        after: '勾选开启分栏',
        dynamic: [
            [
                'eq' => 1,
                'show' => 'viewTabs,viewTabRight,viewTabCode',
            ],
            [
                'neq' => 1,
                'hide' => 'viewTabs,viewTabRight,viewTabCode',
            ],
        ]
    )]
    public bool $viewUseTab = false;

    #[Container(
        label: '分栏栏目',
        tabIndex: 'other',
        itemClass: ListTabPlugin::class
    )]
    public array $viewTabs = [];


    #[Textarea(
        label: '分栏左侧',
        tabIndex: 'other',
        attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $viewTabCode = '';

    #[Textarea(
        label: '分栏右侧',
        tabIndex: 'other',
        attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $viewTabRight = '';

    #[Textarea(
        label: '页面head区域模板',
        tabIndex: 'other',
        prompt: '可放置脚本样式等引用', attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $headTemplate = '';

    #[Textarea(
        label: '页面底部区域模板',
        tabIndex: 'other',
        prompt: '可放置底部脚本，或其他版权等信息',
        attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $footTemplate = '';


    #[Textarea(
        label: '提示信息',
        tabIndex: 'other',
        prompt: '在底部的提示说明帮助',
        attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $information = '';

    #[Textarea(
        label: '警告提示',
        tabIndex: 'other',
        prompt: '在底部的警告提示说明帮助',
        attrs: ['yee-module' => 'code-editor', 'class' => 'form-inp textarea code-editor', 'data-lang' => 'smarty']
    )]
    public string $attention = '';

    #静态函数================


    /**
     * @param int $appId
     * @return string
     * @throws DBException
     */
    protected static function getAppName(int $appId = 0): string
    {
        static $cache = [];
        if (isset($cache[$appId])) {
            return $cache[$appId];
        }
        $app = DB::getRow('select name from @pf_tool_app where id=?', $appId);
        if ($app) {
            $cache[$appId] = $app['name'];
        } else {
            $cache[$appId] = '';
        }
        return $cache[$appId];
    }

    /**
     * @return array
     * @throws DBException
     */
    public static function formIdOptions(): array
    {
        $options = [];
        $rows = DB::getList('select * from @pf_tool_form where extMode<>1 order by id desc');
        foreach ($rows as $rs) {
            $appName = self::getAppName($rs['appId']);// DB::getRow('select name from @pf_tool_app where id=?', $rs['appId']);
            $item = [];
            $item['value'] = $rs['id'] ?? '';
            if (!empty($appName)) {
                $item['text'] = $appName . ' : ' . ($rs['title'] ?? '');
            } else {
                $item['text'] = $rs['title'] ?? '';
            }
            $item['text'] .= '|' . ($rs['key'] ?? '');
            $options[] = $item;
        }
        return $options;
    }


    /**
     * @param $value
     * @return array
     * @throws DBException
     */
    public static function validKeyFunc($value): array
    {
        $id = Request::param('id:i', 0);
        $appId = Request::param('appId:i', 0);
        $row = DB::getRow('select id from @pf_tool_list where `key`=? and id<>? and appId=?', [$value, $id, $appId]);
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
}