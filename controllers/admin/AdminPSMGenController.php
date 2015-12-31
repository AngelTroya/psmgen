<?php

/**
 *  Module PSMGen 
 * 
 *  @author    Angel Maria de Troya de la Vega <angelmaria87@gmail.com>
 *  @copyright 2014 
 *  @license   CopyRight
 */
if (!defined('_PS_VERSION_'))
    exit;

/**
 * @since 1.5.0
 */
class AdminPSMGenController extends ModuleAdminController {

    public function __construct() {
        $this->display = 'view';
        $this->meta_title = $this->l('AdminPSMGen');
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function setMedia() {
        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
//            $this->context->controller->addJqueryPlugin('fancybox');
//            $this->context->controller->addJqueryUI('ui.sortable');
        }
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/css/psmgen.css');
        $this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/js/psmgen.js');

        return parent::setMedia();
    }

    public function initToolBarTitle() {
        $this->toolbar_title = $this->l('PrestaShop Module Generator');
    }

    public function initToolBar() {
        return true;
    }

    public function initContent() {
        $info = '';

        $query = 'SELECT  `name` FROM  `ps_hook`';
        $sql = Db::getInstance()->executeS($query);

        if (Tools::getAdminTokenLite('AdminModules') == Tools::getValue('token')) {
            if (Tools::isSubmit('btnSubmit'))
                $info = $this->postProcess();
        }

//Get links
        $controller_link = Context::getContext()->link->getAdminLink('AdminPSMGen');

        $this->context->smarty->assign('path', _MODULE_DIR_ . $this->module->name . '/');
        $this->context->smarty->assign('controller_link', $controller_link);
        $this->context->smarty->assign('info', $info);

        $i = 0;
        $array_installhooks = array();
        //$array_check = array();
        for ($i = 0; $i < count($sql); $i++) {
            $hook = $sql[$i];
            $hook['id'] = 'hooks_' . $hook['name'];
            $hook['value'] = $hook['name'];
            $hook['label'] = $hook['name'];
            $sql[$i] = $hook;
            $array_installhooks[$i] = array(
                'name' => ('install_' . $hook['id']),
                'id' => ('install_' . $hook['id']),
                'value' => ('install_' . $hook['id']),
                'label' => ('install_' . $hook['id']),
            );
        }
        $this->context->smarty->assign('sql', $sql);
        $this->context->smarty->assign('array_installhooks', $array_installhooks);

//        $this->context->smarty->assign('content_only' => '1');
//        $form = $this->displayForm($sql);
//        $this->context->smarty->assign('form1', $form);

        $this->setTemplate('../../../../modules/' . $this->module->name . '/views/templates/admin/admintemplate.tpl');
//$smarty->assign('items', $items_list);

        parent::initContent();
    }

