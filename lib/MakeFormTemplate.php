<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-6
 * Time: 上午12:22
 */

namespace tool\lib;


use beacon\DB;
use beacon\Logger;
use beacon\Utils;

class MakeFormTemplate
{
    private $form = null;
    private $out = [];
    private $path = null;
    private $keyName = null;

    public function __construct(int $formId = 0)
    {
        $this->form = DB::getRow('select * from @pf_tool_form where id=?', $formId);
        if ($this->form == null) {
            throw new \Exception('生成错误');
        }
        $appId = intval($this->form['appId']);
        $rootDir = ROOT_DIR;
        $app = DB::getRow('select dirName from @pf_tool_app where id=?', $appId);
        if ($app && !empty($app['dirName'])) {
            if (is_dir($app['dirName'])) {
                $rootDir = $app['dirName'];
            }
        }
        $this->path = Utils::path($rootDir, $this->form['namespace'], 'zero/view');
        $this->keyName = $this->form['key'];
        if (isset($this->form['withTpl']) && $this->form['withTpl'] == 1) {
            $this->createTemplate();
        }
    }

    public function getViewFields($tabIndex = null)
    {
        if (!empty($tabIndex)) {
            $fields = DB::getList('select * from @pf_tool_field where formId=? and tabIndex=? order by sort asc', [$this->form['id'], $tabIndex]);
        } else {
            $fields = DB::getList('select * from @pf_tool_field where formId=? order by sort asc', $this->form['id']);
        }
        //修正显示
        $temp = [];
        foreach ($fields as $field) {
            //处理视图的开关默认值
            if ($field['viewClose']) {
                continue;
            }
            if ($field['close']) {
                continue;
            }
            //隐藏字段
            if ($field['hideBox']) {
                continue;
            }
            $name = $field['name'];
            $field['boxName'] = empty($field['boxName']) ? $field['name'] : $field['boxName'];
            $field['boxId'] = empty($field['boxId']) ? $field['boxName'] : $field['boxId'];
            $temp[$name] = $field;
        }
        $fields = $temp;
        $keys = array_keys($fields);
        $temp = [];
        for ($idx = 0, $len = count($keys); $idx < $len; $idx++) {
            $key = $keys[$idx];
            $field = &$fields[$key];
            if ($idx == 0) {
                $field['viewMerge'] = 0;
            }
            //如果这一行合并到上一行
            if ($field['viewMerge'] == -1) {
                if ($idx - 1 >= 0) {
                    $prevField = &$fields[$keys[$idx - 1]];
                    $prevField['next'] = &$field;
                } else {
                    $field['viewMerge'] = 0;
                }
            }
            //合并到下一行
            if ($field['viewMerge'] == 1) {
                if ($idx + 1 < $len) {
                    $nextField = &$fields[$keys[$idx + 1]];
                    $nextField['prev'] = &$field;
                } else {
                    $field['viewMerge'] = 0;
                }
            }
            //不合并
            if ($field['viewMerge'] == 0) {
                $temp[$key] = &$field;
            }
        }
        return $temp;
    }

    private function createTemplate()
    {
        if ($this->form['extMode'] == 1) {
            $this->createPluginSingle();
            $this->createPluginMultiple();
        } else {
            if (empty($this->form['baseLayout'])) {
                $this->out[] = '{extends file=\'layout/layoutForm.tpl\'}';
            } else {
                $this->out[] = '{extends file=\'' . $this->form['baseLayout'] . '\'}';
            }
            if ($this->form['title']) {
                $this->out[] = "{block name='title'}{\$form->title}{/block}";
            }
            if (!empty($this->form['information'])) {
                $this->out[] = "{block name='form-information'}<div class='yee-information'>{$this->form['information']}</div>{/block}";
            }
            if (!empty($this->form['attention'])) {
                $this->out[] = "{block name='form-attention'}<div class='yee-attention'>{$this->form['attention']}</div>{/block}";
            }
            $this->createFormHeader();
            $this->createFormContent();
            $this->createHead();
            $this->createFoot();
        }
    }

    private function createFormHeader()
    {
        $this->out[] = '';

        $this->out[] = "{block name='form-header'}";
        if ($this->form['viewNotBack']) {
            $this->out[] = '<a class="yee-setting" href="javascript:;"><i class="icofont-ruler-pencil"></i></a>';
        } else {
            $this->out[] = '<a class="yee-back" href="javascript:history.back();"><i class="icofont-reply"></i></a>';
        }
        $this->out[] = '<div class="yee-title">{$form->title}</div>';
        $this->out[] = "{/block}";
    }

