<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-2
 * Time: 下午5:54
 */

namespace tool\form;


use beacon\Config;
use beacon\DB;
use beacon\Form;
use beacon\Request;
use beacon\Route;
use tool\plugin\BoxAttr;
use tool\plugin\CustomAttr;
use tool\plugin\DefaultSet;

class SearchForm extends Form
{
    public $title = '搜索字段管理';
    public $template = 'Search.form.tpl';
    public $tbName = '@pf_tool_search';

    protected function load()
    {
        return [

            'label' => [
                'label' => '字段标题 [label]',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入表单名称'],
                'tab-index' => 'base',
                'tips' => '提示：如果标题需要隐藏 可在标题前加 ! 号 ',
            ],
            'btn1' => [
                'label' => '翻译',
                'type' => 'button',
                'box-href' => Route::url('~/S/translate'),
                'box-yee-module' => 'ajax',
                'box-on-before' => 'option.param[\'text\']=$(\'#label\').val()||\'\';',
                'box-on-success' => 'if(ret){$(\'#name\').val(ret.camel2);}',
                'data-carry' => '#label',
                'view-merge' => -1, //合并到上一行
                'tab-index' => 'base',
            ],
            'name' => [
                'label' => '字段名称 [name]',
                'data-val-rule' => ['r' => true, 'regex' => '^[a-z][A-Za-z0-9_]+$'],
                'data-val-message' => ['r' => '没有填写模型关键字！', 'regex' => '字段标识只能使用大写字母开头的数字及字母组合。'],
                'type' => 'text',
                'tips' => '字段名称将搜索字段名称',
                'box-style' => 'width:120px;',
                'tab-index' => 'base',
            ],

            'type' => [
                'label' => '字段类型 [type]',
                'tab-index' => 'base',
                'box-style' => 'min-width:170px;display:inline-block;',
                'type' => 'radio-group', // 单选组
                'options' => Config::get('tool.search_type'), // 单选组的选项值
                'default' => 'text',
                'dynamic' => [
                    [
                        'eq' => 'hidden',
                        'show' => 'hideBox',
                    ],
                    [
                        'neq' => 'hidden',
                        'hide' => 'hideBox',
                    ],
                ],
            ],

            'hideBox' => [
                'label' => '底部隐藏输入框 [hideBox]', // 字段标题
                'type' => 'check', // 单选组
                'default' => 0,
                'after-text' => '勾选后直接在底部添加隐藏输入框',
                'tab-index' => 'base',
            ],

            'varType' => [
                'label' => '值类型 [var-type]',
                'type' => 'select',
                'options' => [
                    ['value' => 'string', 'text' => 'string'],
                    ['value' => 'integer', 'text' => 'integer'],
                    ['value' => 'boolean', 'text' => 'boolean'],
                    ['value' => 'float', 'text' => 'float'],
                    ['value' => 'array', 'text' => 'array'],
                ],
                'tips' => '选择值的变量类型',
                'tab-index' => 'base'
            ],

            'beforeText' => [
                'label' => '前置文本(小标题) [before-text]', // 字段标题
                'tab-index' => 'base',
            ],

            'afterText' => [
                'label' => '尾随文本(单位等) [after-text]', // 字段标题
                'tab-index' => 'base',
            ],

            'tabIndex' => [
                'label' => '选择所属Tab [tab-index]',
                'type' => 'select',
                'options' => [
                    ['value' => 'base', 'text' => '基本栏目'],
                    ['value' => 'senior', 'text' => '高级搜索'],
                ],
                'tips' => '选择所属TAB标签',
                'tab-index' => 'base'
            ],

            'tbWhere' => [
                'label' => 'SQL查询代码',
                'type' => 'textarea',
                'tab-index' => 'base',
                'tips' => '如 (模糊查找) `name` like concat(\'%\',?,\'%\') 或者 type=? 或者 datetime > ?'
            ],

            'tbWhereType' => [
                'label' => '加入条件',
                'type' => 'select',
                'var-type' => 'integer',
                'options' => [
                    [0, '为[空,0,null]不加入'],
                    [1, '为[null]不加入'],
                    [2, '为[空,null]不加入'],
                    [3, '为[0,null]不加入'],
                    [-1, '直接加入'],
                ],
                'tab-index' => 'base'
            ],

            'sort' => [
                'label' => '排序', // 字段标题
                'type' => 'integer',
                'tab-index' => 'base',
                'default' => function () {
                    $listId = Request::get('listId:i', 0);
                    return DB::getMax($this->tbName, 'sort', 'listId=?', $listId) + 10;
                },
            ],

            'viewMerge' => [
                'label' => '向上合并 [view-merge]', // 字段标题
                'type' => 'radio-group', // 单选组
                'options' => [
                    ['value' => 0, 'text' => '不合并'],
                    ['value' => 1, 'text' => '向下合并'],
                    ['value' => -1, 'text' => '向上合并']
                ], // 单选组的选项值
                'default' => 0,
                'tab-index' => 'base',
            ],

            'close' => [
                'label' => '关闭控件 [close]', // 字段标题
                'type' => 'check', // 单选组
                'tips' => '被关闭的控件不会输出任何HTML代码',
                'after-text' => '勾选关闭该控件',
                'default' => 0,
                'tab-index' => 'base',
            ],

            'default' => [
                'label' => '默认值 [default]',
                'type' => 'container',
                'viewCustom' => true,
                'plug-name' => DefaultSet::class,
                'mode' => 'single',
                'tab-index' => 'base',
            ],
            'forceDefault' => [
                'label' => '强制默认值 [force-default]', // 字段标题
                'type' => 'check', // 单选组
                'tips' => '强制使用默认值，如果数据为空 或 0 则强制使用默认值',
                'after-text' => '勾选值为空时强制使用默认值',
                'default' => 0,
                'tab-index' => 'base',
            ],
            'extend' => [
                'label' => '高级设置',
                'type' => 'hidden',
                'viewCustom' => true,
                'type' => 'container',
                'plug-name' => null,
                'data-url' => Route::url('~/field/widget'),
                'mode' => 'single',
                'tab-index' => 'extend',
            ],
            'custom-line' => [
                'label' => '自定义扩展属性',
                'type' => 'line',
                'tab-index' => 'extend',
            ],
            'custom' => [
                'label' => '自定义属性 ',
                'type' => 'container',
                'plug-name' => CustomAttr::class,
                'mode' => 'multiple',
                'tab-index' => 'extend',
            ],

            'box-line' => [
                'label' => 'Input 样式及属性',
                'type' => 'line',
                'tab-index' => 'extend',
            ],

            'boxPlaceholder' => [
                'label' => '输入框内提示文本 [box-placeholder]',
                'type' => 'text',
                'tips' => '直接在输入框内的提示文本(placeholder)',
                'tab-index' => 'extend',
            ],
            'boxClass' => [
                'label' => '控件CSS样式名称 [box-class]',
                'type' => 'text',
                'tips' => '默认系统会指定为 "form-inp 控件类型"',
                'tab-index' => 'extend',
            ],
            'boxStyle' => [
                'label' => '内联style样式 [box-style]',
                'type' => 'textarea',
                'tips' => '控件的内联样式',
                'tab-index' => 'extend',
            ],

            'boxAttrs' => [
                'label' => '其他属性 [box-*]',
                'plug-name' => BoxAttr::class,
                'type' => 'container',
                'mode' => 'multiple',
                'tab-index' => 'extend',
            ],

            'listId' => [
                'label' => '表单ID',
                'type' => 'hidden',
                'hideBox' => true,
                'data-val' => ['r' => true],
                'data-val-msg' => ['r' => '列表ID丢失'],
                'default' => Request::param('listId:i', 0),
                'tab-index' => 'base',
            ],
        ];
    }
}