    public function postProcess() {
        $info = '';
        $metacode = '';
        if (Tools::isSubmit('btnSubmit')) {
            $res = true;
            if ($res) {
                $module_name = $this->sanear_string(Tools::getValue('module_project_name'));

                $name_project = $module_name;
                $author = $this->sanear_string(Tools::getValue('module_author'));
                $module_tab = Tools::getValue('module_tab');
                $version = Tools::getValue('module_version');
                $description = $this->sanear_string(Tools::getValue('module_description'));
                $this->errors = array();

//compruebo errores del formulario
                if (empty($name_project) || strlen($name_project) < 5) {
                    $this->errors[] = 'Project name field should have a minimum of five characters';
                }
                if (empty($author) || strlen($author) < 4) {
                    $this->errors[] = 'Author field should have a minimum of four characters';
                }
                if (empty($version)) {
                    $this->errors[] = 'Version is empty';
                }
                if (empty($description)) {
                    $this->errors[] = 'Description is empty';
                }

//continuo si no hay errores
                if (empty($this->errors)) {
//obtengo el nombre de la carpeta y del proyecto
                    $class_name = ucfirst($name_project);
                    $class_name = preg_replace('[\s+]', "", $name_project);
                    $name_project = strtolower($class_name);

//creamos el archivo principal del proyecto
                    $dstFilename = dirname(__FILE__) . "/" . $name_project;

//creo una carpeta con el proyecto y un archivo con el cÃ³digo del instalador                    
                    mkdir($dstFilename);
                    $archivo = $dstFilename . "/" . $name_project . '.php';
                    $fp = fopen($archivo, "w+");
                    if ($fp == false) {
                        die('No se ha podido crear el archivo.');
                    }
                    $metacode = $this->display_code($fp, $class_name, $name_project, $module_tab, $version, $author, $module_name, $description);

                    $fwrite = fwrite($fp, $metacode);
                    if ($fwrite === false) {
                        die('No se ha podido escribir el archivo.');
                    }

                    fclose($fp);
//                    $this->download_file($name_file);
//creo la estructura del proyecto
                    mkdir("$dstFilename/controllers");
                    mkdir("$dstFilename/controllers/admin");
                    mkdir("$dstFilename/controllers/front");
                    mkdir("$dstFilename/css");
                    mkdir("$dstFilename/img");
                    mkdir("$dstFilename/js");
                    mkdir("$dstFilename/override");
                    mkdir("$dstFilename/translations");
                    mkdir("$dstFilename/views");
                    mkdir("$dstFilename/views/templates");
                    mkdir("$dstFilename/views/templates/admin");
                    mkdir("$dstFilename/views/templates/front");
                    mkdir("$dstFilename/views/templates/hook");

//creamos el archivo index.php en controllers y lo incluimos en todos los directorios

                    $fpindex = fopen("$dstFilename/controllers/index.php", "w+");
                    if ($fpindex == false) {
                        die('No se ha podido crear el archivo index.php.');
                    }

                    $index = $this->genera_index_file($name_project, $author);

                    $fwrite = fwrite($fpindex, $index);
                    if ($fwrite === false) {
                        die('No se ha podido escribir el archivo index.php.');
                    }

                    fclose($fpindex);
                    if (!copy("$dstFilename/controllers/index.php", "$dstFilename/controllers/admin/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/controllers/front/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/css/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/img/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/js/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/override/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/translations/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/views/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/views/templates/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/views/templates/admin/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/views/templates/front/index.php") ||
                            !copy("$dstFilename/controllers/index.php", "$dstFilename/views/templates/hook/index.php")) {
                        d("Error al copiar index \n");
                    }
//creo los archivos de los hooks
//------------------------------------------------------------------  
                    //tengo que comprobar si existe el display y el action para los tpl y importarlos
                    ////(nombre de archivo ejemplo hookPaymentTemplate.tpl) no es lo que hago ni de lejos
                    foreach ($_POST as $key => $variable) {
                        if (preg_match("/^(hooks_)/", $key)) {
                            $hook = explode("hooks_", $key);
                            $hook_name = $hook[1];
                            $patron = '/action/';
                            if (preg_match($patron, $hook_name)) {
                                $hook = explode("action", $hook_name);
                                $hook_name = $hook[1];
                            }

                            $patron = '/display/';
                            if (preg_match($patron, $hook_name)) {
                                $hook = explode("display", $hook_name);
                                $hook_name = $hook[1];
                            }
                            $hook_name = ucfirst($hook_name);
                            $hook_dirname_src = dirname(dirname(dirname(__FILE__))) . "/view_templates/hook$hook_name" . "Template.tpl";
                            $hook_dirname_dst = "$dstFilename/views/templates/hook/hook$hook_name" . "Template.tpl";
                            //intento copiar archivos hook y display
                            if (!copy($hook_dirname_src, $hook_dirname_dst)) {
                                $name_tpl = "$dstFilename/views/templates/hook/$hook_name" . "Template.tpl";
                                $fp = fopen($name_tpl, "w+");
                                fclose($fp);
                            }
                        }
                    }
//------------------------------------------------------------------                    
//creo el zip a descargar
//                    p("numficheros: " . $zip->numFiles . "\n");
//                    p("estado:" . $zip->status . "\n");
//                    for ($i = 0; $i < $zip->numFiles; $i++) {
//                        p("index: $i\n");
//                        p($zip->statIndex($i));
//                    }
//// Zip archive will be created only after closing object
//                    if (!$zip->close()) {
//                        d('fallo al cerrar el zip');
//                    }

                    $rootPath = "../modules/psmgen/controllers/admin/$name_project";
                    $destination = "../modules/psmgen/controllers/admin/$name_project.zip";

                    $this->zip($rootPath, $destination, $name_project);

                    // Transmit zip file
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename='" . $name_project . ".zip'");
                    readfile($destination);
                    unlink($destination);
                }
                $info = $this->module->displayConfirmation($this->l('Configuration updated'));
            } else {
                $info = $this->module->displayError($this->l('Custom value error.'));
            }
        } elseif (Tools::getIsset('getOrPostData')) {
            $res = true;
            if ($res) {
                $info = $this->module->displayConfirmation($this->l('Configuration updated'));
            } else {
                $info = $this->module->displayError($this->l('Custom value error.'));
            }
        }
        return $info;
    }

