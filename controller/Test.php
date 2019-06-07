<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-6
 * Time: 上午8:12
 */

namespace tool\controller;


use beacon\Config;
use beacon\Controller;
use beacon\DB;
use beacon\Field;
use beacon\Logger;
use beacon\Utils;
use beacon\Form;

class Test extends BaseController
{
    private $formId = 0;


    private function loadFormId()
    {
        $this->formId = $this->get('formId:i', 0);
        if ($this->formId == 0) {
            $this->error('缺少参数', null, Route::url('~/form'));
        }
        $this->assign('formId', $this->formId);
    }

    function indexAction()
    {
        $type = 'add';
        $this->loadFormId();
        $formRow = DB::getRow('select * from @pf_tool_form where id=?', $this->formId);
        if (!$formRow) {
            $this->error('不存在的模型!');
        }
        if (empty($formRow['withTpl'])) {
            $this->error('需要生成模板才可查看测试效果!');
        }
        if (empty($formRow['withForm'])) {
            $this->error('需要生成表单才可查看测试效果!');
        }
        $namespace = trim($formRow['namespace'], '\\');
        $viewPath = [];
        $viewPath[] = Utils::path(ROOT_DIR, $namespace, 'view');
        $viewPath[] = Utils::path(ROOT_DIR, $namespace, 'zero/view');
        $conpath = Utils::path(ROOT_DIR, $namespace, 'config.php');
        if (file_exists($conpath)) {
            $appConfig = @require $conpath;
            if (is_array($appConfig) && isset($appConfig['sdopx.template_dir'])) {
                $viewPath = $appConfig['sdopx.template_dir'];
                foreach ($viewPath as &$item) {
                    $item = Utils::path(ROOT_DIR, $item);
                }
            }
        }
        $cfg = Config::get('sdopx.template_dir');
        foreach ($cfg as $item) {
            $viewPath[] = $item;
        }
        $viewPath[] = Utils::path(ROOT_DIR, '/view');
        $viewPath['common'] = Utils::path(ROOT_DIR, '/view/common');
        $viewPath = array_unique($viewPath);
        $this->view()->setTemplateDir($viewPath);
        if ($formRow['extMode'] == 1) {
            $namespace = $namespace . '\\zero\\plugin';
            $className = 'Zero' . $formRow['key'] . 'Plugin';
            $template = 'Zero' . $formRow['key'] . '.plugin.tpl';
        } else {
            $namespace = $namespace . '\\zero\\form';
            $className = 'Zero' . $formRow['key'] . 'Form';
            $template = 'Zero' . $formRow['key'] . '.form.tpl';
        }
        $className = $namespace . '\\' . $className;
        if ($formRow['extMode'] == 1) {
            $form = new Form($type);
            $form->title = '测试表单-' . $formRow['title'];
            $field1 = new Field($form);
            $field1->type = 'container';
            $field1->name = 'single';
            $field1->label = '单组模式：single';
            $field1->mode = 'single';
            $field1->plugName = $className;
            $form->addField('single', $field1);
            $field2 = new Field($form);
            $field2->type = 'container';
            $field2->name = 'multiple';
            $field2->label = '多行模式：multiple';
            $field2->mode = 'multiple';
            $field2->plugName = $className;
            $form->addField('multiple', $field2);
            $template = 'Test.form.tpl';
        } else {
            $form = Form::instance($className, $type);
        }
        if ($this->isGet()) {
            $this->assign('form', $form);
            if (empty($form->template)) {
                $this->display($template);
            } else {
                $this->display($form->template);
            }
            return;
        }
        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            echo '<pre>' . PHP_EOL;
            var_export($values);
            echo '</pre>' . PHP_EOL;
            exit;
        }
    }

}