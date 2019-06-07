<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-27
 * Time: 下午4:06
 */

namespace tool\form;


use beacon\DB;
use beacon\Form;
use beacon\Request;
use beacon\Route;

class AppFrom extends Form
{
    public $title = '项目管理';
    public $template = 'App.form.tpl';
    public $tbName = '@pf_tool_app';

    protected function load()
    {
        $load = [
            'name' => [
                'label' => '项目名称',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入项目名称'],
                'tips' => '请输入项目名称',
                'type' => 'remote',
                'data-url' => Route::url('~/Index/checkName'),
                'data-method' => 'post',
                'box-class' => 'form-inp text',
                'box-placeholder' => '如：后台管理',
                'remote-func' => function ($value) {
                    $id = Request::param('id:i', 0);
                    $row = DB::getRow('select id from @pf_tool_app where `name`=? and id<>?', [$value, $id]);
                    if ($row) {
                        return false;
                    }
                    return true;
                },
            ],
            'namespace' => [
                'label' => '命名空间',
                'data-val-rule' => ['r' => true, 'regex' => '^[a-z0-9]+(\\\\[a-z0-9]+)*$',],
                'data-val-message' => ['r' => '请输入命名空间', 'regex' => '命名空间格式不正确'],
                'box-placeholder' => '如：app\admin',
                'tips' => '请输入项目命名空间,用于自动加载和生成文件',
            ],
            'module' => [
                'label' => '路由模块名',
                'data-val-rule' => ['r' => true, 'regex' => '^[a-zA-Z][A-Za-z0-9_]*$'],
                'data-val-message' => ['r' => '没有填写路由模块名！', 'regex' => '路由模块名只能使用字母开头的数字及字母组合。'],
                'tips' => '如果模块名称不正确，则无法测试列表，将会查找对应的路由规则',
                'box-style' => 'width:120px;',
                'view-tab-index' => 'base',
                'box-placeholder' => '如：admin',
            ],
            'isDefault' => [
                'label' => '是否默认项目',
                'type' => 'check',
                'default' => false,
                'tips' => '勾选设置成为默认项目',
            ],
        ];
        if ($this->isEdit()) {
            $load['name']['data-bind'] = 'id';
            $this->addHideBox('id', Request::get('id:i', 0));
        }
        return $load;
    }
}