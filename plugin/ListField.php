<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-29
 * Time: 上午12:19
 */

namespace tool\plugin;


use beacon\Form;

class ListField extends Form
{
    public $template = 'plugin/ListField.plugin.tpl';

    protected function load()
    {
        return [
            'title' => [
                'label' => '标题',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '标识不能为空'],
                'box-class' => 'form-inp title',
                'box-style' => 'width:145px;',
                'box-placeholder' => '字段标题',
                'box-class' => 'form-inp mf',
            ],
            'orderName' => [
                'label' => '排序字段',
                'type' => 'text',
                'box-style' => 'max-width:100px;',
                'box-placeholder' => '排序字段名',
                'box-class' => 'form-inp mf',
            ],
            'thAlign' => [
                'label' => 'Th属性',
                'type' => 'select',
                'options' => [['', '默认对齐'], ['left', 'left'], ['center', 'center'], ['right', 'right']],
                'default' => 'center',
            ],
            'thWidth' => [
                'label' => '宽',
                'box-style' => 'width:50px;',
                'box-placeholder' => '宽',
                'default' => 80,
                'box-class' => 'form-inp mf',
            ],
            'thAttrs' => [
                'label' => '其他属性',
                'box-placeholder' => '其他TH属性',
                'box-class' => 'form-inp mf',
            ],
            'tdAlign' => [
                'label' => 'TD对齐',
                'type' => 'select',
                'options' => [['', '默认对齐'], ['left', 'left'], ['center', 'center'], ['right', 'right']],
                'default' => 'center',
                'box-class' => 'form-inp mf',
            ],
            'tdAttrs' => [
                'label' => '其他属性',
                'box-placeholder' => '其他TD属性',
                'box-class' => 'form-inp mf',
            ],
            'keyName' => [
                'label' => '指定键名',
                'box-style' => 'width:150px;',
                'box-placeholder' => '不指定系统自动分配',
                'box-class' => 'form-inp mf',
            ],
            'field' => [
                'label' => '数据库字段',
                'type' => 'delay-select',
                'header' => ['', '选择字段'],
                'box-class' => 'form-inp mf',
            ],
            'code' => [
                'label' => '值',
                'type' => 'textarea',
                'box-style' => "width: 450px; height:60px;",
                'box-class' => 'form-inp textarea code',
                'box-placeholder' => '值变量修饰',
                'box-class' => 'form-inp mf navy',
            ],

        ];
    }
}