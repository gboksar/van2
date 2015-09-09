<?php

/*
 * Document : .php
 * Created on : 24/11/2012, 08:05:45 AM
 * Author : GBoksar / gustavo@boksar.info
 * Package : transito
 * Subpackage : seguimiento
 * Version : 0.1a
 * License : http://opensource.org/licenses/gpl-license.php GNU Public license (GPLv2+)
 */

// PETICION RECIBIDA - ALMACENADA TAL CUAL
define('CREATE_PETS',"CREATE TABLE  `peticion` (
  `peticion_id` int(11) NOT NULL AUTO_INCREMENT,
  `fechaHora` datetime NOT NULL,
  `ip` varchar(30) NOT NULL,
  `tiempo` double NOT NULL DEFAULT '0',
  `tipo` varchar(1) NOT NULL DEFAULT 'I' COMMENT 'V-Valida, I-Inavlida,C-Consulta,D-Despacho',
  `tiempo` double NOT NULL DEFAULT 0,
  `xml` longtext,
  PRIMARY KEY (`peticion_id`),
  KEY `petIP` (`ip`,`fechaHora`),
  KEY `petFecha` (`fechaHora`),
  KEY `petIPTipo` (`tipo`,`fechaHora`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

// XML DATA - INFORMACION DEL XML POST VALIDACION
define('CREATE_XML',"CREATE TABLE  `xml` (
  `xml_id` int(11) NOT NULL AUTO_INCREMENT,
  `peticion_id` int(11) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `documento` varchar(50) NOT NULL,
  `intercambio` varchar(50) NOT NULL,
  `fechaDoc` datetime NOT NULL,
  `tipoEnvio` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'P-Prueba, N-Normal',
  `urlDestino` varchar(255) NOT NULL,
  PRIMARY KEY (`xml_id`) USING BTREE,
  KEY `xmlHash` (`hash`,`documento`),
  KEY `xmlFechaDoc` (`fechaDoc`),
  KEY `xmlTipo` (`tipoEnvio`,`peticion_id`),
  KEY `xmlPet` (`peticion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

// DATOS DE ENVIO A ADUANA DEL XML RECIBIDO Y RESPUESTA DE ADUANA EN CASO DE EXISTIR
define('CREATE_ENV',"CREATE TABLE  `envio` (
  `envio_id` int(11) NOT NULL AUTO_INCREMENT,
  `xml_id` int(11) NOT NULL,
  `fechaHoraEnvio` datetime NOT NULL,
  `tiempo` double NOT NULL,
  `respuestaAduana` longtext NOT NULL,
  `tipo` varchar(1) NOT NULL DEFAULT 'I' COMMENT 'I-Inicial, C-Consulta',
  PRIMARY KEY (`envio_id`),
  KEY `envioXML` (`xml_id`),
  KEY `envioFecha` (`fechaHoraEnvio`),
  KEY `envioTipo` (`fechaHoraEnvio`,`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

// RESPUESTA ENVIADA AL DESPACHANTE
define('CREATE_RESP',"CREATE TABLE  `respuesta` (
  `respuesta_id` int(11) NOT NULL AUTO_INCREMENT,
  `xml_id` int(11) NOT NULL,
  `xml` longtext NOT NULL,
  `fechaHora` datetime NOT NULL,
  `tiempo` double NOT NULL,
  PRIMARY KEY (`respuesta_id`),
  KEY `respXML` (`xml_id`),
  KEY `respFecha` (`fechaHora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

?>