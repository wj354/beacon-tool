<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-30
 * Time: 上午9:12
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
use tool\plugin\Dynamic;

class FieldForm extends Form
{
    public $title = '字段管理';
    public $template = 'Field.form.tpl';
    public $tbName = '@pf_tool_field';

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
                'type' => 'remote',
                'data-url' => Route::url('~/Index/checkName'),
                'data-method' => 'post',
                'data-bind' => 'id,formId',
                'remote-func' => function ($value) {
                    $id = Request::param('id:i', 0);
                    $formId = Request::param('formId:i', 0);
                    $row = DB::getRow('select id from @pf_tool_field where `name`=? and id<>? and formId=?', [$value, $id, $formId]);
                    if ($row) {
                        return false;
                    }
                    return true;
                },
                'tips' => '字段名称将作为表字段名称',
                'box-style' => 'width:120px;',
                'tab-index' => 'base',
            ],

            'boxName' => [
                'label' => '输入框名称 [box-name]', // 字段标题
                'tips' => '输入框的 name 属性值,如果不填则与字段名称一致',
                'tab-index' => 'base',
                'box-style' => 'width:120px;',
            ],

            'type' => [
                'label' => '字段类型 [type]',
                'tab-index' => 'base',
                'box-style' => 'min-width:170px;display:inline-block;',
                'type' => 'radio-group', // 单选组
                'options' => Config::get('tool.support_type'), // 单选组的选项值
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

            'dbfield' => [
                'label' => '是否数据库字段 [dbfield]',
                'type' => 'check',
                'default' => 1,
                'after-text' => '是否同步创建数据库字段',
                //同步动态
                'dynamic' => [
                    [
                        'eq' => 1,
                        'show' => ['dbtype', 'dbcomment', 'db_def1'],
                    ],
                    [
                        'neq' => 1,
                        'hide' => ['dbtype', 'dbcomment', 'db_def1'],
                    ],
                ],
                'tab-index' => 'base',
            ],
            'dbtype' => [
                'label' => '数据库字段类型 [dbtype]',
                'type' => 'delay-select',
                'header' => ['', '数据库字段类型'],
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请选择字段类型'],
                'tips' => '在数据库中的字段类型',
                'tab-index' => 'base',
            ],
            'dblen' => [
                'label' => '长度 [dblen]',
                'type' => 'integer',
                'view-merge' => -1,
                'box-style' => "width: 80px",
                'tab-index' => 'base',
            ],
            'dbpoint' => [
                'label' => '小数点 [dbpoint]',
                'type' => 'integer',
                'view-merge' => -1,
                'box-style' => "width: 80px",
                'tab-index' => 'base',
            ],
            'dbpoint' => [
                'label' => '小数点 [dbpoint]',
                'type' => 'integer',
                'view-merge' => -1,
                'box-style' => "width: 80px",
                'tab-index' => 'base',
            ],
            'dbcomment' => [
                'label' => '字段备注 [dbcomment]',
                'type' => 'text',
                'tips' => '如果为空，使用标题作为备注',
                'box-style' => "width: 380px",
                'tab-index' => 'base',
            ],

            'db_def1' => [
                'label' => '默认值 [default]',
                'type' => 'select',
                'tab-index' => 'base',
                'options' => [
                    ['value' => 'null', 'text' => 'NULL'],
                    ['value' => 'empty', 'text' => '空字符串'],
                    ['value' => 'value', 'text' => '值'],
                ],
                'dynamic' => [
                    [
                        'eq' => 'value',
                        'show' => ['db_def2'],
                    ],
                    [
                        'neq' => 'value',
                        'hide' => ['db_def2'],
                    ],
                ],
            ],

            'db_def2' => [
                'label' => '!值',
                'type' => 'text',
                'tab-index' => 'base',
                'view-merge' => -1,
            ],

            'beforeText' => [
                'label' => '前置文本(小标题) [before-text]', // 字段标题
                'tab-index' => 'base',
            ],

            'afterText' => [
                'label' => '尾随文本(单位等) [after-text]', // 字段标题
                'tab-index' => 'base',
            ],

            'sort' => array(
                'label' => '排序', // 字段标题
                'type' => 'integer',
                'tab-index' => 'base',
                'default' => function () {
                    $formId = Request::get('formId:i', 0);
                    return DB::getMax($this->tbName, 'sort', 'formId=?', $formId) + 10;
                },
            ),

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
                'tips' => '被关闭的控件不会输出任何HTML代码，也不会保存入库',
                'after-text' => '勾选关闭该控件',
                'default' => 0,
                'tab-index' => 'base',
            ],

            'viewClose' => [
                'label' => '关闭视图 [view-close]', // 字段标题
                'type' => 'check', // 单选组
                'tips' => '仅关闭视图，保存入库时使用默认值',
                'after-text' => '勾选关闭该控件视图',
                'default' => 0,
                'tab-index' => 'base',
            ],

            'viewHide' => [
                'label' => '隐藏视图 [view-hide]', // 字段标题
                'type' => 'check', // 单选组
                'tips' => '输出，设置样式隐藏',
                'after-text' => '勾选隐藏视图',
                'default' => 0,
                'tab-index' => 'base',
            ],

            'offEdit' => [
                'label' => '编辑状态只读 [off-edit]', // 字段标题
                'type' => 'check', // 单选组
                'tips' => '在Form为编辑状态时，不可编辑',
                'after-text' => '勾选编辑只读',
                'default' => 0,
                'tab-index' => 'base',
            ],
            'default' => [
                'label' => '默认值 [default]',
                'type' => 'container',
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
            'unique-line' => [
                'label' => '设置唯一验证',
                'type' => 'line',
                'tab-index' => 'extend',
            ],
            'unique' => [
                'label' => '是否唯一 [unique]', // 字段标题
                'type' => 'check', // 单选组
                'default' => 0,
                'after-text' => '勾选后数据唯一',
                'tab-index' => 'extend',
            ],
            'remoteUrl' => [
                'label' => '远程验证路径 [remoteUrl]', // 字段标题
                'type' => 'text', // 单选组
                'tab-index' => 'extend',
            ],
            'remoteError' => [
                'label' => '远程验证提示消息', // 字段标题
                'type' => 'text', // 单选组
                'tab-index' => 'extend',
            ],
            'value-line' => [
                'label' => '值处理函数',
                'type' => 'line',
                'tab-index' => 'extend',
            ],
            'valueFunc' => [
                'label' => '处理值的函数',
                'type' => 'text',
                'data-val-rule' => ['regex' => '^\w+(\\\\\w+)*::\w+$'],
                'data-val-message' => ['regex' => '格式不正确。'],
                'tab-index' => 'extend',
                'tips' => '如果值需要后期加工，可以设置加工的PHP函数，完整的静态函数名，如 libs\MyClass::myFunc',
            ],
            'dynamic-line' => [
                'label' => '动态呈现',
                'type' => 'line',
                'tab-index' => 'view',
                'tips' => '在数据库中的字段类型',
            ],
            'dynamic' => [
                'label' => '动态呈现控制 [dynamic]',
                'type' => 'container',
                'plug-name' => Dynamic::class,
                'mode' => 'multiple',
                'tab-index' => 'view',
            ],
            'box-line' => [
                'label' => 'Input 样式及属性',
                'type' => 'line',
                'tab-index' => 'view',
            ],
            'boxPlaceholder' => [
                'label' => '输入框内提示文本 [box-placeholder]',
                'type' => 'text',
                'tips' => '直接在输入框内的提示文本(placeholder)',
                'tab-index' => 'view',
            ],
            'boxClass' => [
                'label' => '控件CSS样式名称 [box-class]',
                'type' => 'text',
                'tips' => '默认系统会指定为 "form-inp 控件类型"',
                'tab-index' => 'view',
            ],
            'boxStyle' => [
                'label' => '内联style样式 [box-style]',
                'type' => 'textarea',
                'tips' => '控件的内联样式',
                'tab-index' => 'view',
            ],

            'boxAttrs' => [
                'label' => '其他属性 [box-*]',
                'plug-name' => BoxAttr::class,
                'type' => 'container',
                'mode' => 'multiple',
                'tab-index' => 'view',
            ],

            'tips' => [
                'label' => '提示信息 [tips]',
                'type' => 'textarea',
                'tab-index' => 'view',
            ],
            'tpl-line' => [
                'label' => '自定义模板',
                'type' => 'line',
                'tab-index' => 'view',
            ],
            'viewTemplate' => [
                'label' => '排版样式 [view-template]',
                'type' => 'select',
                'header' => '请选择排版样式皮肤',
                'tips' => '指定控件皮肤',
                'options' => [['default', '默认(横向排版)', 'default'], ['editor', '编辑器（纵向排版）', 'editor']],
                'tab-index' => 'view',
            ],
            'viewAsterisk' => [
                'label' => '标注星号(*) [view-asterisk]',
                'type' => 'check',
                'after-text' => '在标题后面打上一个红色星号',
                'tab-index' => 'valid',
            ],
            'dataValRule' => [
                'label' => '验证配置 [data-val]',
                'type' => 'textarea',
                'tips' => '验证规则配置',
                'tab-index' => 'valid',
                'box-yee-module' => 'valid-rule',
                'data-bind' => '#dataValMsg',
            ],
            'dataValMessage' => [
                'label' => '错误提示  [data-val-message]',
                'type' => 'textarea',
                'tips' => '错误提示信息',
                'tab-index' => 'valid',
                'box-yee-module' => 'valid-message',
            ],
            'dataValDefault' => [
                'label' => '默认提示内容',
                'type' => 'textarea',
                'tips' => '验证的默认提示内容',
                'tab-index' => 'valid',
            ],
            'dataValCorrect' => [
                'label' => '正确提示内容',
                'type' => 'textarea',
                'tips' => '验证的默认提示内容',
                'tab-index' => 'valid',
            ],
            'dataValOutput' => [
                'label' => '呈现内容的标签ID',
                'type' => 'text',
                'tips' => '用于呈现正确或者错误信息的HTML标签，如：#test-validation 或者 .test-validation',
                'tab-index' => 'valid',
            ],
            'dataValDisabled' => [
                'label' => '关闭验证',
                'type' => 'check',
                'after-text' => '关闭认证后，前后台不再验证数据，如需要开启可在JS和PHP控制器代码中取消',
                'tab-index' => 'valid',
            ],
            'validFunc' => [
                'label' => '验证数值的函数 ',
                'type' => 'text',
                'data-val-rule' => ['regex' => '^\w+(\\\\\w+)*::\w+$'],
                'data-val-message' => ['regex' => '格式不正确。'],
                'tab-index' => 'extend',
                'tips' => '如果值需要后期验证，可以设置验证的PHP函数，完整的静态函数名，如 libs\MyClass::myFunc',
            ],
            'formId' => [
                'label' => '表单ID',
                'type' => 'hidden',
                'hideBox' => true,
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '表单ID丢失'],
                'default' => Request::param('formId:i', 0),
                'tab-index' => 'base',
            ],
        ];
    }

}