    public function ajaxProcessAdminModule() {
        $this->context->smarty->assign(array('content_only' => '1'));

        die('data');
    }

    public function sanear_string($string) {

        $string = trim($string);

        $string = str_replace(
                array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string
        );

        $string = str_replace(
                array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string
        );

        $string = str_replace(
                array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string
        );

        $string = str_replace(
                array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string
        );

        $string = str_replace(
                array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string
        );

        $string = str_replace(
                array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string
        );

//Esta parte se encarga de eliminar cualquier caracter extraÃƒÂ±o
        $string = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":",
            ".", " "), '', $string
        );

        return $string;
    }

    private function display_code($fp, $class_name, $name_project, $module_tab, $version, $author, $module_name, $description) {

        $codigo = '';
        $clase = '';

        $constructor = $this->genera_constructor($name_project, $module_tab, $version, $author, $module_name, $description);
//        installTab
//        uninstall
//        uninstallTab
//genero las funciones de todos los hooks que hemos marcado y guardo los nombres de los hooks 
        $hooks = '';
        foreach ($_POST as $key => $variable) {
            if (preg_match("/^(hooks_)/", $key)) {
                $hook = explode("_", $key);
                $hook_name = $hook[1];
                $array_hooks[] = $hook[1];
//genero la funcion del hook y la aÃƒÂ±ado a la variable $hooks para aÃƒÂ±adirlo despuÃƒÂ©s en la clase
                $hooks_functions .= $this->genera_hook($hook_name, $hooks, $name_project);
            }
        }

        $install = $this->genera_install($array_hooks, $name_project);
        $uninstall = $this->genera_uninstall($array_hooks, $name_project);

//no se si este if puede borrarse. Comprobarlo        
        if (Tools::getValue('module_configpage')) {
            $config_page = $this->genera_getContent();
        }
        $clase .= "\t" . ' public function __construct()' . "\n " . "\t" . '{' . "\n " . $constructor . "\n " . "\t" . '}' . "\n " . "\n ";
        $clase .= "\t" . 'public function install()' . "\n " . "\t" . '{' . "\n " . $install . "\n " . "\t" . '}' . "\n " . "\n ";
//modificar para que los tab se muestren sÃƒÂ³lo si se instalan Tabs
        $clase .= "\t" . 'public function installTab()' . "\n " . "\t" . '{' . "\n " . $installTab . "\n " . "\t" . '}' . "\n " . "\n ";
        $clase .= "\t" . 'public function uninstall()' . "\n " . "\t" . '{' . "\n " . $uninstall . "\n " . "\t" . '}' . "\n " . "\n ";
        $clase .= "\t" . 'public function uninstallTab()' . "\n " . "\t" . '{' . "\n " . $uninstallTab . "\n " . "\t" . '}' . "\n " . "\n ";

        if (Tools::getValue('module_configpage')) {
//como el getContent invoca a postProcess generamos aquÃƒÂ­ tambiÃƒÂ©n dicha funciÃƒÂ³n si hace falta
            $config_page = $this->genera_getContent();
            $post_process = $this->genera_postProcess();
            $clase .= "\t" . 'public function getContent()' . "\n " . "\t" . '{' . "\n " . $config_page . "\n " . "\t" . '}' . "\n " . "\n ";
            $clase .= "\t" . 'public function postProcess()' . "\n " . "\t" . '{' . "\n " . $post_process . "\n " . "\t" . '}' . "\n " . "\n ";
        }

        $clase .= $hooks_functions;
        $codigo .= '<?php' . "\n " . 'class ' . $class_name . ' extends Module' . "\n ";
        $codigo .= '{' . "\n " . $clase . '}' . "\n ";

        return $codigo;
    }

    private function genera_constructor($name_project, $module_tab, $version, $author, $module_name, $description) {

        $constructor .= "\t" . "\t" . '$this->name = \'' . $name_project . '\';' . "\n ";
        $constructor .= "\t" . "\t" . '$this->tab = \'' . $module_tab . '\';' . "\n ";
        $constructor .= "\t" . "\t" . '$this->version = \'' . $version . '\';' . "\n ";
        $constructor .= "\t" . "\t" . '$this->author = \'' . $author . '\';' . "\n ";
        $constructor .= "\t" . "\t" . '$this->need_instance = ' . Tools::getValue('module_needInstance') . ';' . "\n ";
        $constructor .= "\t" . "\t" . '$this->module_key = ' . 0 . ';' . "\n ";
        $constructor .= "\t" . "\t" . 'parent::__construct();' . "\n ";
        $constructor .= "\t" . "\t" . '$this->displayName = $this->l( \'' . $module_name . '\');' . "\n ";
        $constructor .= "\t" . "\t" . '$this->displayName = $this->l( \'' . $description . '\');' . "\n ";
        $constructor .= "\t" . "\t" . '$path = dirname(__FILE__);' . "\n ";
        $constructor .= "\t" . "\t" . 'if (strpos(__FILE__, \'Module.php\') !== false)' . "\n ";
        $constructor .= "\t" . "\t" . "\t" . '$path .= ' . '/../modules/' . $name_project . ';' . "\n ";
        $constructor .= "\t" . "\t" . 'include_once $path.\'/ModelClass.php\'';

        return $constructor;
    }

    private function genera_getContent() {

        $config_page = '';
        $config_page .= "\t" . "\t" . '$info = \'\';' . "\n ";
        $config_page .= "\t" . "\t" . 'if (Tools::getAdminTokenLite(\'AdminModules\') == Tools::getValue(\'token\'))' . "\n ";
        $config_page .= "\t" . "\t" . "\t" . '$info = $this->postProcess()' . "\n ";
        $config_page .= "\t" . "\t" . '$id_lang = $this->context->language->id;' . "\n ";
        $config_page .= "\t" . "\t" . '$this->smarty->assign(\'id_lang\', $id_lang);' . "\n ";
        $config_page .= "\t" . "\t" . '$this->smarty->assign(\'info\', $info);' . "\n ";
        $config_page .= "\t" . "\t" . '$this->smarty->assign(\'badges\', Configuration::get(\'CUSTOM_VALUE\'));' . "\n ";
        $config_page .= "\t" . "\t" . 'return $this->display(__FILE__, \'views/templates/admin/config.tpl\');' . "\n ";

        return $config_page;
    }

    private function genera_postProcess() {

        $post_process = '';
        $post_process .= "\t" . "\t" . '$info = \'\';' . "\n ";
        $post_process .= "\t" . "\t" . '$res = true;' . "\n ";
        $content_if = '';
        $content_if .= "\t" . "\t" . "\t" . '$res = Configuration::updateValue(\'CUSTOM_VALUE\', (int)Tools::getValue(\'custom_value\'))' . "\n ";
        $content_if .= "\t" . "\t" . 'if ($res)' . "\n ";
        $content_if .= "\t" . "\t" . "\t" . '$info = $this->displayConfirmation($this->l(\'Configuration updated\'));' . "\n ";
        $content_if .= "\t" . "\t" . 'else' . "\n " . "\t" . "\t" . "\t" . '$info = $this->displayError($this->l(\'Custom value error.\'));' . "\n ";
        $post_process .= "\t" . "\t" . 'if (Tools::isSubmit(\'btnSubmit_config\'))' . "\n " . "\t" . "\t" . '{' . "\n " . $content_if . "\t" . "\t" . '}' . "\n ";
        $post_process .= "\t" . "\t" . 'return $info';

        return $post_process;
    }

    private function genera_hookDisplayBackOfficeHeader($name_project) {
        $dboh = '';
        $content_if = '';

        $content_if .= "\t" . "\t" . "\t" . '$this->context->controller->addJquery();' . "\n ";
        $content_if = "\t" . "\t" . "\t" . '//$this->context->controller->addJqueryPlugin(\'fancybox\');' . "\n ";
        $content_if = "\t" . "\t" . "\t" . '//$this->context->controller->addJqueryUI(\'ui.sortable\');' . "\n ";

        $dboh .= "\t" . "\t" . 'if (method_exists($this->context->controller, \'addJquery\'))' . "\n " . "\t" . "\t" . '{' . "\n " . $content_if . "\t" . "\t" . '}' . "\n ";
        $dboh .= "\t" . "\t" . '$this->context->controller->addCSS($this->_path.\'css/' . $name_project . '.css\');' . "\n ";
        $dboh .= "\t" . "\t" . '$this->context->controller->addJS($this->_path.\'js/' . $name_project . '.js\');' . "\n ";

        return $dboh;
    }

    private function genera_hookDisplayHeader($name_project) {
        $db = '';
        $content_if = '';

        $content_if .= "\t" . "\t" . "\t" . '$this->context->controller->addJquery();' . "\n ";
        $content_if = "\t" . "\t" . "\t" . '//$this->context->controller->addJqueryPlugin(\'fancybox\');' . "\n ";
        $content_if = "\t" . "\t" . "\t" . '//$this->context->controller->addJqueryUI(\'ui.sortable\');' . "\n ";

        $db .= "\t" . "\t" . 'if (method_exists($this->context->controller, \'addJquery\'))' . "\n " . "\t" . "\t" . '{' . "\n " . $content_if . "\t" . "\t" . '}' . "\n ";
        $db .= "\t" . "\t" . '$this->context->controller->addCSS($this->_path.\'css/' . $name_project . '.css\', \'all\');' . "\n ";
        $db .= "\t" . "\t" . '$this->context->controller->addJS($this->_path.\'js/' . $name_project . '.js\', \'all\');' . "\n ";

        return $db;
    }

    private function genera_hook($hook_name, $hooks, $name_project) {
        if ($hook_name == 'displayBackOfficeHeader') {
            $hooks .= "\t" . 'public function hook' . $hook_name . ' ()' . "\n " . "\t" . '{' . "\n ";
            $hooks .= $this->genera_hookDisplayBackOfficeHeader($name_project) . "\n " . "\t" . '}' . "\n " . "\n ";
        } elseif ($hook_name == 'displayHeader') {
            $hooks .= "\t" . 'public function hook' . $hook_name . ' ()' . "\n " . "\t" . '{' . "\n ";
            $hooks .= $this->genera_hookDisplayHeader($name_project) . "\n " . "\t" . '}' . "\n " . "\n ";
        } else {
            $hooks .= "\t" . 'public function hook' . $hook_name . ' ($params)' . "\n " . "\t" . '{' . "\n ";
            $hooks .= "\t" . "\t" . '//TO DO' . "\n " . "\t" . '}' . "\n " . "\n ";
        }
        return $hooks;
    }

