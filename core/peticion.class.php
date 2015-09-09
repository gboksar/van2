<?php

/**
 * Peticion despachante
 *
 * @filesource peticion.php
 * @author GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @subpackage core
 * @version 2.0a
 *
 */
class peticion {

    private $_xml;
    private $_xml_id;
    private $_documento;
    private $_intercambio;
    private $_fecha; // Fecha Peticion
    private $_transaccion;
    private $_ws;
    private $_envio;
    private $_tipoEnvio;
    private $_finalURL;
    private $_XMLData;
    private $_peticion_id;
    private $_firmante;
    private $_errores;
    private $_hash;
    private $_sizeXML; // Tamaño del XML a ser procesado (Inluyendo cabezales y sobres)
    private $_sizeDOC; // Tamaño del documento enviado a ser procesado (contenido del tag ENVIO)
    private $_fechaHoraEnvioAduana;
    private $_respuestaAduana;
    
    private $_tiempoRecepcion;  // desde 0
    private $_tiempoAlmacenado; // desde 0
    private $_tiempoProceso; // desde 0
    private $_tiempoRespuestaAduana; //desde 0
    private $_tiempoRespuestaDespachante; // desde 0
    // El tiempo total o parcial del proceso de un documento se obtiene de la suma de los parciales...

    // Errores
    const ERROR_INTERNO_SQL = 'EV01|ERROR INTERNO. PROBLEMAS DE BASE DE DATOS O SISTEMA ';
    const ERROR_DESAUTORIZADO_ADAU = 'EV02|ERROR: UD NO ESTA AUTORIZADO A CONSULTAR LA TRANSACCION SOLICITADA ';
    const ERROR_XML = 'EX01|ERROR: XML RECIBIDO NO TIENE FORMATO VALIDO ';
    const ERROR_XML2 = 'EX02|ERROR: XML RECIBIDO NO TIENE FORMATO VALIDO, FALLA EN ESTRUCTURA! ';
    const ERROR_FIRMA_MAL_FORMADA = 'ES01|ERROR: FIRMA DIGITAL MAL FORMADA ';
    const ERROR_FIRMA_FALLA_INTEGRIDAD = 'ES02|ERROR: INTEGRIDAD DEL MENSAJE COMPROMETIDA ';
    const ERROR_FIRMA_FUERA_DE_FECHA = 'ES03|ERROR: FIRMA DIGITAL VENCIDA ';
    const ERROR_FIRMA_NO_AUTORIZADA = 'ES04|ERROR: FIRMA DIGITAL NO AUTORIZADA POR ADAU ';
    const ADUANA_NO_RESPONDIO = 'EA01|ERROR: EL SERVICIO DE ADUANA ESTA CAIDO, CONSULTE EN UNOS MINUTOS ';
    const ADUANA_CAIDO = 'EA02|ADUANA NO RESPONDIO EN EL MOMENTO, CONSULTE EN UNOS MINUTOS ';

    /*
     * CIVDB: Can't Insert Record In DB
     * STF: Start Transaction Failed - Abort procedure
     *
     */

    /**
     * Constructor
     */
    public function __construct($xml) {
        $this->_xml = $xml;
        $this->_xmlData = '';
        $this->_documento = '';
        $this->_intercambio = '';
        $this->_fecha = '';
        $this->_transaccion = '';
        $this->_ws = '';
        $this->_envio = '';
        $this->_tipoEnvio = '';
        $this->_finalURL = '';
        $this->_XMLData = '';
        $this->_peticion_id = 0;
        $this->_firmante = '';
        $this->_errores = array();
        $this->_fecha = date("Y-m-d H:i:s");
        $this->_fechaHoraEnvioAduana = '';
        $this->_hash = md5($xml . date('YmdHis') . rand()); // el HASH se crea para identificar el documento en el futuro. Se arma en base al XML,fecha-hora y una semilla aleatoria.
        $this->_sizeXML = strlen($xml);
        $this->_sizeDOC = 0;
        $this->_respuestaAduana = '';
        
        $this->_tiempoRecepcion = 0;              // Tiempo que toma recibir el documento
        $this->_tiempoAlmacenado = 0;             // Tiempo que se demora en almacenar en la BD
        $this->_tiempoProceso = 0;                // Tiempo que toma procesar el documento y validarlo
        $this->_tiempoRespuestaAduana = 0;        // Tiempo que toma el envio a Aduana y la respuesta (TO: 60s)
        $this->_tiempoRespuestaDespachante = 0;   // Tiempo que toma la enviar la respuesta al despachante
    }