    private function createFormTab()
    {
        /**
         * <div class="yee-tab">
         * <ul yee-module="form-tab">
         * <li data-bind-name="base" class="curr"><a href="javascript:void(0);">基本配置</a></li>
         * <li data-bind-name="extend"><a href="javascript:void(0);">扩展配置</a></li>
         * </ul>
         * </div>
         */
        if (!$this->form['viewUseTab']) {
            return;
        }
        $this->out[] = '';
        $this->out[] = '<div class="yee-tab">';
        $this->out[] = '<ul yee-module="form-tab">';
        $tabs = Helper::convertArray($this->form['viewTabs'], []);
        $first = true;
        foreach ($tabs as $tab) {
            $this->out[] = '<li data-bind-name="' . $tab['key'] . '"' . ($first ? ' class="curr"' : '') . '><a href="javascript:void(0);">' . $tab['value'] . '</a></li>';
            $first = false;
        }
        $this->out[] = '</ul>';
        $this->out[] = '</div>';
    }

    private function createFormCaption()
    {
        if ($this->form['viewUseTab']) {
            return;
        }
        /**
         *  <div class="panel-caption">
         * <i class="icofont-pencil-alt-3"></i>
         * <h3>{if $form->isAdd()}新增网站应用{else}编辑网站应用{/if}</h3>
         * </div>
         */
        $this->form['caption'] = empty($this->form['caption']) ? '{if $form->isAdd()}新增{else}编辑{/if}' . $this->form['title'] : $this->form['caption'];
        $this->out[] = '';
        $this->out[] = '<div class="panel-caption">';
        $this->out[] = '<i class="icofont-pencil-alt-3"></i>';
        $this->out[] = '<h3>' . $this->form['caption'] . '</h3>';
        $this->out[] = '</div>';
    }

    private function createFieldInline($field)
    {
        if (!empty($field['prev'])) {
            $this->createFieldInline($field['prev']);
        }
        if ($this->form['makeStatic'] == 2) {
            $this->out[] = '{if !$form->getField(' . var_export($field['name'], true) . ')->close}';
        }
        if ($field['type'] == 'button') {
            $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
            if (!empty($field['tips'])) {
                $this->out[] = '<span class="yee-field-tips ' . $field['type'] . '">' . $field['tips'] . '</p>';
            }
        } else {
            if ($this->form['extMode'] == 1) {
                $this->out[] = '<div class="yee-row-inline' . ($field['viewHide'] ? ' none' : '') . '" id="row_{$form->getField(' . var_export($field['name'], true) . ')->boxId}">';
            } else {
                $this->out[] = '<div class="yee-row-inline' . ($field['viewHide'] ? ' none' : '') . '" id="row_' . $field['boxId'] . '">';
            }
            if (isset($field['label'][0]) && $field['label'][0] != '!') {
                if ($field['viewAsterisk'] == 1) {
                    $this->out[] = '<label class="inline-label"><em></em>' . htmlspecialchars($field['label']) . '：</label>';
                } else {
                    $this->out[] = '<label class="inline-label">' . htmlspecialchars($field['label']) . '：</label>';
                }
            }
            $this->out[] = '<span style="margin-right: 10px">';
            $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
            if (!empty($field['tips'])) {
                $this->out[] = '<span class="yee-field-tips ' . $field['type'] . '">' . $field['tips'] . '</span>';
            }
            $this->out[] = '</span>';
            $this->out[] = '</div>';
        }
        if ($this->form['makeStatic'] == 2) {
            $this->out[] = '{/if}';
        }
        if (!empty($field['next'])) {
            $this->createFieldInline($field['next']);
        }
    }

