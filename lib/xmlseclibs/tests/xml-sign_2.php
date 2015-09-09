<?php
require(dirname(__FILE__) . '/../xmlseclibs.php');

if (file_exists(dirname(__FILE__) . '/sign-basic-test_2.xml')) {
    unlink(dirname(__FILE__) . '/sign-basic-test_2.xml');
}

$doc = new DOMDocument();
$doc->load(dirname(__FILE__) . '/basic-doc.xml');

$objDSig = new XMLSecurityDSig();

$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

$objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'));

$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
/* load private key */
$objKey->loadKey(dirname(__FILE__) . '/test-rade.key', TRUE);

/* if key has Passphrase, set it using $objKey->passphrase = <passphrase> " */


$objDSig->sign($objKey);

/* Add associated public key */
$objDSig->add509Cert(file_get_contents( dirname(__FILE__) . '/test-rade.crt'));

$objDSig->appendSignature($doc->documentElement);
$doc->save(dirname(__FILE__) . '/sign-basic-test_2.xml');

//$sign_output = file_get_contents(dirname(__FILE__) . '/sign-basic-test.xml');
//$sign_output_def = file_get_contents(dirname(__FILE__) . '/sign-basic-test.res');
//if ($sign_output != $sign_output_def) {
//	echo "NOT THE SAME";
$sign_output = file_get_contents(dirname(__FILE__) . '/sign-basic-test_2.xml');
print $sign_output;
?>
