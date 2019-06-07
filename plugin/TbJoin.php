<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-29
 * Time: 上午2:31
 */

namespace tool\plugin;


use beacon\Form;

class TbJoin extends Form
{
    public $template = 'plugin/TbJoin.plugin.tpl';

    protected function load()
    {
        return [

            'tbName' => [
                'label' => '附加表',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '附加表不能为空'],
            ],
            'alias' => [
                'label' => '别名',
                'data-val-rule' => ['regex' => '^[A-Z]+$'],
                'data-val-message' => ['regex' => '表别名只能是大写字母'],
                'box-style' => 'width:100px;',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '别名不能为空'],
                'box-class' => 'form-inp mf',
            ],
            'join' => [
                'label' => 'JOIN',
                'type' => 'select',
                'options' => [['inner join', 'inner join | 交集'], ['left join', 'left join | 左集合'], ['right join', 'right join | 右集合']],
            ],
            'on' => [
                'label' => 'ON',
                'type' => 'textarea',
                'box-style' => 'width:200px; height:18px;vertical-align: middle;',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => 'JSON条件不能为空'],
                'box-class' => 'form-inp mf',
            ],

        ];
    }
}