//recibe por parÃƒÂ¡metro los nombres de todos los hooks que se han marcado en el formulario
    private function genera_install($array_hooks, $name_project) {
        $register_hooks = '';
        $content = '';
        $content .= "\t" . "\t" . '//SET SQL TABLES' . "\n ";
        $content .= "\t" . "\t" . '//$res = Db::getInstance()->execute(\'\');' . "\n ";
        $content .= "\t" . "\t" . '//SQL EXAMPLE' . "\n ";
        $content .= "\t" . "\t" . '//CREATE TABLE IF NOT EXISTS `\'._DB_PREFIX_.\'custom_table` (' . "\n ";
        $content .= "\t" . "\t" . '//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,' . "\n ";
        $content .= "\t" . "\t" . '//`count` int(10)  int(11) NOT NULL,' . "\n ";
        $content .= "\t" . "\t" . '//`name` varchar(256) NOT NULL,' . "\n ";
        $content .= "\t" . "\t" . '//PRIMARY KEY (`id`))' . "\n ";
        $content .= "\t" . "\t" . '//ENGINE=\'._MYSQL_ENGINE_' . "\n " . "\n ";
        $content .= "\t" . "\t" . '//META URLs' . "\n ";
        $content .= "\t" . "\t" . '//$meta = new Meta();' . "\n ";
        $content .= "\t" . "\t" . '//$meta->page = \'module-' . $name_project . '\';' . "\n ";
        $content .= "\t" . "\t" . '//$meta->title = $this->l(\'Selectomer\');' . "\n ";
        $content .= "\t" . "\t" . '//$meta->page = \'module-' . $name_project . '\';' . "\n ";
        $content .= "\t" . "\t" . '//$meta->url_rewrite = $this->l(\'' . $name_project . '\')' . "\n " . "\n ";
        $i = 0;
        $tam = count($array_hooks);
        for ($i = 0; $i < $tam; $i++) {
            $name = $array_hooks[$i];
            if ($i < $tam - 1)
                $register_hooks .= "\t" . "\t" . '|| !$this->registerHook(\'' . $name . '\') ||' . "\n ";
            else
                $register_hooks .= "\t" . "\t" . '|| !$this->registerHook(\'' . $name . '\')' . "\n ";
        }

        $content .= "\t" . "\t" . 'if(!parent::install() || ' . "\n " . "\t" . "\t" . '!$this->installTab() ' . "\n " . $register_hooks . "\t" . "\t" . ')' . "\n ";
        $content .= "\t" . "\t" . "\t" . 'return false;' . "\n " . "\n ";
        $content .= "\t" . "\t" . 'Configuration::updateValue(\'CUSTOM_VALUE\', 0);' . "\n ";
        $content .= "\t" . "\t" . 'return true;' . "\n ";

        return $content;
    }

    private function genera_uninstall($array_hooks, $name_project) {
        $register_hooks = '';
        $content = '';
        $content .= "\t" . "\t" . '//SET SQL TABLES' . "\n ";
        $content .= "\t" . "\t" . '//$res = Db::getInstance()->execute(\'\');' . "\n ";
        $content .= "\t" . "\t" . '//SQL EXAMPLE' . "\n ";
        $content .= "\t" . "\t" . '//DROP TABLE IF EXISTS `\'._DB_PREFIX_.\'custom_table`' . "\n ";
        $content .= "\t" . "\t" . '$id_lang = $this->context->language->id;';
        $content .= "\t" . "\t" . '//$meta_id = Meta::getMetaByPage(\'' . $name_project . '\', $id_lang);' . "\n ";
        $content .= "\t" . "\t" . '//$meta = new Meta($meta_id[\'id_meta\']);' . "\n " . "\n ";

        $i = 0;
        $tam = count($array_hooks);
        for ($i = 0; $i < $tam; $i++) {
            $name = $array_hooks[$i];
            if ($i < $tam - 1)
                $register_hooks .= "\t" . "\t" . '|| !$this->unregisterHook(\'' . $name . '\') ||' . "\n ";
            else
                $register_hooks .= "\t" . "\t" . '|| !$this->unregisterHook(\'' . $name . '\')' . "\n ";
        }

        $content .= "\t" . "\t" . 'if(!parent::uninstall() || ' . "\n " . "\t" . "\t" . '!$this->uninstallTab() ' . "\n " . $register_hooks . "\t" . "\t" . ')' . "\n ";
        $content .= "\t" . "\t" . "\t" . 'return false;' . "\n " . "\n ";
        $content .= "\t" . "\t" . 'Configuration::deleteByName(\'CUSTOM_VALUE\');' . "\n ";
        $content .= "\t" . "\t" . 'return true;' . "\n ";

        return $content;
    }

