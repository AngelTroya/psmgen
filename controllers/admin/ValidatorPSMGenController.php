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
class ValidatorPSMGenController extends ModuleAdminController {

    public $error = '';
    public $num_warnings = 0;

    public function __construct() {
        $this->display = 'view';
        $this->meta_title = $this->l('ValidatorPSMGen');
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function setMedia() {
        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJquery();
        }
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/css/psmgen.css');
        $this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/js/psmgen.js');

        return parent::setMedia();
    }

    public function initToolBarTitle() {
        $this->toolbar_title = $this->l('PrestaShop Module Validator');
    }

    public function initToolBar() {
        return true;
    }

    public function initContent() {
        $info = '';

        if (Tools::getAdminTokenLite('AdminModules') == Tools::getValue('token')) {
            if (Tools::isSubmit('btnSubmit')) {
                $info = $this->postProcess();
            }
        }

//Get links
        $controller_link = Context::getContext()->link->getAdminLink('ValidatorPSMGen');

        $this->context->smarty->assign('path', _MODULE_DIR_ . $this->module->name . '/');
        $this->context->smarty->assign('controller_link', $controller_link);
        $this->context->smarty->assign('info', $info);

        //$form = $this->_display_admin();
        //$this->context->smarty->assign('form1', $form);
//        $this->context->smarty->assign('content_only' => '1');

        $this->setTemplate('../../../../modules/' . $this->module->name . '/views/templates/admin/validatortemplate.tpl');
//$smarty->assign('items', $items_list);

        parent::initContent();
    }

