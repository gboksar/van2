<?php
/**
 * DB structure
 *
 * @filesource
 * @author Pablo Erartes (pabloeuy@gmail.com) / GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @subpackage model
 * @version 1.44
 *
 */

include_once('./cfg.php');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//                      W E B   S E R V I C E
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
$name = 'wsVAN';

$server = new nusoap_server;
$server->configureWSDL($name, 'urn:'.$name);
$server->wsdl->schemaTargetNamespace = WS_VAN_URL;

// Registro de los métodos expuestos
$server->register('despacho',          			  // Nombre del método
	array('xml' => 'xsd:string'),     // Entradas
	array('return' => 'xsd:string'),  // Salidas
	'urn:'.$name,                     // namespace
	'urn:'.$name.'#peticion',         // soapaction (URI del header)
	'rpc',                            // protocolo
	'encoded',                        // como se encuentran los datos en el xml
	$name.' '.VERSION.' - Registra movimiento enviado por Operador/Despachante y retorna HASH del movimiento. Puede ver m&aacute;s documentaci&oacute;n en <a href="./ayuda">este enlace</a>.' // documentación del método
);
$server->register('consulta', // Nombre del método
        array('xml' => 'xsd:string'), // Entradas
        array('return' => 'xsd:string'), // Salidas
        'urn:'.$name, // namespace
        'urn:'.$name.'#consulta', // soapaction (URI del header)
        'rpc', // protocolo
        'encoded', // como se encuentran los datos en el xml
        $name.' '.VERSION.' - Retorna la respuesta de Aduana correspondiente la transaccion solicitada mediante el HASH y el documento. Puede ver m&aacute;s documentaci&oacute;n en <a href="./ayuda">este enlace</a>.' // documentación del método
);
$server->register('status', // Nombre del método
        array('xml' => 'xsd:string'), // Entradas
        array('return' => 'xsd:string'), // Salidas
        'urn:'.$name, // namespace
        'urn:'.$name.'#status', // soapaction (URI del header)
        'rpc', // protocolo
        'encoded', // como se encuentran los datos en el xml
        $name.' '.VERSION.' - Retorna el estado del WS. Puede ver m&aacute;s documentaci&oacute;n en <a href="./ayuda">este enlace</a>.' // documentación del método' // documentación del método
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);