    private function createFieldRow($field)
    {
        //容器
        if ($field['type'] == 'container') {
            $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
            return;
        }
        if ($field['type'] == 'line') {
            $this->out[] = '<div class="yee-line">';
            $this->out[] = '<label class="line-label">' . htmlspecialchars($field['label']) . '</label>';
            if (!empty($field['tips'])) {
                $this->out[] = '<span style="margin-left: 15px;" class="yee-field-tips ' . $field['type'] . '">' . $field['tips'] . '</span>';
            }
            $this->out[] = '</div>';
            return;
        }
        if ($this->form['makeStatic'] == 2) {
            $this->out[] = '{if !$form->getField(' . var_export($field['name'], true) . ')->close}';
        }
        if ($this->form['extMode'] == 1) {
            $this->out[] = '<div class="yee-row' . ($field['viewHide'] ? ' none' : '') . '" id="row_{$form->getField(' . var_export($field['name'], true) . ')->boxId}">';
        } else {
            $this->out[] = '<div class="yee-row' . ($field['viewHide'] ? ' none' : '') . '" id="row_' . $field['boxId'] . '">';
        }
        if ($field['viewAsterisk'] == 1) {
            $this->out[] = '<label class="row-label"><em></em>' . htmlspecialchars($field['label']) . '：</label>';
        } else {
            $this->out[] = '<label class="row-label">' . htmlspecialchars($field['label']) . '：</label>';
        }
        $this->out[] = '<div class="row-cell">';
        if (!empty($field['prev'])) {
            $this->createFieldInline($field['prev']);
        }
        $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
        if (!empty($field['next'])) {
            $this->createFieldInline($field['next']);
        }
        $out = [];
        if (!empty($field['dataValRule']) || !empty($field['dataValGroup'])) {
            $out[] = '<span id="';
            if ($this->form['extMode'] == 1) {
                $out[] = '{$form->getField(' . var_export($field['name'], true) . ')->boxId}';
            } else {
                $out[] = htmlspecialchars($field['boxId']);
            }
            $out[] = '-validation"></span>';
            $this->out[] = join('', $out);
        }
        if (!empty($field['tips'])) {
            $this->out[] = '<p class="yee-field-tips ' . $field['type'] . '">' . $field['tips'] . '</p>';
        }
        $this->out[] = '</div>';
        $this->out[] = '</div>';
        if ($this->form['makeStatic'] == 2) {
            $this->out[] = '{/if}';
        }
    }

    private function createPanelField($tab = null)
    {
        if ($tab) {
            foreach ($this->getViewFields($tab['key']) as $field) {
                $this->out[] = '<!-- ' . $field['label'] . ' -->';
                $this->createFieldRow($field);
            }
        } else {
            foreach ($this->getViewFields() as $field) {
                $this->out[] = '<!-- ' . $field['label'] . ' -->';
                $this->createFieldRow($field);
            }
        }
    }

    private function createPanelContent($tab = null)
    {
        //如果生成静态
        if ($tab) {
            if ($tab['first']) {
                $this->out[] = '<div class="panel-content" name="' . $tab['key'] . '">';
            } else {
                $this->out[] = '<div class="panel-content none" name="' . $tab['key'] . '">';
            }
        } else {
            $this->out[] = '<div class="panel-content">';
        }
        if ($this->form['makeStatic']) {
            $this->createPanelField($tab);
            $this->out[] = '</div>';
            return;
        }
        if ($tab) {
            $this->out[] = "{foreach from=\$form->getViewFields(" . var_export($tab['key'], true) . ") item='field'}";
        } else {
            $this->out[] = "{foreach from=\$form->getViewFields() item='field'}";
        }
        $this->out[] = '{field_row field=$field}';
        $this->out[] = ' {/foreach}';
        $this->out[] = '</div>';
    }

    private function createSubmit()
    {
        $this->out[] = '<div class="yee-submit">';
        $this->out[] = '<label class="submit-label"></label>';
        $this->out[] = '<div class="submit-cell">';
        $this->out[] = '{$form->fetchHideBox()|raw}';
        $this->out[] = '<input type="submit" class="form-btn red" value="提交">';
        if (!$this->form['viewNotBack']) {
            $this->out[] = '<input type="hidden" name="__BACK__" value="{$this->getReferrer()}">';
            $this->out[] = '<a href="javascript:history.back();" class="form-btn back">返回</a>';
        } else {
            $this->out[] = '{literal}<a href="javascript:;" onclick="Yee.readyDialog(function(dlg){dlg.close()})" class="form-btn back">取消</a>{/literal}';
        }
        $this->out[] = '</div>';
        $this->out[] = '</div>';
    }

    private function createFormContent()
    {
        $this->out[] = '';
        $this->out[] = "{block name='form-content'}";
        $out = [];
        $out[] = '<form method="post" yee-module="validate';
        if ($this->form['useAjax']) {
            $out[] = ' ajax';
        }
        $out[] = '"';
        if ($this->form['validateMode']) {
            $out[] = ' data-validate@mode="' . $this->form['validateMode'] . '"';
        }
        $out[] = '>';
        $this->out[] = join('', $out);
        $this->createFormTab();
        $this->out[] = '<div class="yee-panel">';
        $this->createFormCaption();
        if ($this->form['viewUseTab']) {
            $tabs = Helper::convertArray($this->form['viewTabs'], []);
            $first = true;
            foreach ($tabs as $tab) {
                $tab['first'] = $first;
                $first = false;
                $this->createPanelContent($tab);
            }
        } else {
            $this->createPanelContent();
        }
        $this->createSubmit();
        $this->out[] = '</div>';
        $this->out[] = '</form>';
        $this->out[] = "{/block}";
    }

