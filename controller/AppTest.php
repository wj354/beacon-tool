<?php


namespace tool\controller;


use beacon\core\App;
use beacon\core\DB;
use beacon\core\Form;
use beacon\core\Logger;
use beacon\core\Method;
use beacon\widget\Container;
use beacon\widget\Single;
use tool\libs\Helper;
use tool\libs\MakeFormTemplate;
use tool\model\TestContainer;
use tool\model\TestSingle;

/**
 * Class AppField
 * @property array $form
 * @property int $formId
 * @package tool\controller
 */
class AppTest extends AppBase
{
    private int $_formId = 0;
    private ?array $_form = null;

    public function __get(string $name)
    {
        if ($name == 'formId') {
            if ($this->_formId == 0) {
                $this->_formId = $this->param('formId:i', 0);
                if ($this->_formId == 0) {
                    $this->error('缺少参数', ['back' => App::url('~/AppForm')]);
                }
            }
            return $this->_formId;
        }
        if ($name == 'form') {
            if ($this->_form === null) {
                $this->_form = DB::getRow('select * from @pf_tool_form where id=?', $this->formId);
                if ($this->_form == null) {
                    $this->error('添加失败,表单不存在');
                }
            }
            return $this->_form;
        }
    }

    #[Method(act: 'form', method: Method::GET | Method::POST)]
    public function testForm()
    {
        $formRow = $this->getFormRow();
        $form = $this->getForm($formRow);
        if ($this->isGet()) {
            $this->assign('formRow', $formRow);
            $this->assign('form', $form);
            if ($formRow['extMode'] != 1) {
                $formId = intval($formRow['id']);
                $maker = new MakeFormTemplate($formId, true);
                $code = $maker->getCode();
                $this->view()->disposable=true;
                $this->display('string:' . $code);
            } else {
                $this->display('test/test.form.tpl');
            }
        }
    }

    private function getFormRow(): array
    {
        $formRow = $this->form;
        $viewBtns = Helper::convertArray($formRow['viewBtns']);
        $temp = [];
        foreach ($viewBtns as $btn) {
            $temp[] = intval($btn);
        }
        $formRow['viewBtns'] = $temp;
        if ($formRow['viewUseTab']) {
            $formRow['viewTabs'] = Helper::convertArray($this->form['viewTabs'], []);
            $first = true;
            foreach ($formRow['viewTabs'] as &$tab) {
                $tab['first'] = $first;
                $first = false;
            }
        }
        return $formRow;
    }

    private function getForm(array $formRow): Form
    {
        $namespace = trim($formRow['namespace'], '\\');
        if ($formRow['extMode'] == 1) {
            $namespace = $namespace . '\\zero\\plugin';
            $className = $formRow['key'] . 'Plugin';
        } else {
            $namespace = $namespace . '\\zero\\model';
            $className = $formRow['key'] . 'Model';
        }
        $className = $namespace . '\\' . $className;
        if (!class_exists($className)) {
            $this->error('类：' . $className . '不存在!');
        }
        /**
         * @var Form $form ;
         */
        $plugStyle = 1;
        if ($formRow['extMode'] == 1) {
            if ($formRow['plugMode'] == 'container') {
                $form = Form::create(TestContainer::class, 'add');
                $form->getField('container')->itemClass = $className;
                $plugStyle = intval($formRow['plugStyle']);
            } else {
                $form = Form::create(TestSingle::class, 'add');
                $form->getField('single')->itemClass = $className;
            }
        } else {
            $form = Form::create($className, 'add');
        }
        foreach ($form->getViewFields() as $field) {
            if ($field instanceof Container||$field instanceof Single) {
                $field->template = $this->getPluginTemplate($field,$plugStyle);
            }
        }
        return $form;
    }

    private function getPluginTemplate(Container|Single $field, int $plugStyle)
    {
        if (preg_match('@^(.*)\\\zero\\\plugin\\\(.*)Plugin@', $field->itemClass, $temp)) {
            $frow = DB::getRow('select id from @pf_tool_form where extMode=1 and namespace=? and `key`=?', [$temp[1], $temp[2]]);
            if ($frow) {
                $maker = new MakeFormTemplate(intval($frow['id']), true);
                $code = $maker->getCode();
                Logger::log($code);
                return 'string:' . $code;
            }
        }
        Logger::log('xxxxxxxxxxxxxxxxx');
        if ($field instanceof Container) {
            return 'test/test_container' . $plugStyle . '.plugin.tpl';
        } else {
            return 'test/test_single.plugin.tpl';
        }
    }
}