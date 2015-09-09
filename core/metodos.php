<?php

/**
 * WSController
 *
 * @filesource metodos.php
 * @author GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @version 2.0a
 *
 */
function despacho($xml) {
    $tiempoinicial = _tiempo();
    $mInicio = date("Y-m-d H:i:s");

    $xmlResult = "";
    $xmlPeticion = new peticion($xml);
    $tipo = 'I';
    // Archivo peticion de procesamiento sin verificar.
    // Se almacena tal cual es recibido... se loguea y luego se procede a varificar
    $tiempo = $xmlPeticion->guardarPeticion_entrada($tiempoinicial, $tipo);
    
    //Log provisorio...
    $log_file = 'van_' . $_SERVER['SERVER_ADDR'] . '_peticion_' . date('Ymd') . '.log';
    $log_line = date("Ymd") . "~" . date("His") . '~';
    $log_line .= trim($_SERVER['REMOTE_ADDR']) . '~';
    $log_line .= $xmlPeticion->getPeticionId() . '~';
    $log_line .= ($xmlPeticion->cantidadErrores() > 0?'ERROR':'OK') . '~';
    $log_line .= $tiempo . "\n";
    if (!$file = fopen(LOG_DIR . '/' . $log_file, 'a')) {
        $this->agregarError('ERROR DE LOG (' . LOG_DIR . '/' . $log_file . ')');
    } else {
        if (fwrite($file, $log_line) === FALSE) {
            $this->agregarError('ERROR DE LOG (grabando... ' . LOG_DIR . '/' . $log_file . ')');
        }
    }
    // Fin log
    
    $tiempoProceso = _tiempo();
    //Valido XML recibido...
    if ($xmlPeticion->validoEstructuraXml()) {
        // Si es válido, proceso el documento...
        $xmlPeticion->parseXmlRecibido();
        $log_file = 'van_' . $_SERVER['SERVER_ADDR'] . '_documento_' . date('Ymd') . '.log';
        if ($xmlPeticion->cantidadErrores() == 0) {
            // Si todo es correcto, envío a Aduanas y espero por respuesta
            //$xmlPeticion->guardarDocumento($tiempoProceso, $tiempoinicial, $tipo);
            
            // Envio a Aduana el despacho y espero respuesta... y registra el envio y su respuesta
            // en caso que venga uno.
            $xmlPeticion->enviarAAduana(0);
            
            // Log provisorio...
            $log_file = 'van_' . $_SERVER['SERVER_ADDR'] . '_envioAduana_' . date('Ymd') . '.log';
            $log_line = date("Ymd") . "~" . date("His") . '~';
            $log_line .= trim($_SERVER['REMOTE_ADDR']) . '~';
            $log_line .= trim($id) . '~';
            $log_line .= 'OK' . "\n";
            if (!$file = fopen(LOG_DIR . '/' . $log_file, 'a')) {
                $this->agregarError('ERROR DE LOG (' . LOG_DIR . '/' . $log_file . ')');
            } else {
                if (fwrite($file, $log_line) === FALSE) {
                    $this->agregarError('ERROR DE LOG (grabando... ' . LOG_DIR . '/' . $log_file . ')');
                }
            }
            $tipo = 'D';
            // Fin log
        }else{
            $log_line = date("Ymd") . "~" . date("His") . '~';
            $log_line .= trim($_SERVER['REMOTE_ADDR']) . '~';
            $log_line .= trim($id) . '~';
            $log_line .= $xmlPeticion->cantidadErrores() . ' ERRORES' . "\n";
            if (!$file = fopen(LOG_DIR . '/' . $log_file, 'a')) {
                $this->agregarError('ERROR DE LOG (' . LOG_DIR . '/' . $log_file . ')');
            } else {
                if (fwrite($file, $log_line) === FALSE) {
                    $this->agregarError('ERROR DE LOG (grabando... ' . LOG_DIR . '/' . $log_file . ')');
                }
            }
        }
        // Actualizo datos del documento con el tiempo de procesamiento y envío a aduanas...
        
    }

    // Segun Resultado
    $xmlRespuesta = new respuesta($xmlPeticion);
    $huboError = false;
    if ($xmlPeticion->cantidadErrores() == 0) {
        $xmlResult = $xmlRespuesta->obtenerXML_OK($xmlPeticion);
    } else {
        $huboError = true;
        $xmlResult = $xmlRespuesta->obtenerXML_Error($xmlPeticion);
    }
    
    $log_file = 'van_' . $_SERVER['SERVER_ADDR'] . '_peticion_' . date('Ymd') . '.log';
    $log_line = date("Ymd") . "~" . date("His") . '~';
    $log_line .= trim($_SERVER['REMOTE_ADDR']) . '~';
    $log_line .= trim($id) . '~';
    $log_line .= ($huboError?'ERROR':'OK') . "\n";

    if (!$file = fopen(LOG_DIR . '/' . $log_file, 'a')) {
        $this->agregarError('ERROR DE LOG (' . LOG_DIR . '/' . $log_file . ')');
    } else {
        if (fwrite($file, $log_line) === FALSE) {
            $this->agregarError('ERROR DE LOG (grabando... ' . LOG_DIR . '/' . $log_file . ')');
        }
    }
    
    // Se almacena la respuesta final con el tiempo total del proceso...
    $xmlRespuesta->guardarRespuesta($xmlPeticion, $mInicio, $tiempoinicial);
    return $xmlResult;
}

function consulta($xml) {
    $mInicio = date("Y-m-d H:i:s");
    $tiempoinicial = _tiempo();

    $xmlResult = "";
    $tipo = "I";
    $xmlPeticion = new peticion($xml);
    if ($xmlPeticion->validoEstructuraXmlConsulta()) {
        // cargo datos
        $xmlPeticion->parseXmlRecibidoConsulta();
        // Si no hay errores
        if ($xmlPeticion->cantidadErrores() == 0) {
            $xmlPeticion->enviarAAduana($tiempoinicial);
            $tipo = 'C';
        }
    }
    $xmlPeticion->guardarPeticion($mInicio, $tiempoinicial, $tipo);
    

    // Segun Resultado
    $xmlRespuesta = new respuesta($xmlPeticion);
    if ($xmlPeticion->cantidadErrores() == 0) {
        $xmlResult = $xmlRespuesta->obtenerXML_OK($xmlPeticion);
        //$xmlPeticion->guardoXMLs($id);
    } else {
        $xmlResult = $xmlRespuesta->obtenerXML_Error($xmlPeticion);
    }
    $log_file = 'van_' . $_SERVER['SERVER_ADDR'] . '_' . date('Ymd') . '.log';
    $log_line = date("Ymd") . "~" . date("His") . '~';
    $log_line .= trim($_SERVER['REMOTE_ADDR']) . '~';
    $log_line .= trim($xmlPeticion->getPeticionId()) . '~';
    $log_line .= trim($xmlPeticion->getHash()) . "\n";

    if (!$file = fopen(LOG_DIR . '/' . $log_file, 'a')) {
        $this->agregarError('ERROR DE LOG (' . LOG_DIR . '/' . $log_file . ')');
    } else {
        if (fwrite($file, $log_line) === FALSE) {
            $this->agregarError('ERROR DE LOG (grabando... ' . LOG_DIR . '/' . $log_file . ')');
        }
    }
    $xmlRespuesta->guardarRespuesta($xmlPeticion, $mInicio, $tiempoinicial, $xmlPeticion);
    return $xmlResult;
}