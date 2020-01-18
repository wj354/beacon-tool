<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-28
 * Time: 下午10:22
 */

namespace tool\form;


use beacon\Config;
use beacon\DB;
use beacon\Form;
use beacon\Route;
use beacon\Request;
use tool\plugin\ListBtn;
use tool\plugin\ListField;
use tool\plugin\ListTab;
use tool\plugin\SelectBtn;
use tool\plugin\TbJoin;
use tool\plugin\TopBtn;

class ListForm extends Form
{
    public $title = '应用列表';
    public $template = 'List.form.tpl';
    public $tbName = '@pf_tool_list';

    protected function load()
    {
        return [
            'formId' => [
                'label' => '选择表单模型',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请选择表单模型'],
                'type' => 'select',
                'header' => '请选择表单模型',
                'options' => function () {
                    $options = [];
                    $rows = DB::getList('select * from @pf_tool_form where extMode<>1 order by id desc');
                    foreach ($rows as $rs) {
                        $item = [];
                        $item[] = isset($rs['id']) ? $rs['id'] : '';
                        $item[] = isset($rs['title']) ? $rs['title'] : '';
                        $item[] = isset($rs['key']) ? $rs['key'] : '';
                        $options[] = $item;
                    }
                    return $options;
                },
                'tab-index' => 'base',
            ],

            'key' => [
                'label' => '列表标识符',
                'data-val-rule' => ['r' => true, 'regex' => '^[A-Z][A-Za-z0-9]+$'],
                'data-val-message' => ['r' => '没有填写模型关键字！', 'regex' => '模型标识只能使用大写字母开头的数字及字母组合。'],
                'tips' => '创建后不可更改，并具有唯一性，与文档的模板相关连，建议由英文、数字组成，因为部份Unix系统无法识别中文文件',
                'type' => 'remote',
                'data-url' => Route::url('~/Lists/checkKey'),
                'data-method' => 'post',
                'data-bind' => 'eid',
                //  'off-edit' => true,
                'valid-func' => function ($value) {
                    $id = Request::param('id:i', 0);
                    $appId = Request::param('appId:i', 0);
                    $row = DB::getRow('select id from @pf_tool_list where `key`=? and appId=? and id<>?', [$value, $appId, $id]);
                    if ($row) {
                        return '列表标识符已经存在';
                    }
                    return null;
                },
                'tab-index' => 'base',
            ],

            'title' => [
                'label' => '列表名称',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入表单名称'],
                'tab-index' => 'base',
            ],

            'appId' => [
                'label' => '所属项目', //标题
                'type' => 'select-dialog', //下拉框
                'default' => 0,
                'tab-index' => 'base',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '没有选择项目'],
                'data-url' => Route::url('~/Index/select'),
                'data-width' => 860,
                'box-style' => 'width:350px',
                'text-func' => function ($value) {
                    $row = DB::getRow('select `name`,`namespace` from @pf_tool_app where  id=?', $value);
                    if ($row) {
                        return $row['name'] . ' (' . $row['namespace'] . ')';
                    }
                    return '';
                },
                'default' => function () {
                    $appId = Request::get('appId:i', 0);
                    if (empty($appId)) {
                        $appId = DB::getOne('select id from @pf_tool_app order by isDefault desc,id desc limit 0,1');
                        if ($appId == null) {
                            $appId = 0;
                        }
                    }
                    return intval($appId);
                }
            ],


            'fields' => [
                'label' => '字段信息 ',
                'plug-name' => ListField::class,
                'type' => 'container',
                'mode' => 'multiple',
                'tab-index' => 'base',
            ],

            'useTwoLine' => [
                'label' => '使用两行',
                'type' => 'check',
                'after-text' => '勾选使用两行,会将最后一列拆到下一行',
                'tab-index' => 'base',
            ],

            'orgFields' => [
                'label' => '其他未修饰字段',
                'type' => 'textarea',
                'box-placeholder' => 'id,name,title',
                'box-style' => 'width:500px;height:20px',
                'tab-index' => 'base',
                'box-class' => 'form-inp mf',
                'tips' => '未修饰的字段只用于模板数据处理使用，并不作为列显示，多个用逗号隔开',
            ],
            'button1' => [
                'label' => '选择',
                'type' => 'button',
                'tab-index' => 'base',
                'view-merge' => -1,
                'box-yee-module' => 'dialog',
                'data-width' => 600,
                'data-height' => 800,
                'data-carry' => '#formId,#orgFields',
                'box-on-success' => "$('#orgFields').val(ret);",
                'box-href' => Route::url(['ctl' => 'Lists', 'act' => 'dbfield', 'pname' => 'orgFields'])
            ],
            'listResize' => [
                'label' => '可调整列',
                'type' => 'check',
                'after-text' => '勾选拖动可调整列宽',
                'tab-index' => 'base',
            ],
            'listRewrite' => [
                'label' => '改写地址栏',
                'type' => 'check',
                'after-text' => '勾选后改写地址栏，刷新可以直接定位页面',
                'tab-index' => 'base',
            ],
            'fixed' => [
                'label' => '可固定列',
                'type' => 'check',
                'after-text' => '勾选可固定列',
                'tab-index' => 'base',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'leftFixed,rightFixed',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'leftFixed,rightFixed',
                    ],
                ],
            ],

            'leftFixed' => [
                'label' => '左固定列数',
                'type' => 'integer',
                'tab-index' => 'base',
                'view-merge' => -1, //合并到上一行
                'box-style' => 'width:60px',
                'default' => 0
            ],

            'rightFixed' => [
                'label' => '右固定列数',
                'type' => 'integer',
                'tab-index' => 'base',
                'view-merge' => -1, //合并到上一行
                'box-style' => 'width:60px',
                'default' => 0
            ],

            'usePageList' => [
                'label' => '是否使用分页',
                'type' => 'check',
                'after-text' => '勾选使用分页',
                'tab-index' => 'base',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'pageSize',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'pageSize',
                    ],
                ],
            ],

            'pageSize' => [
                'label' => '每页记录数 ',
                'type' => 'integer',
                'default' => 20,
                'tab-index' => 'base',
                'view-merge' => -1, //合并到上一行
            ],
            'renderMode' => [
                'label' => '列表渲染引擎',
                'type' => 'select',
                'options' => [['vue', 'Vue渲染'], ['yee', 'Yee渲染']],
                'tab-index' => 'base',
            ],
            'baseController' => [
                'label' => '控制器继承于',
                'tab-index' => 'base',
                'tips' => '将会生托管生成一个同名控制器继承于此控制器',
            ],

            'useCustomTemplate' => [
                'label' => '使用自定义模板',
                'type' => 'check',
                'after-text' => '勾选使用自定义模板',
                'tab-index' => 'base',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'templateHook,template',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'templateHook,template',
                    ],
                ],
            ],

            'templateHook' => [
                'label' => '数据修饰模板',
                'tab-index' => 'base',
                'tips' => '用于修饰数据格式的模板',
            ],
            'template' => [
                'label' => '列表模板',
                'tab-index' => 'base',
                'tips' => '列表使用的模板,使用模板后其他列表设置无效',
            ],
            'baseLayout' => [
                'label' => '皮肤文件Layout',
                'tab-index' => 'base',
                'default' => 'layout/layoutList.tpl',
                'tips' => '父页面皮肤文件，如果为空 默认使用系统皮肤文件 layout/layoutList.tpl',
            ],
            'withTpl' => [
                'label' => '生成模板',
                'type' => 'check',
                'after-text' => '勾选生成模板',
                'tab-index' => 'base',
                'default' => 1,
            ],
            'withCtl' => [
                'label' => '生成控制器',
                'type' => 'check',
                'after-text' => '勾选生成控制器文件',
                'tab-index' => 'base',
                'default' => 1,
                'view-merge' => -1,
            ],
            'withSearch' => [
                'label' => '生成搜索',
                'type' => 'check',
                'after-text' => '勾选生成搜索表单',
                'tab-index' => 'base',
                'default' => 1,
                'view-merge' => -1,
            ],
            'tbName' => [
                'label' => '主表',
                'tab-index' => 'data',
                'box-disabled' => 'disabled',
                'tips' => '主表和所选表单模型保持一致',
            ],

            'tbAlias' => [
                'label' => '主表别名',
                'tab-index' => 'data',
                'box-placeholder' => '别名',
                'view-merge' => -1,
                'data-val-rule' => ['regex' => '^[A-Z]+$'],
                'data-val-message' => ['regex' => '表别名只能是大写字母'],
                'box-style' => 'width:100px;',
            ],

            'tbField' => [
                'label' => '查询字段',
                'type' => 'textarea',
                'box-placeholder' => '查询字段,为空则为 *',
                'tab-index' => 'data',
            ],
            'button2' => [
                'label' => '选择',
                'type' => 'button',
                'tab-index' => 'data',
                'view-merge' => -1,
                'box-yee-module' => 'dialog',
                'data-width' => 600,
                'data-height' => 800,
                'data-carry' => '#formId,#tbField',
                'box-on-success' => "$('#tbField').val(ret);",
                'box-href' => Route::url(['ctl' => 'Lists', 'act' => 'dbfield', 'pname' => 'tbField'])
            ],

            'tbJoin' => [
                'label' => '附加表',
                'type' => 'container',
                'plug-name' => TbJoin::class,
                'box-placeholder' => '附加表',
                'mode' => 'multiple',
                'tab-index' => 'data',
                'viewShowRemoveBtn' => true,
            ],

            'tbWhere' => [
                'label' => '查询条件',
                'type' => 'textarea',
                'box-placeholder' => '如 and `name`=\'wj008\'',
                'tab-index' => 'data',
                'box-class' => 'form-inp mf',
            ],

            'tbOrder' => [
                'label' => '数据排序',
                'type' => 'textarea',
                'tab-index' => 'data',
                'box-placeholder' => '如 sort desc,id asc',
                'default' => 'id desc',
            ],

            'useSqlTemplate' => [
                'label' => '使用SQL模板',
                'after-text' => '勾选使用SQL模板',
                'type' => 'check',
                'tab-index' => 'data',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'sqlTemplate',
                        'hide' => 'tbJoin,tbField,tbOrder,tbWhere',
                    ],
                    [
                        'neq' => 1,
                        'show' => 'tbJoin,tbField,tbOrder,tbWhere',
                        'hide' => 'sqlTemplate',
                    ],
                ],
            ],
            'sqlTemplate' => [
                'label' => 'SQL模板',
                'type' => 'textarea',
                'tips' => '支持模板语法 参数数组：{$param}，条件：{$where|raw}，raw 过滤器可排除SQL编码',
                'box-style' => 'width:700px; height:120px;',
                'tab-index' => 'data',
                'box-class' => 'form-inp mf navy',
            ],
            'sqlCountTemplate' => [
                'label' => '查询数量SQL模板',
                'type' => 'textarea',
                'tips' => '用于加速查询条数的SQL,非必填,支持模板语法，参数数组：{$param}，条件：{$where|raw}，raw 过滤器可排除SQL编码',
                'box-style' => 'width:700px; height:120px;',
                'tab-index' => 'data',
                'box-placeholder' => '如 select count(*) from `@pf_test` where 1=1 {$where|raw}',
                'box-class' => 'form-inp mf navy',
            ],
            'actionLine' => [
                'label' => '公开的方法',
                'type' => 'line',
                'tab-index' => 'operate',
            ],
            'actions' => [
                'label' => '公开方法',
                'type' => 'check-group',
                'options' => Config::get('tool.support_action', []),
                'tab-index' => 'operate',
            ],
            'topLine' => [
                'label' => '顶部操作区',
                'type' => 'line',
                'tab-index' => 'operate',
            ],

            'topButtons' => [
                'label' => '顶部右侧操作区域',
                'type' => 'container',
                'plug-name' => TopBtn::class,
                'mode' => 'multiple',
                'tab-index' => 'operate',
            ],

            'listLine' => [
                'label' => '列表操作区',
                'type' => 'line',
                'tab-index' => 'operate',
            ],
            'thTitle' => [
                'label' => '操作区TH列标题',
                'tips' => '如果不需要列表操作区，此处留空',
                'box-style' => 'width:160px;',
                'tab-index' => 'operate',
                'default' => '操作',
            ],
            'thOpName' => [
                'label' => '修饰参数名',
                'tips' => '操作区修饰键名',
                'box-style' => 'width:160px;',
                'tab-index' => 'operate',
                'default' => '_operate',
                'view-merge' => -1,
            ],
            'thAlign' => [
                'label' => 'TH对齐',
                'type' => 'select',
                'options' => [['', '默认对齐'], ['left', 'left'], ['center', 'center'], ['right', 'right']],
                'tab-index' => 'operate',
                'default' => 'right',
            ],
            'thWidth' => [
                'label' => '宽',
                'view-merge' => -1,
                'box-style' => 'width:50px;',
                'default' => 240,
                'tab-index' => 'operate',
            ],
            'thAttrs' => [
                'label' => '其他属性',
                'view-merge' => -1,
                'tab-index' => 'operate',
                'box-class' => 'form-inp text mf',
            ],

            'tdAlign' => [
                'label' => 'TD对齐',
                'type' => 'select',
                'options' => [['', '默认对齐'], ['left', 'left'], ['center', 'center'], ['right', 'right']],
                'tab-index' => 'operate',
                'default' => 'right',
            ],
            'tdAttrs' => [
                'label' => '其他属性',
                'view-merge' => -1,
                'tab-index' => 'operate',
                'box-class' => 'form-inp text mf',
            ],

            'listButtons' => [
                'label' => '列表操作区域',
                'type' => 'container',
                'plug-name' => ListBtn::class,
                'mode' => 'multiple',
                'tab-index' => 'operate',
            ],

            'selectLine' => [
                'label' => '全选操作区',
                'type' => 'line',
                'tab-index' => 'operate',
            ],

            'useSelect' => [
                'label' => '是否支持全选',
                'type' => 'check',
                'after-text' => '勾选支持全选',
                'tab-index' => 'operate',
            ],

            'selectType' => [
                'label' => '全选按钮位置',
                'type' => 'select',
                'options' => [['search', '搜索区右侧'], ['buttom', '列表底部'], ['top', '列表顶部']],
                'tab-index' => 'operate',
            ],

            'selectStyle' => [
                'label' => '全选域样式',
                'type' => 'text',
                'tab-index' => 'operate',
                'view-merge' => -1,
            ],

            'selectButtons' => [
                'label' => '全选区域操作',
                'type' => 'container',
                'plug-name' => SelectBtn::class,
                'mode' => 'multiple',
                'tab-index' => 'operate',
            ],

            'searchTop' => [
                'label' => '搜索区顶部代码',
                'type' => 'textarea',
                'tab-index' => 'operate',
                'box-style' => 'width:700px; height:120px;',
                'box-class' => 'form-inp textarea mf',
            ],

            'templateLine' => [
                'label' => '模板其他',
                'type' => 'line',
                'tab-index' => 'other',
            ],

            'caption' => [
                'label' => '标题代码',
                'type' => 'textarea',
                'tab-index' => 'other',
                'box-style' => 'width:700px; height:60px;',
                'box-class' => 'form-inp textarea mf',
            ],
            'viewUseTab' => [
                'label' => '是否分栏', //标题
                'type' => 'check', // 这里是一个 checkbox
                'default' => 0, //默认 选中
                'after-text' => '勾选开启分栏', //在输入框尾部添加一个提示内容
                'tab-index' => 'other',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'viewTabs,viewTabRight',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'viewTabs,viewTabRight',
                    ],
                ],
            ],

            'viewTabs' => [
                'label' => '分栏栏目', //标题
                'type' => 'container',
                'plug-name' => ListTab::class,
                'mode' => 'multiple',
                'tab-index' => 'other',
            ],

            'viewTabRight' => [
                'label' => '分栏右侧', //标题
                'type' => 'textarea',
                'tab-index' => 'other',
                'box-class' => 'form-inp textarea mf',
            ],

            'headTemplate' => [
                'label' => '页面head区域模板',
                'type' => 'textarea',
                'tips' => '可放置脚本样式等引用',
                'tab-index' => 'other',
                'box-class' => 'form-inp textarea mf',
            ],

            'footTemplate' => [
                'label' => '页面底部区域模板',
                'type' => 'textarea',
                'tips' => '可放置底部脚本，或其他版权等信息',
                'tab-index' => 'other',
                'box-class' => 'form-inp textarea mf',
            ],

            'information' => [
                'label' => '提示信息',
                'type' => 'textarea',
                'tips' => '在底部的提示说明帮助',
                'tab-index' => 'other',
                'box-class' => 'form-inp textarea mf',
            ],

            'attention' => [
                'label' => '警告提示',
                'type' => 'textarea',
                'tips' => '在底部的警告提示说明帮助',
                'tab-index' => 'other',
                'box-class' => 'form-inp textarea mf',
            ],

        ];
    }
}