    /**
     * Destructor
     */
    public function __destruct() {
        
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                      E R R O R E S
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function agregarError($error) {
        $this->_errores[] = $error;
    }

    public function cantidadErrores() {
        return count($this->_errores);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                      V A L I D A C I O N E S
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function validoEstructuraXml() {
        $result = false;
        $aux = xml2array(html_entity_decode($this->_xml));
        if (isset($aux['DAE-ADAU'])) {
            $this->_XMLData = $aux['DAE-ADAU'];
            $result = !empty($this->_XMLData) &&
                    !empty($this->_XMLData['Documento']) &&
                    !empty($this->_XMLData['Intercambio']) &&
                    !empty($this->_XMLData['Fecha']) &&
                    !empty($this->_XMLData['TipoEnvio']) &&
                    !empty($this->_XMLData['Url']) && // En un futuro cambiará por WS
                    !empty($this->_XMLData['Envio']);
            if (!$result) {
                $this->agregarError(self::ERROR_XML);
            }
        } else {
            $this->agregarError(self::ERROR_XML2);
        }
        return $result;
    }

    public function validoEstructuraXmlConsulta() {
        $result = false;
        $aux = xml2array(html_entity_decode($this->_xml));
        if (isset($aux['DAE-ADAU'])) {
            $this->_XMLData = $aux['DAE-ADAU'];
            $result = !empty($this->_XMLData) &&
                    !empty($this->_XMLData['Hash']) &&
                    !empty($this->_XMLData['Documento']);
            if (!$result) {
                $this->agregarError(self::ERROR_XML);
            }
        } else {
            $this->agregarError(self::ERROR_XML2);
        }
        return $result;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                             X M L
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    function parseXmlRecibido() {
        $aux = $this->_XMLData;
        $this->_documento = $aux['Documento'];
        $this->_intercambio = (isset($aux['Intercambio']) ? $aux['Intercambio'] : 0);
        $this->_fecha = $aux['Fecha'];
        $this->_transaccion = (isset($aux['Transaccion']) ? $aux['Transaccion'] : 'n/d');
        $this->_ws = $aux['Url'];
        $this->_envio = $aux['Envio'];
        $this->_tipoEnvio = $aux['TipoEnvio'];
        $this->_peticion_id = 0;
        $this->_firmante = array();
        $this->_errores = array();
        $this->_sizeEnvio = strlen($aux['Envio']); // Obtengo tamaño del documento enviado a procesar en Aduanas
        $this->_respuestaAduana = '';
        $this->_tiempoRespuestaAduana = 0;
        $this->actualizoXML();
    }
    
    private function actualizoXML() {
        $db = db::getInstance();
        $sql = "UPDATE `" . DB_NAME . "`.xml SET "
                . "documento='".$this->_documento."', "
                . "intercambio='".$this->_intercambio."', "
                . "fechaDoc='".$this->_fecha."', "
                . "tipoEnvio='".$this->_tipoEnvio."', "
                . "urlDestino='".$this->_ws."' "
                . "WHERE hash='".$this->_hash."' ";
        if (!$db->executeSql($sql)) {
            $error1 = mysql_error();
            $this->_peticion_id = -1;
            $this->agregarError('ERROR-CIVDB/XMLUPD: ' . self::ERROR_INTERNO_SQL . ' / ' . $error1);
        }
    }

    function parseXmlRecibidoConsulta() {
        $aux = $this->_XMLData;
        $this->_documento = $aux['Documento'];
        $this->_hash = $aux['Hash'];
        $this->_respuestaAduana = '';
        $this->_tiempoRespuestaAduana = 0;
        $this->_getPeticion();
    }

    public function validoRemitente() {
        return $this->_validoDespachante() &&
                $this->_validoFirma();
    }

    private function _validoDespachante() {
//        $req = curl_init(AUTH_URL);
//        curl_setopt($req, CURLOPT_HEADER, 0);
//        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($req, CURLOPT_POST, true);
//        $cert = $this->cert2array();
//        $data = 'documento=' . $this->_XMLenviado['documento'] .
//                '|nroDocTitular=' . $this->_XMLenviado['IdDocumento'] .
//                '|tipoDocEmisor=' . substr($cert['subject']['serialNumber'], 0, 3) .
//                '|nroDocEmisor=' . substr($cert['subject']['serialNumber'], 4) .
//                '|tamanoMensaje=' . strlen($this->_xml) .
//                '|fechaGenerado=' . $this->_XMLenviado['FechaHoraDocumentoElectronico'] .
//                '|fechaRecibido=' . date('Y-m-d H:i:s') .
//                '|numeroRADE=0' .
//                '|numeroInterno=' . $this->_numeroInterno .
//                '|signature=' . http_build_query($this->_XMLenviado['Signature']) .
//                '|tipoDeDocumento=' . $this->_XMLenviado['Objeto']['TipoDocumentoAduana'];
//        $post = array("data" => $data);
//        curl_setopt($req, CURLOPT_POSTFIELDS, $post);
//        $result = curl_exec($req);
//        curl_close($req);
//
////////////////////////////////////// LOG PROVISORIO ///////////////////////////////////////////////
//
//	$log_file = 'consultas_admin_' . $_SERVER['SERVER_ADDR'] . '_' . date('Ymd') . '.log';
//        $fecha = explode('T', $this->_XMLenviado['FechaHoraDocumentoElectronico']);
//        $log_line  = trim($this->_XMLenviado['TipoDocumento']) . '~';
//        $log_line .= trim($this->_XMLenviado['IdDocumento']) . '~';
//        $log_line .= trim($fecha[0]) . '~';
//        $log_line .= trim($fecha[1]) . '~';
//        $log_line .= trim($result) . "\n";
//
//        if (!$file = fopen(LOG_DIR . '/../admin/' . $log_file, 'a')) {
//            $this->agregarError(self::ERROR_LOGFILE . '(' . LOG_DIR . '/../admin/' . $log_file . ')');
//        }else{
//                if (fwrite($file, $log_line) === FALSE) {
//                        $this->agregarError(self::ERROR_LOGFILE . '(grabando... ' . LOG_DIR . '/../admin/' . $log_file . ')');
//                }
//        }
///////////////////////////////////////////////////////////////////////////////////////////////
        return true;
    }

    public function enviarAAduana($tiempoInicial = 0) {
        if($tiempoInicial == 0) {
            $tiempoInicial = _tiempo();
        }
        $WS_DATA = _WS_URL($this->_ws);
        $this->_fechaHoraEnvioAduana = date("Y-m-d H:i:s");
        // Invoco WS usando autenticación!!!
        $param = array('In' => base64_decode($this->_XMLData['Envio']));
        $ws = new SoapClientAuth($WS_DATA['URI'], array(
            'login' => $WS_DATA['USER'],
            'password' => $WS_DATA['PASS'])
        );
        $this->_finalURL = $WS_DATA;
        $ws->setTimeout(intval($WS_DATA['TIMEOUT']));
        $respuesta = $ws->Execute($param);
        $this->_respuestaAduana = $respuesta->Out;

        $mFin = date("Y-m-d H:i:s");
        $this->_tiempoRespuestaAduana = round(_tiempo() - $tiempoInicial, 5);
        $this->guardarDocumento(_tiempo(), $tiempoinicial, $tipo);
    }

    public function getRespuestaAduana() {
        return $this->_respuestaAduana;
    }

    public function getHash() {
        return $this->_hash;
    }

    public function getPeticionId() {
        return $this->_peticion_id;
    }

    private function _getPeticion() {
        $error = '';
        $sql = "SELECT x.* FROM `" . DB_NAME . "`.xml x WHERE hash='" . $this->_hash . "' AND documento='" . $this->_documento . "'";
        $db = db::getInstance();
        $db->selectDB($this->_documento);
        $obj = $db->loadObject($sql);
        if (!is_null($obj)) {
            $this->_xml = $obj->xml;
            $aux = xml2array(base64_decode($obj->xml));
            $this->_XMLData = $aux['DAE-ADAU'];
            $this->_fecha = $obj->fechaDoc;
            $this->_tipoEnvio = $obj->tipoEnvio;
            $this->_peticion_id = $obj->peticion_id;
            $this->_hash = $obj->hash;
            $this->_documento = $obj->documento;
            $this->_intercambio = $obj->intercambio;
            
            $this->_transaccion = (isset($aux['Transaccion']) ? $aux['Transaccion'] : 'n/d');
            $this->_envio = $aux['Envio'];
            $this->_ws = $aux['URL'];
            $this->_size = strlen($aux['Envio']);
            
            $this->_finalURL = '';
            $this->_firmante = '';
            $this->_errores = array();
            $this->_respuestaAduana = '';
            $this->_tiempoRespuestaAduana = 0;
        } else {
            // Si sale por aquí es porque no existe el HASH para ese RUT, equivale a que no puede conusltar el mov.
            $error = true;
            $this->agregarError(self::ERROR_DESAUTORIZADO_ADAU);
        }
    }

    private function _validoFirma() {
        $xml = xml2array($this->_XMLenviado);
        $xml_name = '.' . DIR_TMP . '/' . $this->_hash . '.xml';
        file_put_contents($xml_name, $xml);
        $certValid = false;
        try {
            $doc = new DOMDocument();
            $doc->load('.' . DIR_TMP . '/' . $xml_name);
            $objXMLSecDSig = new XMLSecurityDSig();
            $objDSig = $objXMLSecDSig->locateSignature($doc);
            if (!$objDSig) {
                throw new Exception(self::ERROR_FIRMA_MAL_FORMADA);
            }
            $objXMLSecDSig->canonicalizeSignedInfo();
            $objXMLSecDSig->idKeys = array('wsu:Id');
            $objXMLSecDSig->idNS = array('wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd');
            $retVal = $objXMLSecDSig->validateReference();
            if (!$retVal) {
                throw new Exception(self::ERROR_FIRMA_FALLA_INTEGRIDAD);
            }
            $objKey = $objXMLSecDSig->locateKey();
            if (!$objKey) {
                throw new Exception(self::ERROR_FIRMA_MAL_FORMADA);
            }
            $certificado = openssl_x509_parse($objKey->getX509Certificate());
            $validTo = date("r", $certificado['validTo_time_t']);
            $validFrom = date("r", $certificado['validFrom_time_t']);
            if ($objXMLSecDSig->verify($objKey)) {
                $certValid = true;
            }
            $firma_inicio = date("Y-m-d H:i:s", strtotime($validFrom));
            $firma_vencimiento = date("Y-m-d H:i:s", strtotime($validTo));
            $firma_vigenteDesde = floor((strtotime(date('d-m-Y H:i:s')) - strtotime($firma_inicio)) / (60 * 60 * 24));
            $firma_venceEn = floor((strtotime($firma_vencimiento) - strtotime($this->fecha)) / (60 * 60 * 24));
            if ($firma_venceEn < 0 || $firma_vigenteDesde < 0) {
                throw new Exception(self::ERROR_FIRMA_FUERA_DE_FECHA);
            }
        } catch (Exception $e) {
            $certValid = false;
            $this->agregarError($e);
        }

        if ($this->cantidadErrores() > 0) {
            $certValid = false;
        }
        return $certValid;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                             GUARDAR
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function guardarPeticion_entrada($tiempoinicial, $tipo) {
        /**
         * Este metodo almacena los datos recibidos TAL CUAL son recibidos. Sin verificiación alguna.
         * Guarda los datos de la petición original en la tabla peticion y el XML con sobre incluido en
         * la tabla xml sin parsear.
         * Los campos adicionales de la tabla xml serán cargados una vez comience el proceso de validación
         * del XML recibido.
         */
        
        // Ip activa
        $ip = '';
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            $ip = getenv("REMOTE_ADDR");
        }
        // Conexion
        $db = db::getInstance();

        // Comienzo transaccion
        if ($db->executeSql('SET AUTOCOMMIT=0') && $db->executeSql('START TRANSACTION')) {
            // Errores
            $error = false;
            $error1 = "";

            $this->_tiempoRecepcion = (_tiempo() - $tiempoinicial);

            // Agrego peticion
            $sql1 = "INSERT INTO `" . DB_NAME . "`.peticion"
                    . "(peticion_id, fechaHora, ip, remitente, messageSize, tiempoRecepcion, tipo) VALUES ("
                    . "0,"
                    . "NOW(),"
                    . "'" . $ip . "',"
                    . "'" . $this->_remitente . "',"
                    . "'" . $this->_sizeXML . "',"
                    . "'" . $this->_tiempoRecepcion . "',"
                    . "'$tipo'"
                    . ")";
            if ($db->executeSql($sql1)) {
                $this->_peticion_id = $db->last_insert_id();
                $sql2 = "INSERT INTO `" . DB_NAME . "`.xml"
                    ."(xml_id, peticion_id, hash, documento, intercambio, fechaDoc, tipoEnvio, urlDestino) VALUES ("
                    ."0,"
                    ."'". $this->_peticion_id ."',"
                    ."'". $this->_hash ."',"
                    ."'','','0000-00-00','','')";
                if (!$db->executeSql($sql2)) {
                    $error2 = mysql_error();
                    $this->_peticion_id = -1;
                    $error = true;
                    $this->agregarError('ERROR-CIVDB/PETXML01: ' . self::ERROR_INTERNO_SQL . ' / ' . $error2);
                }
            } else {
                $error1 = mysql_error();
                $this->_peticion_id = -1;
                $error = true;
                $this->agregarError('ERROR-CIVDB/PET01: ' . self::ERROR_INTERNO_SQL . ' / ' . $error1);
            }
            if (!$error) {
                 $db->executeSql('COMMIT');
            } else {
                 $db->executeSql('ROLLBACK');
            }
        } else {
            // IMPOSIBLE INICIAR TRANSACCION A NIVEL DE BD! Se GENERA ERROR
            $this->agregarError('ERROR-DB_TF01: '.self::ERROR_INTERNO_SQL);
        }
        return $this->_tiempoRecepcion;
    }

    private function guardarDocumento($mInicio, $tiempoinicial, $tipo) {
        // Conexion
        $db = db::getInstance();

        // Comienzo transaccion
        if ($db->executeSql('SET AUTOCOMMIT=0') && $db->executeSql('START TRANSACTION')) {
            // Errores
            $error = false;

            $mFin = date("Y-m-d H:i:s");
            $tiempofinal = _tiempo();
            $tiempoParcial = ($tiempofinal - $mInicio);
            $tiempoTotal = ($tiempofinal - $tiempoinicial);

            // Guardo wsAduana de Aduanas en tabla correspondiente
            $error3 = "";
            $sql3 = "INSERT INTO `" . DB_NAME . "`.envio VALUES "
                    . " (0,'" . $this->_xml_id . "','" . $this->_fechaHoraEnvioAduana . "','" . $this->_tiempoRespuestaAduana . "','" . (is_array($this->_respuestaAduana) ? array2xml($this->_respuestaAduana) : base64_encode($this->_respuestaAduana)) . "','$tipo')";
            if (!$db->executeSql($sql3)) {
                $error3 = mysql_error();
                $error = true;
                $this->agregarError(self::ERROR_INTERNO_SQL . ' (CIVDB:ENV)' . $error3);
            }


            // Finalizo transaccion
            if (!$error) {
                $db->executeSql('COMMIT');
            } else {
                $db->executeSql('ROLLBACK');
            }
        } else {
//            $this->agregarError(self::ERROR_INTERNO_SQL . ' (STF:01)');
        }
        // TMP SQL sentence
//        file_put_contents('/tmp/vanADAU_insert.sql', $sql1."\nError: $error1\n\n".$sql2."\nError: $error2\n\n".$sql3."\nError: $error3\n");
    }

    public function getErrores() {
        $result = array();
        foreach ($this->_errores as $value) {
            $aux = explode('|', $value);
            $result[$aux[0]] = $aux[1];
        }
        return $result;
    }

    public function getNro() {
          return 0;
    }

}
