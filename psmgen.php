<?php

/**
 *  Module PSMGen For Help & Support http://www.selectomer.com
 * 
 *  @author    Angel María de Troya de la Vega <info@selectomer.com>
 *  @copyright 2014 Selectomer TIC
 *  @license   CopyRight
 */
//SET WITH CASE SENSITIVE
class PSMGen extends Module {

    public function __construct() {
        //SET WITH CASE SENSITIVE
        $this->name = 'psmgen';
        //SET TAB
        $this->tab = 'administration';
        $this->version = '1.0';
        $this->author = 'Angel Maria de Troya de la Vega';
        $this->need_instance = 0;
        $this->module_key = 'module_key';

        parent::__construct();
        //SET
        $this->displayName = $this->l('PS Module Generator');
        //SET
        $this->description = $this->l('This Module helps us to create a new module with a simple form');

        $path = dirname(__FILE__);
        if (strpos(__FILE__, 'Module.php') !== false) {
            $path .= '/../modules/' . $this->name;
        }

        //SET OTHERS
        //Import classes
        include_once $path . '/ModelClass.php';
    }

    public function install() {
        //SET SQL TABLES
        //META URLs
//        $meta = new Meta();
//        $meta->page = 'module-selectomer';
//        $meta->title = $this->l('Selectomer');
//        $meta->url_rewrite = $this->l('selectomer');

        if (!parent::install() ||
//            !$meta->add() ||
                !$this->installTab() || !$this->installSubTab()
//            !$res ||
        ) {
            return false;
        }

        // Default Module Configuration
        Configuration::updateValue('CUSTOM_VALUE', 0);
        $this->context->nameModule = $this->name;

        return true;
    }

    public function installTab() {
        $tab = new Tab();
        $tab->active = 1;
        //class_name = controller name
        $tab->class_name = 'AdminPSMGen';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = PSMGen::l('PSMGen', (int) $lang['id_lang']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminModuleMenu');
        $tab->module = $this->name;
        return $tab->add();
    }

    public function installSubTab() {
        $tab = new Tab();
        $tab1 = new Tab();
        $tab->active = 1;
        $tab1->active = 1;
        //nombre del controlador para el validador
        $tab->class_name = 'ValidatorPSMGen';
        $tab1->class_name = 'AdminPSMGen';
        $tab->name = array();
        $tab1->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = PSMGen::l('ValidatorPSMGen', (int) $lang['id_lang']);
            $tab1->name[$lang['id_lang']] = PSMGen::l('PSMGen', (int) $lang['id_lang']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminPSMGen');
        $tab1->id_parent = (int) Tab::getIdFromClassName('AdminPSMGen');
        $tab->module = $this->name;
        $tab1->module = $this->name;
        
        return $tab1->add() && $tab->add();
    }

    public function uninstall() {
        //SET SQL TABLES
        //SQL EXAMPLES


        $id_lang = $this->context->language->id;
//        $meta_id = Meta::getMetaByPage('selectomer', $id_lang);
//        $meta = new Meta($meta_id['id_meta']);

        if (!parent::uninstall() ||
//            !$res || 
                !$this->uninstallSubTab() || !$this->uninstallTab()
        ) {
            return false;
        }

        //Default Module Configuration
        Configuration::deleteByName('CUSTOM_VALUE');

        return true;
    }

    public function uninstallTab() {
        $id_tab = (int) Tab::getIdFromClassName('AdminPSMGen');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $deleted = $tab->delete();
        }
        return $deleted;
    }

    public function uninstallSubTab() {
        $id_tab = (int) Tab::getIdFromClassName('ValidatorPSMGen');
        $id_tab1 = (int) Tab::getIdFromClassName('AdminPSMGen');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $deleted = $tab->delete();
        }
        if ($id_tab1) {
            $tab1 = new Tab($id_tab1);
            $deleted1 = $tab1->delete();
        }
        return $deleted1 && $deleted;
    }

    public function getContent() {
        $info = '';
        //Check token
        if (Tools::getAdminTokenLite('AdminModules') == Tools::getValue('token')) {
            $info = $this->postProcess();
        }

        $id_lang = $this->context->language->id;

        $this->smarty->assign('id_lang', $id_lang);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('badges', Configuration::get('CUSTOM_VALUE'));

        return $this->display(__FILE__, 'views/templates/admin/config.tpl');
    }

    private function postProcess() {
        $info = '';
        $res = true;
        if (Tools::isSubmit('btnSubmit_config')) {
            $res = Configuration::updateValue('CUSTOM_VALUE', (int) Tools::getValue('custom_value'));
            if ($res) {
                $info = $this->displayConfirmation($this->l('Configuration updated'));
            } else {
                $info = $this->displayError($this->l('Custom value error.'));
            }
        }
        return $info;
    }

    public function hookDisplayBackOfficeHeader() {
        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
            //$this->context->controller->addJqueryPlugin('fancybox');
            //$this->context->controller->addJqueryUI('ui.sortable');
        }
        $this->context->controller->addCSS($this->_path . 'css/selectomer.css');
        $this->context->controller->addJS($this->_path . 'js/selectomer.js');
    }

    public function hookDisplayHeader() {
        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
            //$this->context->controller->addJqueryPlugin('fancybox');
            //$this->context->controller->addJqueryUI('ui.sortable');
        }
        $this->context->controller->addCSS($this->_path . 'css/selectomer.css', 'all');
        $this->context->controller->addJS($this->_path . 'js/selectomer.js', 'all');
    }

}
