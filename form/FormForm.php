<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-27
 * Time: 下午10:04
 */

namespace tool\form;


use beacon\DB;
use beacon\Request;
use beacon\Route;
use tool\plugin\FormTab;

class FormForm extends \beacon\Form
{
    public $title = '应用表单';
    public $template = 'Form.form.tpl';
    public $tbName = '@pf_tool_form';

    protected function load()
    {
        return [
            'title' => [
                'label' => '表单名称',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入表单名称'],
                'tab-index' => 'base',
            ],
            'btn1' => [
                'label' => '翻译',
                'type' => 'button',
                'box-href' => Route::url('~/S/translate'),
                'box-yee-module' => 'ajax',
                'box-on-before' => 'option.param[\'text\']=$(\'#title\').val()||\'\';',
                'box-on-success' => 'if(ret){$(\'#key\').val(ret.camel);$(\'#tbName\').val(ret.under);}',
                'data-carry' => '#label',
                'view-merge' => -1, //合并到上一行
                'tab-index' => 'base',
            ],
            'key' => [
                'label' => '模型关键字',
                'data-val-rule' => ['r' => true, 'regex' => '^[A-Z][A-Za-z0-9]+$'],
                'data-val-message' => ['r' => '没有填写模型关键字！', 'regex' => '模型标识只能使用大写字母开头的数字及字母组合。'],
                'tips' => '创建后不可更改，并具有唯一性，与文档的模板相关连，建议由英文、数字组成，因为部份Unix系统无法识别中文文件',
                //'off-edit' => true,
                'type' => 'remote',
                'data-url' => Route::url('~/Form/checkKey'),
                'data-method' => 'post',
                'data-bind' => 'eid',
                'valid-func' => function ($value) {
                    //Logger::log($value);
                    $id = Request::param('id:i', 0);
                    $appId = Request::param('appId:i', 0);
                    $row = DB::getRow('select id from @pf_tool_form where `key`=? and id<>? and appId=?', [$value, $id, $appId]);
                    if ($row) {
                        return '表单标识符已经存在';
                    }
                    return null;
                },
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

            'extMode' => [
                'label' => '选择模式',
                'off-edit' => true,
                'type' => 'radio-group',
                'box-style' => 'width:230px; clear:both;',
                'options' => [
                    ['value' => 0, 'text' => '普通模式', 'tips' => '，表单形式使用'],
                    ['value' => 1, 'text' => '容器插件', 'tips' => '，即该表单仅作为容器插件使用'],
                    ['value' => 4, 'text' => '继承表模式', 'tips' => '，使用继承的表'],
                    ['value' => 2, 'text' => '分类层级', 'tips' => '，比如分类结构'],
                    //   ['value' => 3, 'text' => '常用字段', 'tips' => '，一些常用的字段'],
                ],
                'default' => 0,
                'dynamic' => [
                    [
                        'eq' => 0,
                        'show' => 'tbName,tbCreate,tbEngine,useAjax,viewNotBack,validateMode',
                        'hide' => 'tbNameEx,extFields,notSingleWrap,notMultipleWrap,plugStyle',
                    ],
                    [
                        'eq' => 1,
                        'show' => 'notSingleWrap,notMultipleWrap,plugStyle',
                        'hide' => 'tbNameEx,extFields,tbName,tbCreate,tbEngine,useAjax,viewNotBack,validateMode',
                    ],
                    [
                        'eq' => 2,
                        'show' => 'tbName,tbCreate,tbEngine,useAjax,viewNotBack,validateMode',
                        'hide' => 'tbNameEx,extFields,notSingleWrap,notMultipleWrap,plugStyle',
                    ],
                    [
                        'eq' => 4,
                        'show' => 'tbNameEx,useAjax,viewNotBack,validateMode',
                        'hide' => 'tbName,extFields,notSingleWrap,notMultipleWrap,plugStyle,tbCreate,tbEngine',
                    ],

                ],
                'tab-index' => 'base',
            ],

            'notSingleWrap' => [
                'label' => '单行模式下不使用行外层', //标题
                'type' => 'check', // 这里是一个 checkbox
                //  'view-merge' => -1, //合并到上一行
                'after-text' => '勾选后控件占据整行', //在输入框尾部添加一个提示内容
                'tab-index' => 'base',
            ],
            'notMultipleWrap' => [
                'label' => '多行模式下不使用行外层', //标题
                'type' => 'check', // 这里是一个 checkbox
                //  'view-merge' => -1, //合并到上一行
                'after-text' => '勾选后控件占据整行', //在输入框尾部添加一个提示内容
                'default' => false,
                'tab-index' => 'base',
            ],
            'plugStyle' => [
                'label' => '多行模式的样式', //标题
                'type' => 'select', //下拉框
                'options' => [
                    ['value' => 0, 'text' => '默认'],
                    ['value' => 1, 'text' => '单行'],
                    ['value' => 2, 'text' => '紧凑'],
                ], // 下拉框的两个选项
                'default' => 0,
                'tab-index' => 'base',
            ],

            'tbName' => [
                'label' => '数据库表名称', // 字段标题
                'type' => 'text', //输入框类型
                //验证数据
                'data-val-rule' => ['r' => true, 'regex' => '^[a-z][a-z0-9_]+$'], // 验证规则 r 是简写  是验证不能为空
                'data-val-message' => ['r' => '没有填数据库名称！', 'regex' => '数据库名称为小写字母下划线数字组合。'], //如果错误了 提示的
                'off-edit' => function () {
                    if ($this->isEdit() && Request::isPost()) {
                        $id = Request::param('id:i');
                        $row = DB::getRow('select extMode from @pf_tool_form where id=?', $id);
                        if ($row && $row['extMode'] == 1) {
                            return true;
                        }
                    }
                    return false;
                }, //编辑状态下 这个不允许修改数据库
                'tips' => '自定义类型数据存放数据的表', //在输入框标题处 给个提示
                'tab-index' => 'base',
            ],

            'tbNameEx' => [
                'label' => '选择继承表', // 字段标题
                'type' => 'select', //输入框类型
                //验证数据
                'data-val-rule' => ['r' => true, 'regex' => '^[a-z][a-z0-9_]+$'], // 验证规则 r 是简写  是验证不能为空
                'data-val-message' => ['r' => '没有选择要继承的表名！', 'regex' => '数据库名称为小写字母下划线数字组合。'], //如果错误了 提示的
                'header' => '请选择要继承的数据表',
                'options' => function () {
                    return DB::getList('select tbName as value,concat(title,\' | \',tbName) as text from @pf_tool_form where extMode=0 or extMode=2');
                },
                'off-edit' => function () {
                    if ($this->isEdit() && Request::isPost()) {
                        $id = Request::param('id:i');
                        $row = DB::getRow('select extMode from @pf_tool_form where id=?', $id);
                        if ($row && $row['extMode'] == 1) {
                            return true;
                        }
                    }
                    return false;
                }, //编辑状态下 这个不允许修改数据库
                'tips' => '选择要继承的数据表', //在输入框标题处 给个提示
                'off-save' => true,
                'tab-index' => 'base',
            ],

            'tbCreate' => [
                'label' => '是否创建数据库', //标题
                'type' => 'check', // 这里是一个 checkbox
                'default' => 1, //默认 选中
                //  'view-merge' => -1, //合并到上一行
                'after-text' => '勾选后将会创建对应的数据库表', //在输入框尾部添加一个提示内容
                'off-edit' => true, //编辑状态下 这个不允许修改数据库
                'default' => true,
                'tab-index' => 'base',
            ],

            'tbEngine' => [
                'label' => '数据库存储引擎', //标题
                'type' => 'select', //下拉框
                'off-edit' => true,
                'options' => [
                    ['value' => 'InnoDB', 'text' => 'InnoDB'],
                    ['value' => 'MyISAM', 'text' => 'MyISAM'],
                ], // 下拉框的两个选项
                'default' => 'InnoDB',
                'tab-index' => 'base',
            ],

            'extFields' => [
                'label' => '常用字段',
                'off-edit' => true,
                'type' => 'check-group',
                'box-style' => 'width:230px; clear:both;',
                'options' => [
                    ['value' => 'name', 'text' => '名称[name]'],
                    ['value' => 'title', 'text' => '标题[title]'],
                    ['value' => 'allow', 'text' => '审核[allow]'],
                    ['value' => 'sort', 'text' => '排序[sort]',]
                ],
                'default' => ['name', 'allow', 'sort'],
                'tab-index' => 'base',
            ],
            'useAjax' => [
                'label' => '使用AJAX提交',
                'type' => 'check',
                'default' => true,
                'tab-index' => 'base',
                'after-text' => '勾选使用AJAX提交表单',
            ],
            'viewNotBack' => [
                'label' => '不返回上页',
                'type' => 'check',
                'after-text' => '勾选添加编辑等不返回上页',
                'tab-index' => 'base',
            ],
            'validateMode' => [
                'label' => '表单验证提示方式',
                'type' => 'radio-group',
                'default' => 0,
                'tab-index' => 'base',
                'options' => [
                    ['value' => 0, 'text' => '默认方式'],
                    ['value' => 2, 'text' => '对话框(单条)'],
                    ['value' => 1, 'text' => '对话框(多条)'],
                ],
            ],
            'template' => [
                'label' => '使用模板',
                'tips' => '如果不填写，则使用生成的模板',
                'tab-index' => 'base',
            ],
            'baseLayout' => [
                'label' => '皮肤文件Layout',
                'tab-index' => 'base',
                'default' => 'layout/layoutForm.tpl',
                'tips' => '父页面皮肤文件，如果为空 默认使用系统皮肤文件 layout/layoutForm.tpl',
            ],

            'withTpl' => [
                'label' => '生成模板',
                'type' => 'check',
                'after-text' => '勾选生成模板文件',
                'tab-index' => 'base',
                'default' => 1,
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'makeStatic',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'makeStatic',
                    ],
                ],
            ],
            'withForm' => [
                'label' => '生成表单',
                'type' => 'check',
                'after-text' => '勾选生成Form文件',
                'tab-index' => 'base',
                'default' => 1,
                'view-merge' => -1,
            ],
            'makeStatic' => [
                'label' => '静态生成',
                'type' => 'check',
                'after-text' => '尽可能的生成静态模板',
                'tab-index' => 'base',
                'default' => 1,
            ],
            'viewUseTab' => [
                'label' => '是否分栏', //标题
                'type' => 'check', // 这里是一个 checkbox
                'default' => 0, //默认 选中
                'after-text' => '勾选开启分栏', //在输入框尾部添加一个提示内容
                'tab-index' => 'extend',
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => 'viewTabs',
                    ],
                    [
                        'neq' => 1,
                        'hide' => 'viewTabs',
                    ],
                ],
            ],

            'viewTabs' => [
                'label' => '分栏栏目', //标题
                'type' => 'container',
                'plug-name' => FormTab::class,
                'mode' => 'multiple',
                'tab-index' => 'extend',
            ],

            //表单头

            'caption' => [
                'label' => '表单头',
                'type' => 'textarea',
                'tab-index' => 'extend',
                'tips' => '不填写默认使用标题，支持sdopx模板语法',
            ],

            'description' => [
                'label' => '头部说明(介绍)',
                'type' => 'textarea',
                'tab-index' => 'extend',
                'tips' => '在表单头部的说明文本，支持sdopx模板语法',
            ],
            'information' => [
                'label' => '提示信息(提示)',
                'type' => 'textarea',
                'tips' => '在底部的提示说明帮助，支持sdopx模板语法',
                'tab-index' => 'extend',
            ],
            'attention' => [
                'label' => '警告提示(警告)',
                'type' => 'textarea',
                'tips' => '在底部的警告提示说明帮助，支持sdopx模板语法',
                'tab-index' => 'extend',
            ],
            'head' => [
                'label' => 'head区',
                'type' => 'textarea',
                'tab-index' => 'extend',
                'tips' => 'html 头部区，可放入js css 等资源',
            ],
            'script' => [
                'label' => '脚本代码',
                'type' => 'textarea',
                'tips' => '需要在页面中执行的JS代码',
                'box-style' => 'width:600px; height:200px;',
                'tab-index' => 'extend',
            ],
        ];
    }

}