    //comprobar si se ha enviado algo y comprobar a posteriori si es un zip
    public function postProcess() {
        $info = '';
        //descomprimo el archivo zip una vez se haya enviado
        if (Tools::isSubmit('btnSubmit')) {
            $res = true;

            $tmp_name = $_FILES['nombre_archivo_cliente']['tmp_name'];
            $new_name = $_FILES['nombre_archivo_cliente']['name'];

            $zip = new ZipArchive;
            $unzipdir = dirname(__FILE__) . "/unzip/";

            $dstdirname = $unzipdir . $new_name;

            move_uploaded_file($tmp_name, $dstdirname);
            $path = pathinfo(realpath($unzipdir), PATHINFO_DIRNAME);
            $res = $zip->open("$dstdirname");
            if ($res) {
                $path = $path . '/unzip';
                $path = realpath($path);
                $zip->extractTo($path);
                $zip->close();
            }

            //-----------------------------------------------
            //Aquí realizo las funciones de validación.
            //Compruebo si existen todos los directorios adecuados en la estructura y los creo sino existen.
//            $this->errors[] = array();
            $unzip_path = $path . '/' . substr($new_name, 0, -4);
            $unzip_path = realpath($unzip_path);

            if (!file_exists($unzip_path)) {
                $this->errors[] = $this->l("The project don't exists.\n");
            } else {
                $this->validateDir($unzip_path);
                $this->validateIndex($unzip_path, $new_name);
                //$this->context->smarty->assign('stringConfirmation', $this->error);
//                p($this->error);
            }

            //Por último debo enviar y borrar tanto el zip como el descomprimido de la carpeta.
            //-----------------------------------------------

            if ($res) {
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

    public
            function ajaxProcessAdminModule() {
        $this->context->smarty->assign(array('content_only' => '1'));

        die('data');
    }

    private function validateDir($dstdirname) {

        //existe el proyecto y comprobamos la estructura interna
        //controllers
        $validate_dir = realpath($dstdirname . '/controllers/');
        p("directorio" . $dstdirname);

        if (!file_exists($validate_dir)) {

            //creamos los directorios que necesitamos.
            if (mkdir("$dstdirname/controllers") && mkdir("$dstdirname/controllers/admin") && mkdir("$dstdirname/controllers/front")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio controllers no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio controllers no encontrado. Cree el directorio controllers y los subdirectorios admin y front.\n");
            }
        }
        //css

        $validate_dir = realpath($dstdirname . '/css/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/css")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio css no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio css no encontrado. Cree el directorio css.\n");
            }
        }
        //img
        $validate_dir = realpath($dstdirname . '/img/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/img")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio img no encontrado. Cree el directorio img.\n");
            } else {
                $this->errors[] = $this->l("Directorio img no encontrado. Cree el directorio img.\n");
            }
        }

        //js
        $validate_dir = realpath($dstdirname . '/js/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/js")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio js no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio js no encontrado. Cree el directorio js.\n");
            }
        }
        //override
        $validate_dir = realpath($dstdirname . '/override/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/override")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio override no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio override no encontrado. Cree el directorio override.\n");
            }
        }
        //translations
        $validate_dir = realpath($dstdirname . '/translations/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/translations")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio translations no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio translations no encontrado. Cree el directorio translations.\n");
            }
        }
        //views
        $validate_dir = realpath($dstdirname . '/views/');
        if (!file_exists($validate_dir)) {
            if (mkdir("$dstdirname/views") && mkdir("$dstFilename/views/templates") && mkdir("$dstFilename/views/templates/admin") && mkdir("$dstFilename/views/templates/front") && mkdir("$dstFilename/views/templates/hook")) {
                $this->num_warnings++;
                $this->warnings[] = $this->l("$this->num_warnings. Directorio views no encontrado, se ha creado correctamente.\n");
            } else {
                $this->errors[] = $this->l("Directorio views no encontrado. Cree el directorio views y los subdirectorios templates/admin, templates/front y templates/hook.\n");
            }
        }

        //eliminamos carpetas creadas durante el desarrollo inútiles en la versión final.
        $validate_dir = realpath($dstdirname . '/__MACOSX/');
        if (file_exists($validate_dir)) {
            $this->eliminarDir($validate_dir);
            $this->num_warnings++;
            $this->warnings[] = $this->l("$this->num_warnings. Directorio __MACOSX encontrado, se ha eliminado correctamente.\n");
        }
        $validate_dir = realpath($dstdirname . '/.DS_Store/');
        if (file_exists($validate_dir)) {
            $this->eliminarDir($validate_dir);
            $this->num_warnings++;
            $this->warnings[] = $this->l("$this->num_warnings. Directorio .DS_Store encontrado, se ha eliminado correctamente.\n");
        }
        $validate_dir = realpath($dstdirname . '/nbproject/');
        if (file_exists($validate_dir)) {
            $this->eliminarDir($validate_dir);
            $this->num_warnings++;
            $this->warnings[] = $this->l("$this->num_warnings. Directorio nbproject encontrado, se ha eliminado correctamente.\n");
        }
        $validate_dir = realpath($dstdirname . '/.git/');
        if (file_exists($validate_dir)) {
            $this->eliminarDir($validate_dir);
            $this->num_warnings++;
            $this->warnings[] = $this->l("$this->num_warnings. Directorio .git encontrado, se ha eliminado correctamente.\n");
        }
    }

    private function validateIndex($dstdirname, $new_name) {

        //array con todas las direcciones de los archivos index a validar
        $validate_dir_array = array(
            0 => $dstdirname . '/index.php',
            1 => $dstdirname . '/controllers/index.php',
            2 => $dstdirname . '/controllers/admin/index.php',
            3 => $dstdirname . '/controllers/front/index.php',
            4 => $dstdirname . '/css/index.php',
            5 => $dstdirname . '/img/index.php',
            6 => $dstdirname . '/js/index.php',
            7 => $dstdirname . '/override/index.php',
            8 => $dstdirname . '/translations/index.php',
            9 => $dstdirname . '/views/index.php',
            10 => $dstdirname . '/views/templates/index.php',
            11 => $dstdirname . '/views/templates/admin/index.php',
            12 => $dstdirname . '/views/templates/front/index.php',
            13 => $dstdirname . '/views/templates/hook/index.php');

        /* busco algún archivo index.php en el directorio. 
         * Si existe será el que copiemos a todos los directorios que no tengan un archivo index.
         */
        $exist_index = false;
        $i = 0;

        while ($i < count($validate_dir_array) && !$exist_index) {
            if (file_exists($validate_dir_array[$i])) {
                $exist_index = true;
                $index_file = $validate_dir_array[$i];
            }
            $i++;
        }

        if ($exist_index) {//copiamos el index a todas las rutas que no tengan index
            foreach ($validate_dir_array as $dir_index) {
                if (!file_exists($dir_index)) {
                    copy($index_file, $dir_index);

                    //añadimos a error aquellos que no existan.
                    $array_error = explode($new_name, $dir_index);
                    $this->num_warnings++;
                    $this->warnings[] = $this->l("$this->num_warnings. No existe el archivo $dir_index, será creado automáticamente.\n");
                }
            }
        } else {//sino existe index crearemos uno
            $fpindex = fopen("$dstdirname/controllers/index.php", "w+");
            if ($fpindex == false) {
                die('No se ha podido crear el archivo index.php.');
            }

            $index = $this->genera_index_file($new_name);

            $fwrite = fwrite($fpindex, $index);
            if ($fwrite === false) {
                die('No se ha podido escribir el archivo index.php.');
            }

            fclose($fpindex);

            $this->num_warnings++;
            $this->errors[] = $this->l("$this->num_warnings. No existe el archivo $dir_index, será creado automáticamente.\n");
            //copiamos index.php en los directorios donde deben encontrarse
            if (!copy("$dstdirname/controllers/index.php", "$dstdirname/controllers/admin/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/controllers/front/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/css/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/img/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/js/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/override/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/translations/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/views/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/views/templates/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/views/templates/admin/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/views/templates/front/index.php") ||
                    !copy("$dstdirname/controllers/index.php", "$dstdirname/views/templates/hook/index.php")) {
                d("Error al copiar index \n");
            }
        }
//        p('nombre: ' . $new_name);
//        p('directorio: ' . $dir_index);
        $dstdirname .= '/';
        $this->validarArchivos($dstdirname);
    }

    private function genera_index_file($new_name) {

        $index = '';
        $index .= '<?php' . "\n";
        $index .= '/**' . "\n";
        $index .= " * $new_name For Help & Support http://www.selectomer.com" . "\n";
        $index .= ' *' . "\n" . " *  @author    ValidatorPSMGen   <info@selectomer.com>" . "\n";
        //calculo del año actual
        $date = getDate();
        $index .= ' *  @copyright' . $date['year'] . " ValidatorPSMGen" . "\n";
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

    private function validarArchivos($path) {
        // Abrimos la carpeta que nos pasan como parámetro
        $dir = opendir($path);
        // Leo todos los ficheros de la carpeta
        while ($elemento = readdir($dir)) {
            // Si el archivo pertenece a alguna de las carpetas a validar.
            if ($elemento != "." && $elemento != ".." && $elemento != "images" && $elemento != "css" && $elemento != "includes" && $elemento != "translations") {
                // Si es una carpeta
                if (is_dir($path . $elemento)) {
                    // Muestro la carpeta
                    $this->validarArchivos($path . $elemento . "/");
//                    echo "<p><strong>CARPETA: " . $path . $elemento . "</strong></p>";
                    // Si es un fichero
                } else {
                    // Muestro el fichero
//                    echo "<br />" . $path . $elemento;
                    $trozos = explode(".", $elemento);
                    $extension = end($trozos);
//                    //si es archivo php
                    if (($extension == 'php')) {
                        $this->leerArchivoPhp($path . $elemento);
                    }
//                    //si es archivo tpl
//                    if (($extension == 'tpl')) {
//                        $this->leerArchivoTpl($path . $elemento);
//                    }
                }
            }
        }
    }

    private function leerArchivoPhP($file) {
        $cont = 0;
        $fileopen = fopen($file, "r") or exit("Error abriendo fichero!");
//Lee línea a línea y guarda el archivo en un vector
        while ($linea = fgets($fileopen)) {
            if (feof($fileopen))
                break;
            $arrayfile[$cont] = $linea;
            $cont++;
        }
        $pattern = '/\sfunction\s/';
        fclose($fileopen);
        $this->validarArhivoPhp($arrayfile, $file);
    }

    private function leerArchivoTpl($file) {
        $fileopen = fopen("$file", "w+") or exit("Error abriendo fichero!");
        //Lee línea a línea y escribela hasta el fin de fichero
        while ($linea = fgets($fileopen)) {
            if (feof($fileopen))
                break;
            echo $linea . "<br />"; //aquí haré lo que tenga que hacer con la línea leída.
        }
        fclose($fileopen);
    }

    private function eliminarDir($carpeta) {
        foreach (glob($carpeta . "/*") as $archivos_carpeta) {
            if (is_dir($archivos_carpeta)) {
                $this->eliminarDir($archivos_carpeta);
            } else {
                unlink($archivos_carpeta);
            }
        }

        rmdir($carpeta);
    }

    private function validarArhivoPhp($arrayfile, $file) {
        //valido linea a linea cada archivo php
        $linea = 1;

        //busco el sistema operativo del cliente para generar la ruta del error
        $info = $this->detect();

        //p('file' . $file);
        if ($info["os"] == "WIN") {
            $dst_array = explode('\\', $file);
            $error_dir = $dst_array[count($dst_array) - 1];
        } else {
            //aquí debo obtener el mismo $error_dir que para windows pero para unix
        }
//        p($error_dir);

        for ($linea; $linea <= count($arrayfile); $linea++) {

            if (Tools::getValue("module_post") == 1) {
                //comprobar si existe función count en una línea for
                //patrón de for
                $pattern_for = '\Wfor\s*\(.*\)';
                $pattern_count = '\Wcount\s*\(.*\)';
                //si estamos en la cabecera de un for y contiene la función count
                if (preg_match($pattern_for, $linea)) {
                    if (preg_match($pattern_count, $linea)) {
                        $this->num_warnings++;
                        $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir .Linea $linea. Utilizado " . 'count' . " en la cabecera de un for. No es aconsejable su uso directo en una cabecera.");
                    }
                }
                //si contiene la palabra $_POST la cambiamos por la función que utiliza PrestaShop(caso con comillas dobles).
                $pattern = '/\$_POST\s*\[\"\w+\"\]/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {
                    //nos quedamos con el nombre de la variable que sacamos de $_POST
                    $coincidencia = $coincidencias[count($coincidencias) - 1];
                    $variables = explode('"', $coincidencia);

                    //creamos la línea nueva y reemplazamos por la antigua
                    $sustituta = "Tools::getValue(\"$variables[1]\")";
                    $resul = str_replace($coincidencia, $sustituta, $arrayfile[$linea]);
                    $arrayfile[$linea] = $resul;

                    //añadimos el error a la lista de errores
                    $this->num_warnings++;
                    $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir .Linea $linea. Utilizado " . '$_POST' . ". En su lugar debe utilizarse Tools::getValue, por lo que fue sustituido.");
                }
                //si contiene la palabra $_POST la cambiamos por la función que utiliza PrestaShop(caso con comillas simples).

                $pattern = '/\$_POST\s*\[\'\w+\'\]/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {
                    //nos quedamos con el nombre de la variable que sacamos de $_POST
                    $coincidencia = $coincidencias[count($coincidencias) - 1];
                    $variables = explode('"', $coincidencia);

                    //creamos la línea nueva y reemplazamos por la antigua
                    $sustituta = "Tools::getValue(\'$variables[1]\')";
                    $resul = str_replace($coincidencia, $sustituta, $arrayfile[$linea]);
                    $arrayfile[$linea] = $resul;

                    //añadimos el error a la lista de errores
                    $this->num_warnings++;
                    $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir .Linea $linea. Utilizado " . '$_POST' . ". En su lugar debe utilizarse Tools::getValue, por lo que fue sustituido.");
                }
            }
            //----------------------------------------------------------------------------------------------
            if (Tools::getValue("function_isset") == 1) {
                //si contiene la función isset() la sustituiremos por Tools::getIsset()
                $pattern = '/isset\s*\(.+\)/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {

                    $coincidencia = "isset";
                    //creamos la línea nueva y reemplazamos por la antigua
                    $sustituta = "Tools::getIsset";
                    $resul = str_replace($coincidencia, $sustituta, $arrayfile[$linea]);
                    $arrayfile[$linea] = $resul;

                    //añadimos el error a la lista de errores
                    $this->num_warnings++;
                    $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir. Linea $linea. Utilizada la función " . 'isset' . ". En su lugar debe utilizarse Tools::getIsset, por lo que fue sustituido.");
                }
            }

            //----------------------------------------------------------------------------------------------

            if (Tools::getValue("module_fgc") == 1) {
                //Valido y corrijo las apariciones de file_get_contents()
                $pattern = '/file_get_contents\s*\(.+\)/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {
                    //si no es Tools::file_get_content
                    $pattern2 = '/Tools::file_get_contents\s*\(.+\)/';
                    if (!preg_match($pattern2, $arrayfile[$linea])) {
                        $coincidencia = "file_get_contents";
                        //creamos la línea nueva y reemplazamos por la antigua
                        $sustituta = "Tools::file_get_contents";
                        $resul = str_replace($coincidencia, $sustituta, $arrayfile[$linea]);
                        $arrayfile[$linea] = $resul;

                        //añadimos el error a la lista de errores
                        $this->num_warnings++;
                        $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir. Linea $linea. Utilizada la función " . 'file_get_contents' . ". En su lugar debe utilizarse Tools::file_get_contents, por lo que fue sustituido.");
                    }
                }
            }

            //----------------------------------------------------------------------------------------------
            //Tools::redirect()
            if (Tools::getValue("function_header") == 1) {
                $pattern = '/header\s*\(.+\)/';
                $file_pattern = '/index.php$/';
                //si no es un archivo index.php
                if (!preg_match($file_pattern, $file)) {
                    if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {
                        //si no es Tools::file_get_content
                        $coincidencia = "header";
                        //creamos la línea nueva y reemplazamos por la antigua
                        $sustituta = "Tools::redirect()";
                        $resul = str_replace($coincidencia, $sustituta, $arrayfile[$linea]);
                        $arrayfile[$linea] = $resul;

                        //añadimos el error a la lista de errores
                        $this->num_warnings++;
                        $this->warnings[] = $this->l("$this->num_warnings. Archivo $error_dir. Linea $linea. Utilizada la función " . 'header' . ". En su lugar debe utilizarse Tools::redirect, por lo que fue sustituido.");
                    }
                }
            }

            //$_SESSION
            if (Tools::getValue("module_session") == 1) {
                $pattern = '/\$_SESSION\s*\[\'\w+\'\]/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {

                    //añadimos el error a la lista de errores
                    $this->errors[] = $this->l("Archivo $error_dir. Linea $linea. Se ha detectado el uso de " . '$_SESSION' . ". Su utilización está prohibida.");
                }

                $pattern = '/\$_SESSION\s*\[\"\w+\"\]/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {

                    //añadimos el error a la lista de errores
                    $this->errors[] = $this->l("Archivo $error_dir. Linea $linea. Se ha detectado el uso de " . '$_SESSION' . ". Su utilización está prohibida.");
                }