    private function createFoot()
    {
        if (!empty($this->form['script'])) {
            $this->out[] = '';
            $this->out[] = "{block name='footer'}";
            $this->out[] = "{literal left='<{' right='}>'}";
            $this->out[] = $this->form['script'];
            $this->out[] = "{/literal}";
            $this->out[] = "{/block}";
        }
    }

    private function createHead()
    {
        if (!empty($this->form['head'])) {
            $this->out[] = '';
            $this->out[] = "{block name='header'}";
            $this->out[] = "{literal left='<{' right='}>'}";
            $this->out[] = $this->form['head'];
            $this->out[] = "{/literal}";
            $this->out[] = "{/block}";
        }
    }

    //插件单个
    private function createPluginSingle()
    {
        $this->out[] = '{*用于创建单一插件容器集合的hook函数模板*}';
        $this->out[] = "{hook fn='single' field=null form=null}";
        if (!$this->form['notSingleWrap']) {
            $this->out[] = '<div class="yee-row" id="row_{$field->boxId}">';
            $this->out[] = '<label class="row-label">{$field->label}：</label>';
            $this->out[] = '<div class="row-cell">';
        }
        if ($this->form['makeStatic']) {
            $this->createPanelField();
        } else {
            $this->out[] = "{foreach from=\$form->getViewFields() item='child'}";
            $this->out[] = "{field_row field=\$child}";
            $this->out[] = "{/foreach}";
        }
        if (!$this->form['notSingleWrap']) {
            $this->out[] = "</div>";
            $this->out[] = "</div>";
        }
        $this->out[] = "{/hook}";
    }


