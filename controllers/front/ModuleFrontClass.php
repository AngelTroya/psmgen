<?php
/**
*  ModuleClassName For Help & Support http://www.selectomer.com
* 
*  @author    Angel María de Troya de la Vega TIC <info@selectomer.com>
*  @copyright 2014 Selectomer TIC
*  @license   CopyRight
*/

// selectomer[-->Front Controller Name<--]ModuleFrontController

class PSMGenModuleFrontClassModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $info = $this->postProcess();

        $arr = array();

        $this->context->smarty->assign('data', $arr);

//        Get links
        $controller_link = Context::getContext()->link->getAdminLink('AdminCloneAccessories');
        $this->context->smarty->assign('path', _MODULE_DIR_.$this->module->name);
        $this->context->smarty->assign('controller_link', $controller_link);
        $this->context->smarty->assign('info', $info);
        
//        $this->context->smarty->assign('HOOK_RIGHT_COLUMN', false);
//        $this->context->smarty->assign('HOOK_LEFT_COLUMN', false);
//        $this->context->smarty->assign('HOOK_FOOTER', false);
//        $this->context->smarty->assign('content_only' => '1');
        
        $this->setTemplate('fronttemplate.tpl');

        parent::initContent();
    }
    
    public function setMedia()
    {
        if (method_exists($this->context->controller, 'addJquery'))
        {
            $this->context->controller->addJquery();
//            $this->context->controller->addJqueryPlugin('fancybox');
//            $this->context->controller->addJqueryUI('ui.sortable');
        }
        $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/css/moduleclassname.css');
        $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/js/moduleclassname.js');

        return parent::setMedia();
    }

    public function postProcess()
    {
        $info = '';
        $res = false;

        if (Tools::isSubmit('btnSubmit'))
        {
            $res = true;
            if ($res)
                $info = $this->module->displayConfirmation($this->l('Configuration updated'));
            else
                $info= $this->module->displayError($this->l('Custom value error.'));
        }elseif (Tools::getIsset('getOrPostData'))
        {
            $res = true;
            if ($res)
                $info = $this->module->displayConfirmation($this->l('Configuration updated'));
            else
                $info= $this->module->displayError($this->l('Custom value error.'));
        }
        return $info;
    } 

    public function ajaxProcessAdminModule() {
        $this->context->smarty->assign(array('content_only' => '1'));

        die('data');
    }
}