//    function download_file($archivo, $downloadfilename = null) {
//
//        if (file_exists($archivo)) {
//            $downloadfilename = $downloadfilename !== null ? $downloadfilename : basename($archivo);
//            header('Content-Description: File Transfer');
//            header('Content-Type: application/octet-stream');
//            header('Content-Disposition: attachment; filename=' . $downloadfilename);
//            header('Content-Transfer-Encoding: binary');
//            header('Expires: 0');
//            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//            header('Pragma: public');
//            header('Content-Length: ' . filesize($archivo));
//
//            ob_clean();
//            flush();
//            readfile($archivo);
//            exit;
//        }
//    }

    private function genera_index_file($name_project, $author) {

        $index = '';
        $index .= '<?php' . "\n";
        $index .= '/**' . "\n";
        $index .= " * $name_project For Help & Support http://www.selectomer.com" . "\n";
        $index .= ' *' . "\n" . " *  @author    $author   <info@selectomer.com>" . "\n";
        //calculo del aÃƒÂ±o actual
        $date = getDate();
        $index .= ' *  @copyright' . $date['year'] . " $author" . "\n";
        $index .= ' *  @license   CopyRight' . "\n" . ' * */' . "\n";

        $index .= 'header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");' . "\n";
        $index .= 'header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");' . "\n";
        $index .= 'header("Cache-Control: no-store, no-cache, must-revalidate");' . "\n";
        $index .= 'header("Cache-Control: post-check=0, pre-check=0", false);' . "\n";
        $index .= 'header("Pragma: no-cache");' . "\n";
        $index .= 'header("Location: ../");' . "\n";
        $index .= 'exit;' . "\n";

        return $index;
    }

    private function zip($source, $destination, $name_project) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;


                $file = realpath($file);
                $source = realpath($source);

//                $porciones = explode($name_project, $file);
//                p('file' . $file);
//                p('por1' . $porciones[0]);
//                p('por2' . $porciones[1]);
//                p('source' .$source);               

                if (is_dir($file) === true) {
                    p('file ' . $file);
                    p('source ' . $source);
                    p('reemplazo dir: ' . str_replace($source . '/', '', $file . '/'));
//                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    $ruta = explode($source . '/', $file);
                    p('ruta0 ' . $ruta[0]);
                    p('ruta1 ' . $ruta[1]);
                    p('ruta2 ' . $ruta[2]);
                    $zip->addEmptyDir(explode($source . '/', $file . '/'));
                } else if (is_file($file) === true) {
                    p('file ' . $file);
                    p('source ' . $source);
                    p('reemplazo file ' . str_replace($source . '/', '', $file), file_get_contents($file));
//                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    $ruta = explode($source . '/', $file);
                    p('ruta0 ' . $ruta[0]);
                    p('ruta1 ' . $ruta[1]);
                    p('ruta2 ' . $ruta[2]);
                    $zip->addFromString(explode($source . '/', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}