    //批量插件
    private function createPluginMultiple()
    {
        $this->out[] = '{*用于创建多行插件容器集合的hook函数模板 lastIndex 最后行的索引，body 已有item的模板渲染数据，source 用于js动态创建的模板数据base64  *}';
        $this->out[] = "{hook fn='multiple-wrap' field=null form=null lastIndex=0 body=null source=null}";
        if (!$this->form['notMultipleWrap']) {
            $this->out[] = '<div class="yee-row" id="row_{$field->boxId}">';
            $this->out[] = '<label class="row-label">{$field->label}：</label>';
            $this->out[] = '<div class="row-cell">';
        }
        $this->out[] = '<div yee-module="container" id="{$field->boxId}" data-index="{$lastIndex}"{if $field->dataMinSize} data-min-size="{$field->dataMinSize}"{/if}{if $field->dataMaxSize} data-max-size="{$field->dataMaxSize}"{/if} data-source="{$source}">';
        $this->out[] = '<div class="container-wrap" style="display: block">';
        $this->out[] = '{$body|raw}';
        $this->out[] = "</div>";
        $this->out[] = '<div style="display: block;">';
        $this->out[] = '{if !$field->offEdit}<a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>{/if}';
        $this->out[] = '{if $field->tips}<span class="field-tips">{$field->tips}</span>{/if} <span id="{$field->boxId}-validation"></span>';
        $this->out[] = '</div>';
        $this->out[] = '</div>';
        if (!$this->form['notMultipleWrap']) {
            $this->out[] = "</div>";
            $this->out[] = "</div>";
        }
        $this->out[] = "{/hook}";
        $this->out[] = '{*用于创建多行插件容器中每行的数据hook函数模板 form 插件的表单 index 每项的索引*}';
        $this->out[] = "{hook fn='multiple-item' field=null form=null index=null}";
        $this->out[] = '<div class="container-item">';
        //默认模式
        if ($this->form['plugStyle'] == 0) {
            $this->out[] = '<div class="yee-container-title">';
            $this->out[] = '<label class="inline-label" style="text-align: left;">&nbsp;&nbsp;  第 <span name="index" class="red2" style="font-size: 18px;"></span>项&nbsp;&nbsp;&nbsp;</label>';
            $this->out[] = '{if !$field->offEdit}';
            $this->out[] = '{if $field->viewRemoveBtn}<a href="javascript:;" class="yee-btn" name="remove"><i class="icofont-minus-circle"></i>移除</a>{/if}';
            $this->out[] = '{if $field->viewInsertBtn}<a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>{/if}';
            $this->out[] = '{if $field->viewSortBtn}<a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a><a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>{/if}';
            $this->out[] = '{/if}';
            $this->out[] = '</div>';
            $this->out[] = '<div class="yee-container-body">';
            if ($this->form['makeStatic']) {
                $this->createPanelField();
            } else {
                $this->out[] = "{foreach from=\$form->getViewFields() item='child'}";
                $this->out[] = "{field_row field=\$child}";
                $this->out[] = "{/foreach}";
            }
            $this->out[] = '</div>';
        }

        //单行模式
        if ($this->form['plugStyle'] == 1) {
            if ($this->form['makeStatic']) {
                foreach ($this->getViewFields() as $field) {
                    $this->out[] = '<div class="yee-row-inline" id="row_{$form->getField(' . var_export($field['name'], true) . ')->boxId}">';
                    $this->out[] = '<label class="inline-label">' . htmlspecialchars($field['label']) . '：</label>';
                    $this->out[] = '<span style="margin-right: 10px">';
                    $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
                    if (!empty($field['tips'])) {
                        $this->out[] = '<span style="margin-left: 15px;" class="yee-field-tips ' . $field['type'] . '">' . $field['tips'] . '</span>';
                    }
                    $this->out[] = '</span>';
                    $this->out[] = '</div>';
                }
            } else {
                $this->out[] = '{foreach from=$form->getViewFields() item=\'child\'}';
                $this->out[] = '<div class="yee-row-inline" id="row_{$child->boxId}">';
                $this->out[] = '<label class="inline-label">{$child->label}：</label>';
                $this->out[] = '<span style="margin-right: 10px">';
                $this->out[] = '{input field=$child}{if $child->tips}<span class="field-tips">{$child->tips}</span>{/if}';
                $this->out[] = '</span>';
                $this->out[] = '</div>';
                $this->out[] = '{/foreach}';
            }
            $this->out[] = '{if !$field->offEdit}<div class="yee-row-inline" style="margin-right: 10px">';
            $this->out[] = '{if $field->viewRemoveBtn}<a href="javascript:;" class="yee-btn" name="remove"><i class="icofont-minus-circle"></i>移除</a>{/if}';
            $this->out[] = '{if $field->viewInsertBtn}<a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>{/if}';
            $this->out[] = '{if $field->viewSortBtn}<a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a><a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>{/if}';
            $this->out[] = '</div>{/if}';
        }
        //紧凑模式
        if ($this->form['plugStyle'] == 2) {
            if ($this->form['makeStatic']) {
                foreach ($this->getViewFields() as $field) {
                    $this->out[] = '<div class="yee-row-inline" id="row_{$form->getField(' . var_export($field['name'], true) . ')->boxId}">';
                    $this->out[] = '{input field=$form->getField(' . var_export($field['name'], true) . ')}';
                    $this->out[] = '</div>';
                }
            } else {
                $this->out[] = '{foreach from=$form->getViewFields() item=\'child\'}';
                $this->out[] = '<div class="yee-row-inline" id="row_{$child->boxId}">';
                $this->out[] = '{input field=$child placeholder=$child->label}';
                $this->out[] = '</div>';
                $this->out[] = '{/foreach}';
            }
            $this->out[] = '{if !$field->offEdit}<div class="yee-row-inline" style="margin-right: 10px">';
            $this->out[] = '{if $field->viewRemoveBtn}<a href="javascript:;" class="yee-btn" name="remove"><i class="icofont-minus-circle"></i>移除</a>{/if}';
            $this->out[] = '{if $field->viewInsertBtn}<a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>{/if}';
            $this->out[] = '{if $field->viewSortBtn}<a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a><a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>{/if}';
            $this->out[] = '</div>{/if}';
        }
        $this->out[] = '</div>';
        $this->out[] = "{/hook}";
    }

    public function getCode()
    {
        return join("\n", $this->out);
    }

    public function makeFile()
    {
        if (isset($this->form['withTpl']) && $this->form['withTpl'] == 1) {
            $code = '{*Created by Beacon AI Tool2.1. Date:' . date('Y-m-d H:i:s') . '*}' . $this->getCode();
            if ($this->form['extMode'] == 1) {
                $path = Utils::path($this->path, 'plugin');
                Utils::makeDir($path);
                file_put_contents(Utils::path($path, 'Zero' . $this->keyName . '.plugin.tpl'), $code);
            } else {
                $path = Utils::path($this->path, 'form');
                Utils::makeDir($path);
                file_put_contents(Utils::path($path, 'Zero' . $this->keyName . '.form.tpl'), $code);
            }
        }
    }

    public static function make(int $formId = 0)
    {
        $maker = new MakeFormTemplate($formId);
        $maker->makeFile();
    }


}