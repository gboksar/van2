<?php

/**
 * Conexion con la base de datos
 *
 * @filesource
 * @author Pablo Erartes (pabloeuy@gmail.com) / GBoksar/Perro (gustavo@boksar.info)
 * @package van
 * @subpackage model
 * @version 1.44
 *
 */

include_once 'databaseStructure.php';

class db {

    private $_conexion;
    private $_resource;
    private static $_singleton;

    public static function getInstance() {
        if (is_null(self::$_singleton)) {
            self::$_singleton = new db();
        }
        return self::$_singleton;
    }

    private function __construct() {
        $this->_conexion = @mysql_connect(DB_HOST, DB_USER, DB_PASS);
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "';";
        if (is_null($this->loadObject($sql))) {
            $this->_createDatabase(DB_NAME);
        }
        $this->_resource = null;
    }

    public function selectDB($doc_id) {
        if ($doc_id == 0) {
            mysql_select_db(DB_NAME, $this->_conexion);
        } else {
            $db = $this->loadObject("SELECT db_id FROM `" . CONTROL_DB . "`.dbs WHERE " . $doc_id . " BETWEEN xml_starts AND xml_ends");
            mysql_select_db($db->db_id, $this->_conexion);
        }
    }

    public function getResource($sql) {
        if (!($this->_resource = mysql_query($sql, $this->_conexion))) {
            return null;
        }
        return $this->_resource;
    }

    public function executeSql($sql) {
        if (!($this->_resource = mysql_query($sql, $this->_conexion))) {
            return false;
        }
        return true;
    }

    public function loadObjectList($sql) {
        if (!($cur = $this->getResource($sql))) {
            return null;
        }
        $array = array();
        while ($row = @mysql_fetch_object($cur)) {
            $array[] = $row;
        }
        return $array;
    }

    public function freeResults() {
        @mysql_free_result($this->_resource);
        return true;
    }

    public function loadObject($sql) {
        if ($cur = $this->getResource($sql)) {
            if ($object = mysql_fetch_object($cur)) {
                @mysql_free_result($cur);
                return $object;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }

    public function last_insert_id() {
        $id = 0;
        foreach ($this->loadObjectList("SELECT LAST_INSERT_ID() AS ultimo") as $data) {
            $id = $data->ultimo;
        }
        return $id;
    }

    private function _createDatabase($dbname) {
        $lastDB = $this->loadObject("SELECT TABLE_SCHEMA FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE '" . DATABASE_NAME . "%' ORDER BY TABLE_SCHEMA DESC LIMIT 1");
        $result = $this->loadObjectList("SELECT TABLE_NAME,AUTO_INCREMENT FROM information_schema.TABLES where TABLE_SCHEMA = '" . $lastDB->TABLE_SCHEMA . "' order by TABLE_SCHEMA desc");
        $this->executeSql("CREATE DATABASE IF NOT EXISTS $dbname");
        mysql_select_db($dbname, $this->_conexion);
        $this->executeSql(CREATE_ENV);
        $this->executeSql(CREATE_PETS);
        $this->executeSql(CREATE_RESP);
        $this->executeSql(CREATE_XML);
        $update = array();
        $insert = array();
        foreach ($result as $line) {
            $sql = "ALTER TABLE " . $line->TABLE_NAME . " AUTO_INCREMENT = " . $line->AUTO_INCREMENT;
            $this->executeSql($sql);
            $update[] = substr($line->TABLE_NAME, 0, 3) . "_ends=" . ($line->AUTO_INCREMENT - 1);
            $insert[] = $line->AUTO_INCREMENT . ', 99999999999';
        }
        $sql = "UPDATE `".CONTROL_DB."`.dbs SET " . implode(", ", $update) . " WHERE db_id = '" . $lastDB->TABLE_SCHEMA . "'";
        $this->executeSql($sql);
        $sql = "INSERT INTO `".CONTROL_DB."`.dbs VALUES('" . $dbname . "', " . implode(', ', $insert) . ")";
        $this->executeSql($sql);
    }

}

?>
