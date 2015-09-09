<?php 
/**
 * Conexion con la base de datos
 *
 * @filesource
 * @author Pablo Erartes (pabloeuy@gmail.com) / GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @subpackage model
 * @version 0.1a
 *
 */

define('VERSION', 0.5);

// include local config
include_once('./cfg.local.php');
// include params (se administran por app /admin
include_once('./app.params.php');

// include lib
include_once('./lib/nusoap-0.9.5/lib/nusoap.php'); // Definicion de WS locales
include_once('./lib/xml2array.php'); // Conversion de XML a arrays y viceversa
include_once('./lib/db.class.php'); // Funciones para administrar la base de datos
require_once('./lib/xmlseclibs/xmlseclibs.php'); // Validacion de firmas de los despachantes
require_once('./lib/SoapClientAuth.php'); // WS autenticados contra Aduana

// include components
include_once('./core/metodos.php');
include_once('./core/peticion.class.php');
include_once('./core/respuesta.class.php');

// dirctorios de uso interno
define('DIR_TMP', './tmp'); // Para la creacion de archivos temporales en caso de ser necesarios
define('DIR_XML', './xmls'); // Para almacenar los XML de los despachantes y respuestas de aduana a modo de control
define('DIR_LOG', './log'); // Para almacenar los logs del sistema
define('LOG_DIR', DIR_LOG);
define('OFFLINE_FLAG', DIR_LOG . '/flags/van.offline'); // Flag para determinar si el servicio esta o no on-line atendiendo despachos
define('CONTROL_DB', 'controlVAN');

// Log files name prefix like <date_ansi>_<host_ip>_<webservice>.log
// Logs must have the following data:
//<date_time>~<webservice>~<sender_ip>~<RUT_despachante>~[<transaccion_id>]~<sizeOf_base64_content>~<tiempo_recepcion>~<tiempo_aduana>~<tiempo_total>~<respuesta_aduana_resumida>~<estadoFinal_transaccion>
define('LOG_NAME_PREFIX', date('Ymd') . '_' . $_SERVER['SERVER_ADDR'] . '_');

// Nombre de las bases de datos
// Se crean bases de datos semanales por año con el formato van_201504
// Las semanas van de la 1 a la 53, tomandose como 53 los ultimos dias
// del año y partiendo la semana a la mitad de ser necesario.
// Este fix se hace a pedido de ADAU, ya que no existe semana 53, para los 
// sistemas, normalmente los ultimos dias del año se consideran como parte
// de la semana 1 del siguiente.
$hoy = new DateTime( date("Y-m-d") );
$w = $hoy->format("W");
$m = $hoy->format("m");
$y = $hoy->format("Y");
if($w=="01" && intval($m)==12){
  $w="53";
}

define('DB_NAME',DATABASE_NAME . '_'. $y.$w);

function _tiempo() {
	$mtime = explode(" ", microtime());
	return $mtime[1] + $mtime[0];
}

function _WS_URL($servicio) {
    $WS_DATA = array();
    if(MODO=='prod'){
        $WS_DATA['URI']  = PRODUCCION_PROTOCOLO . "://" . PRODUCCION_USUARIO . ":" . PRODUCCION_CLAVE . "@" . PRODUCCION_URL;
        $WS_DATA['USER'] = PRODUCCION_USUARIO;
        $WS_DATA['PASS'] = PRODUCCION_CLAVE;
        $WS_DATA['TIMEOUT'] = PRODUCCION_TIMEOUT;
    }else{
        $WS_DATA['URI']  = TEST_PROTOCOLO . "://" . TEST_USUARIO . ":" . TEST_CLAVE . "@" . TEST_URL;
        $WS_DATA['USER'] = TEST_USUARIO;
        $WS_DATA['PASS'] = TEST_CLAVE;
        $WS_DATA['TIMEOUT'] = TEST_TIMEOUT;
    }
    $WS_DATA['URI'] .=  $servicio . "?WSDL";
    return $WS_DATA;
}