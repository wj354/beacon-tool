<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-26
 * Time: 下午4:33
 */

namespace tool\controller;


use beacon\Controller;
use beacon\Form;
use tool\Tool;

abstract class BaseController extends Controller
{
    public function initialize()
    {
        if (!Tool::$isInstall) {
            $this->redirect('~/install');
        }
        if (!$this->getSession('adminId', 0)) {
            $this->redirect('^/admin');
        }
    }

    protected function displayForm(Form $form, string $template = '')
    {
        $this->assign('form', $form);
        if (empty($template)) {
            if (!empty($form->template)) {
                $template = $form->template;
            }
        }
        return parent::display($template);
    }
}