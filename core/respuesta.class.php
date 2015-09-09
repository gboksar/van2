<?php

/**
 * Respuesta a Despachante
 *
 * @filesource respuesta.class.php
 * @author GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @subpackage core
 * @version 2.0a
 *
 */
class respuesta {

    private $_xml;
    private $_hash;
    private $_fecha;
    private $_resumen;
    private $_errores;
    

    /**
     * Constructor
     */
    public function __construct($peticion) {
        $this->_xml             = '';
        $this->_hash            = $peticion->getHash();
        $this->_fecha           = date("c");
        $this->_errores         = $peticion->getErrores();
        $this->_resumen         = '';
    }

    /**
     * Destructor
     */
    public function __destruct() {
        
    }

    private function _generoXML($contenido) {
        $firma = '';
        $this->_xml = '<?xml version="1.0" encoding="utf-8"?>' .
                '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
                '<SOAP-ENV:Body>' .
                "<DAE-ADAU>$contenido<Signature>$firma</Signature></DAE-ADAU>" .
                '</SOAP-ENV:Body></SOAP-ENV:Envelope>';
    }

    public function obtenerXML_OK($peticion) {
        $this->_generoXML("<NumeroVAN>".$peticion->getNro()."</NumeroVan><Hash>".$peticion->getHash() ."</Hash><Fecha>".date("c")."</fecha><Resumen></Resumen><Respuestas><Respuesta><Codigo>0</Codigo><Descripcion>Procedimiento correcto</Descripcion></Respuesta></Respuestas><RespuestaDNA>" .  base64_encode($peticion->getRespuestaAduana()) . "</RespuestaDNA>");
        return $this->_xml;
    }

    public function obtenerXML_Error($peticion) {
        // Parseo errores para base de datos
        $auxErrores = array();
        $contenido = '<Respuestas>';
        foreach ($peticion->getErrores() as $key => $value) {
            $auxErrores[] = $key . '|' . $value;
            $contenido .= "<Respuesta><Codigo>$key</Codigo><Descripcion>$value</Descripcion></Respuesta>";
        }
        $contenido .= '</Respuestas>';
        $contenido .= '<RespuestaDNA>' .  base64_encode($peticion->getRespuestaAduana()) . '</RespuestaDNA>';
        $this->_generoXML($contenido);
        $this->_errores = implode(',', $auxErrores);
        return $this->_xml;
    }

    public function guardarRespuesta($peticion, $mInicio, $tiempoinicial) {
        // Conexion
        $db = db::getInstance();
        $db->executeSql('SET AUTOCOMMIT=0');
        $db->executeSql('START TRANSACTION');

        $tiempofinal = _tiempo();
        $tiempototal = ($tiempofinal - $tiempoinicial);

        $sql = "INSERT INTO `" . DB_NAME . "`.respuesta VALUES (0, '" . $peticion->getPeticionId() . "','" . base64_encode($this->_xml) . "',NOW(), '" . $tiempototal . "')";
        $db->executeSql($sql);
        $db->executeSql('COMMIT');
        return $sql;
    }

    public function obtenerXML_Status($tiempoinicial) {
        $mFin = date("Y-m-d H:i:s");
        $tiempototal = (_tiempo() - $tiempoinicial);

        $error = "<Hash></Hash><Fecha>".date("c")."</fecha><Resumen></Resumen><Respuestas><Respuesta><Codigo>0</Codigo><Descripcion>ON-LINE</Descripcion></Respuesta></Respuestas><RespuestaDNA></RespuestaDNA>";
        $this->_generoXML("", $error);
        return $this->_xml;
    }


}

?>
