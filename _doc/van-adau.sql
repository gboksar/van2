-- MySQL dump 10.13  Distrib 5.5.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: control
-- ------------------------------------------------------
-- Server version	5.5.41-0ubuntu0.14.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `controlVAN`;

--
-- Table structure for table `dbs`
--
DROP TABLE IF EXISTS `controlVAN`.`dbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `controlVAN`.`dbs`(
  `db_id` varchar(30) NOT NULL,
  `pet_starts` int(11) NOT NULL DEFAULT '1',
  `pet_ends` int(11) NOT NULL DEFAULT '2147483647',
  `xml_starts` int(11) NOT NULL DEFAULT '1',
  `xml_ends` int(11) NOT NULL DEFAULT '2147483647',
  `res_starts` int(11) NOT NULL DEFAULT '1',
  `res_ends` int(11) NOT NULL DEFAULT '2147483647',
  `env_starts` int(11) NOT NULL DEFAULT '1',
  `env_ends` int(11) NOT NULL DEFAULT '2147483647',
  PRIMARY KEY (`db_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbs`
--

LOCK TABLES `controlVAN`.`dbs` WRITE;
/*!40000 ALTER TABLE `controlVAN`.`dbs` DISABLE KEYS */;
INSERT INTO `controlVAN`.`dbs` VALUES ('van_db',1,2147483647,1,2147483647,1,2147483647,1,2147483647);
/*!40000 ALTER TABLE `controlVAN`.`dbs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-30  9:53:17





-- MySQL dump 10.13  Distrib 5.5.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: van-adau
-- ------------------------------------------------------
-- Server version	5.5.41-0ubuntu0.14.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `van_db`;

--
-- Table structure for table `envio`
--

DROP TABLE IF EXISTS `van_db`.`envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `van_db`.`envio` (
  `envio_id` int(11) NOT NULL AUTO_INCREMENT,
  `xml_id` int(11) NOT NULL,
  `fechaHoraEnvio` datetime NOT NULL,
  `tiempo` double NOT NULL,
  `respuestaAduana` longtext NOT NULL,
  `tipo` varchar(1) NOT NULL DEFAULT 'I' COMMENT 'I-Inicial, C-Consulta',
  PRIMARY KEY (`envio_id`),
  KEY `envioFecha` (`fechaHoraEnvio`),
  KEY `envioXML` (`xml_id`) USING BTREE,
  KEY `envioTipo` (`fechaHoraEnvio`,`tipo`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `envio`
--

LOCK TABLES `van_db`.`envio` WRITE;
/*!40000 ALTER TABLE `van_db`.`envio` DISABLE KEYS */;
/*!40000 ALTER TABLE `van_db`.`envio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peticion`
--

DROP TABLE IF EXISTS `van_db`.`peticion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `van_db`.`peticion` (
  `peticion_id` int(11) NOT NULL AUTO_INCREMENT,
  `fechaHora` datetime NOT NULL,
  `ip` varchar(30) NOT NULL,
  `tiempo` double NOT NULL DEFAULT '0',
  `tipo` varchar(1) NOT NULL DEFAULT 'I' COMMENT 'V-Valida, I-Inavlida,C-Consulta,D-Despacho',
  PRIMARY KEY (`peticion_id`),
  KEY `petIP` (`ip`,`fechaHora`),
  KEY `petFecha` (`fechaHora`),
  KEY `petIPTipo` (`tipo`,`fechaHora`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peticion`
--

LOCK TABLES `van_db`.`peticion` WRITE;
/*!40000 ALTER TABLE `van_db`.`peticion` DISABLE KEYS */;
/*!40000 ALTER TABLE `van_db`.`peticion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuesta`
--

DROP TABLE IF EXISTS `van_db`.`respuesta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `van_db`.`respuesta` (
  `respueta_id` int(11) NOT NULL AUTO_INCREMENT,
  `xml_id` int(11) NOT NULL,
  `xml` longtext NOT NULL,
  `fechaHora` datetime NOT NULL,
  `tiempo` double NOT NULL,
  PRIMARY KEY (`respueta_id`) USING BTREE,
  KEY `respXML` (`xml_id`),
  KEY `respFecha` (`fechaHora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta`
--

LOCK TABLES `van_db`.`respuesta` WRITE;
/*!40000 ALTER TABLE `van_db`.`respuesta` DISABLE KEYS */;
/*!40000 ALTER TABLE `van_db`.`respuesta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `xml`
--

DROP TABLE IF EXISTS `van_db`.`xml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `van_db`.`xml` (
  `xml_id` int(11) NOT NULL AUTO_INCREMENT,
  `peticion_id` int(11) NOT NULL,
  `xml` longtext,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xml`
--

LOCK TABLES `van_db`.`xml` WRITE;
/*!40000 ALTER TABLE `van_db`.`xml` DISABLE KEYS */;
/*!40000 ALTER TABLE `van_db`.`xml` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-30  9:53:30