//CORRIGIENDO ERRORES VOY POR AQUÍ
                //session_start()
                $pattern = '/session_start\s*\(.*\)/';
                if (preg_match($pattern, $arrayfile[$linea], $coincidencias)) {
                    //añadimos el error a la lista de errores
                    $this->errors[] = $this->l("Archivo $error_dir. Linea $linea. Se ha detectado el uso de " . 'session_start()' . ". Su utilización está prohibida.\n");
                }
            }

            //validamos los espacios en blanco delante de el operador =
            //patrón para un = sin espacio delante
            $pattern = '/\S=/';
            //patrón para dos == sin espacio delante
            $pattern = '\S={2}';
            //patrón para tres === sin espacio delante
            $pattern = '\S={3}';
            //si se cumple el de 3 no compruebo el de dos y sino compruebo el de dos compruebo el de uno pensar los menores y mayores que)
            unset($coincidencias);

            //uso de la función p() está prohibido.
            $pattern = '\Wp\(.+\)';
            if (preg_match($pattern, $arrayfile[$linea])) {
                $this->errors[] = $this->l("Archivo $error_dir. Linea $linea. Se ha detectado el uso de " . 'p()' . ". Su utilización está prohibida.\n");
            }
        }
    }

    /**
     * Funcion que devuelve un array con los valores:
     * 	os => sistema operativo
     * 	browser => navegador
     * 	version => version del navegador
     */
    private function detect() {
//        $browser = array("IE", "OPERA", "MOZILLA", "NETSCAPE", "FIREFOX", "SAFARI", "CHROME");
        $os = array("WIN", "MAC", "LINUX");

        # definimos unos valores por defecto para el navegador y el sistema operativo
//        $info['browser'] = "OTHER";
        $info['os'] = "OTHER";

        # buscamos el navegador con su sistema operativo
//        foreach ($browser as $parent) {
//            $s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent);
//            $f = $s + strlen($parent);
//            $version = substr($_SERVER['HTTP_USER_AGENT'], $f, 15);
//            $version = preg_replace('/[^0-9,.]/', '', $version);
//            if ($s) {
//                $info['browser'] = $parent;
//                $info['version'] = $version;
//            }
//        }
        # obtenemos el sistema operativo
        foreach ($os as $val) {
            if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $val) !== false)
                $info['os'] = $val;
        }

        # devolvemos el array de valores
        return $info;
    }

    //cambiar tipo de versiones a 1.0.0
    //comprobar que las variables creadas son utilizadas.
    //definir todas las variables
    //comprobar si tiene cabecera todo fichero (licencias)
    //buscar en tpl si existen escapes
    //simples comillas a menos que haya variables en el texto(ojo con las expresiones regulares)
    //aconsejar no usar la función count dentro de la condición de un bucle. Preferible guardar en variable previamente
    //espacios delante y detrás del comando =, ==, ===, !=, >= ,<=
    //si un archivo no es llamado en su directorio y ha sido movido cambiar la ruta si aparece 
}
