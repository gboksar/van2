<?php
include_once '../app.params.php';
require_once("../lib/xml2array.php");

if ($_REQUEST['metodo'] == 'Directo') {
    require_once('../lib/SoapClientAuth.php');

//Preparo variables
    $server = TEST_URL;
    $protocol = TEST_PROTOCOLO;
    $user = TEST_USUARIO;
    $pass = TEST_CLAVE;

    if (isset($_REQUEST['host'])) {
        $user = trim($_REQUEST["user"]);
        $pass = trim($_REQUEST["pass"]);
        if (trim($_REQUEST['host']) == 'PRODUCCION') {
            $server = PRODUCCION_URL;
            $protocol = PRODUCCION_PROTOCOLO;
        }
    }
//$wSvc = trim($_REQUEST["ws"]);
    $timeout = (isset($_REQUEST["timeout"]) ? intval(trim($_REQUEST["timeout"])) : TEST_TIMEOUT);

    $WS_URL = "$protocol://$user:$pass@$server";

// leo XML desde archivo
    $fileContent = file_get_contents($_FILES['docXML']['tmp_name']);
    if ($fileContent === false)
        die("ERROR! Algo salio mal durante el upload del XML...");

    $fileXML = html_entity_decode($fileContent);
    $Soap = xml2array($fileXML);

// Selecciono WS destino según XML
    if (isset($Soap['DAE-ADAU'])) {
        $WS_URL .= trim($Soap['DAE-ADAU']['Url']) . "?WSDL";

        // Quito cabezales de envio y me quedo con contenido (tag DAE)
        $xml = base64_decode($Soap['DAE-ADAU']['Envio']);

        // Invoco WS usando autenticación!!!
        $param = array('In' => $xml);
        try {
            $ws = new SoapClientAuth($WS_URL, array(
                'login' => $user,
                'password' => $pass
            ));
            $ws->setTimeout($timeout);
            $respuesta = $ws->Execute($param);
            $salida = xml2array($respuesta->Out);
        } catch (SoapFault $e) {
            if ($e['faultcode'] == "WSDL") {
                print "<p><b>WS Error:</b> Imposible leer WSDL desde URL destino! Posible problemas de permisos...<br/><b>URL:</b> $WS_URL</p><hr/>";
            }
        } catch (Exception $e) {
            print "<p><b>General Error:</b>$e</p><hr/>";
        }
    } else {
        if (isset($Soap['SOAP-ENV:Envelope']['SOAP-ENV:Body']['m:despacho']['m:xml']['DAE-ADAU'])) {
            print "sobran cabezales...";
            print_r($Soap['SOAP-ENV:Envelope']['SOAP-ENV:Body']['m:despacho']['m:xml']['DAE-ADAU']);
        } else {
            print "<p><b>XML Error:</b> El XML recibido no tiene la forma adecuada...</p><hr/>";
            print_r($Soap);
            print "<hr/>";
        }
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html>
        <body>
    <?php if (is_array($salida)) { ?>
                <h2>Respuesta ADUANAS:</h2>
                <ul>
                    <li><b>Servicio:</b> <?= $WS_URL ?></li>
                    <?php
                    print_r($salida);
                    foreach ($salida['DAERespuesta']['Respuestas'] as $resp) {
                        if (!isset($resp['Tipo'])) {
                            foreach ($resp as $respu) {
                                ?>
                                <li>--------------------------------</li>
                                <li><b>Tipo/Codigo:</b> <?= $respu['Tipo'] ?> / <?= $respu['Codigo'] ?></li>
                                <li><b>Descrip.:</b> <?= (is_array($respu['Descripcion']) ? implode("<br/>", $respu['Descripcion']) : $respu['Descripcion']) ?></li>
                                <li><b>Ayuda:</b> <?= $respu['Ayuda'] ?></li>
                <?php
                }
            } else {
                $respu = $salida['DAERespuesta']['Respuestas']['Respuesta'];
                ?>
                            <li><b>Tipo/Codigo:</b> <?= $respu['Tipo'] ?> / <?= $respu['Codigo'] ?></li>
                            <li><b>Descrip.:</b> <?= (is_array($respu['Descripcion']) ? implode("<br/>", $respu['Descripcion']) : $respu['Descripcion']) ?></li>
                            <li><b>Ayuda:</b> <?= $respu['Ayuda'] ?></li>
            <?php }
        }
        ?>
                </ul>
                <hr/>
                <a href="/van/tests">Volver</a>
                <hr/>
                <h2>XML ENVIADO COMO PARAMETRO al WS "<?= $wSvc ?>"</h2>
                <data>
        <?= htmlentities($xml) ?>
                </data>
                <hr/>
    <?php } ?>
            <a href="/van/tests">Volver</a>
        </body>
    </html>
<?php
} else {
    require_once('../lib/nusoap-0.9.5/lib/nusoap.php');

    // leo XML desde archivo
    $fileContent = file_get_contents($_FILES['docXML']['tmp_name']);
    if ($fileContent === false)
        die("ERROR! Algo salio mal durante el upload del XML...");

    $Soap = xml2array(html_entity_decode($fileContent));
    $Soap = $Soap['SOAP-ENV:Envelope']['SOAP-ENV:Body']['m:despacho']['m:xml']['DAE-ADAU'];

    $client = new nusoap_client("http://192.168.1.31/van/index.php?wsdl", true);
    $error  = $client->getError();

    if ($error) {
        echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
    }

    $result = $client->call("despacho", array("xml"=>$Soap));

    if ($client->fault) {
        echo "<h2>Fault</h2><pre>";
        print_r($result);
        echo "</pre>";
    } else {
        $error = $client->getError();
        if ($error) {
            echo "<h2>Error</h2><pre>" . $error . "</pre>";
        } else {
            echo "<h2>Main</h2>";
            echo $result;
        }
    }

    // show soap request and response
    echo "<h2>Request</h2>";
    echo "<pre>" . htmlspecialchars($client->request, ENT_QUOTES) . "</pre>";
    echo "<h2>Response</h2>";
    echo "<pre>" . htmlspecialchars($client->response, ENT_QUOTES) . "</pre>";
}