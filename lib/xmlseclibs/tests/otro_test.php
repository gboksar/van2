<?php

require_once('soap-wsse.php');

define('PRIVATE_KEY', '/etc/rade/cert/rade.key');
define('CERT_FILE', '/etc/rade/cert/rade.crt');

class MySoapClient extends SoapClient
{
  function __doRequest($request, $location, $saction, $version)
  {
    $doc = new DOMDocument('1.0');
    $doc->loadXML($request);

    $objWSSE = new WSSESoap($doc);

    /* timestamp expires after five minutes */
    $objWSSE->addTimestamp(300);

    /* create key object, set passphrase and load key */
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
    $objKey->passphrase = 'My password.';
    $objKey->loadKey(PRIVATE_KEY, TRUE);

    /* sign message */
    $objWSSE->signSoapDoc($objKey);

    /* add certificate */
    $token = $objWSSE->addBinaryToken(file_get_contents(CERT_FILE));
    $objWSSE->attachTokentoSig($token);

    // this DOES print the header
    // echo $objWSSE->saveXML();

    return parent::__doRequest($objWSSE->saveXML(), $location, $saction, $version);
  }
}

// connection options
$options = array(
  'soap_version' => SOAP_1_1,
  'local_cert' => CERT_FILE,
  'connection_timeout' => 20,
  'cache_wsdl' => WSDL_CACHE_NONE,
  'exceptions' => true,
  'user_agent' => 'MySoapClient',
  'trace' => true,
);

try
{
  $client = new MySoapClient('http://some_ip/server.php?wsdl', $options);

  $message = '<my soap message />';

  $result = $client->test($message);
  print_r($result);
}
catch (Exception $e)
{
  var_dump($e);
}

// this does NOT print the header
echo $client->__getLastRequest();

?> 
