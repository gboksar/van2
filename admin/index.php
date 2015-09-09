<?php
$fileName = 'van.ini';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function write_ini_file($file, array $options) {
    $tmp = '';
    foreach ($options as $section => $values) {
        $tmp .= "[$section]\n";
        foreach ($values as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $tmp .= "{$key}[$k] = \"$v\"\n";
                }
            } else
                $tmp .= "$key = \"$val\"\n";
        }
        $tmp .= "\n";
    }
    file_put_contents($file, $tmp);
    unset($tmp);
}

$msg = "";
$params = array();
if (isset($_REQUEST['saveData'])) {
    $params['TEST']['PROTOCOLO'] = $_REQUEST['TEST_PROTOCOLO'];
    $params['TEST']['URL'] = $_REQUEST['TEST_URL'];
    $params['TEST']['USUARIO'] = $_REQUEST['TEST_USUARIO'];
    $params['TEST']['CLAVE'] = $_REQUEST['TEST_CLAVE'];
    $params['TEST']['TIMEOUT'] = $_REQUEST['TEST_TIMEOUT'];
    $params['PRODUCCION']['PROTOCOLO'] = $_REQUEST['PRODUCCION_PROTOCOLO'];
    $params['PRODUCCION']['URL'] = $_REQUEST['PRODUCCION_URL'];
    $params['PRODUCCION']['USUARIO'] = $_REQUEST['PRODUCCION_USUARIO'];
    $params['PRODUCCION']['CLAVE'] = $_REQUEST['PRODUCCION_CLAVE'];
    $params['PRODUCCION']['TIMEOUT'] = $_REQUEST['PRODUCCION_TIMEOUT'];
    $params['GENERAL']['ERRORES_REINTENTAR'] = implode(',', array_filter($_REQUEST['ERRORES_REINTENTAR']));
    $params['GENERAL']['MODO'] = $_REQUEST['MODO'];
    write_ini_file($fileName, $params);
    $fileContent = array();
    $fileContent[] = "<?php";
    foreach ($params as $s => $section) {
        foreach ($section as $key => $value) {
            $constName = $s . '_' . $key;
            if ($s == 'GENERAL') {
                $constName = $key;
            }
            $fileContent[] = "define('$constName','$value');";
        }
    }
    $wsURL = $_REQUEST['TEST_PROTOCOLO'] . '://' . $_REQUEST['TEST_USUARIO'] . ':' . trim($_REQUEST['TEST_CLAVE']) . '@' . trim($_REQUEST['TEST_URL']);
    if($_REQUEST['MODO']=='prod'){
        $wsURL = $_REQUEST['PRODUCCION_PROTOCOLO'] . '://' . $_REQUEST['PRODUCCION_USUARIO'] . ':' . trim($_REQUEST['PRODUCCION_CLAVE']) . '@' . trim($_REQUEST['PRODUCCION_URL']);
    }
    $fileContent[] = "define('WS_ADUANA_URL', '$wsURL');";
    $fileContent[] = "";
    file_put_contents('../app.params.php', implode("\n", $fileContent));
    $params['GENERAL']['ERRORES_REINTENTAR'] = array_filter($_REQUEST['ERRORES_REINTENTAR']);
} else {
    if (is_readable($fileName)) {
        $params = parse_ini_file($fileName, true);
        if(isset($params['GENERAL']['ERRORES_REINTENTAR'])){
            $params['GENERAL']['ERRORES_REINTENTAR'] = explode(',', $params['GENERAL']['ERRORES_REINTENTAR']);
        }else{
            $params['GENERAL']['ERRORES_REINTENTAR'] = array();
        }
    } else {
        $params['TEST']['PROTOCOLO'] = 'http';
        $params['TEST']['URL'] = '';
        $params['TEST']['USUARIO'] = '';
        $params['TEST']['CLAVE'] = '';
        $params['TEST']['TIMEOUT'] = 90;
        $params['PRODUCCION']['PROTOCOLO'] = 'http';
        $params['PRODUCCION']['URL'] = '';
        $params['PRODUCCION']['USUARIO'] = '';
        $params['PRODUCCION']['CLAVE'] = '';
        $params['PRODUCCION']['TIMEOUT'] = 90;
        $params['GENERAL']['ERRORES_REINTENTAR'] = array();
        $params['GENERAL']['MODO'] = 'prod';
    }
}
?>
<html>
    <head>
        <title>Panel de administraci&oacute;n de parametros VAN</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <link rel="stylesheet" href="styles.css" />
        <script type="text/javascript">
            $(document).ready(function() {
                var max_fields = 10; //maximum input boxes allowed
                var wrapper = $(".input_fields_wrap"); //Fields wrapper
                var add_button = $(".add_field_button"); //Add button ID

                var x = 1; //initlal text box count
                $(add_button).click(function(e) { //on add input button click
                    e.preventDefault();
                    if (x < max_fields) { //max input box allowed
                        x++; //text box increment
                        $(wrapper).append('<div><input type="text" name="ERRORES_REINTENTAR[]" class="codError"/><a href="#" class="remove_field">Eliminar</a></div>'); //add input box
                    }
                });

                $(wrapper).on("click", ".remove_field", function(e) { //user click on remove text
                    e.preventDefault();
                    $(this).parent('div').remove();
                    x--;
                })
            });
        </script>
    </head>
    <body>
    <center>
        <a href="http://www.adau.com.uy"><img src="/logo_adau.jpg" alt="ADAU - Asoc. de Despachantes de Aduana del Uruguay" title="Haga click aqu&iacute; si desea acceder al portal de la ADAU"/></a>
        <h1>VAN v2.0</h1>
        <h2>Panel para administraci&oacute;n de par&aacute;metros del sistema</h2>
        <form name="params" id="params" method="post">
            <table>
                <tr>
                    <td width="50%" align="center">
                        <fieldset name="test">
                            <h3>Valores para TEST</h3>
                            <label for="TEST_PROTOCOLO">URI:</label>
                            <select name="TEST_PROTOCOLO">
                                <option value="https" <?= ($params['TEST']['PROTOCOLO'] == 'https' ? 'selected="selected"' : '') ?>>https</option>
                                <option value="http" <?= ($params['TEST']['PROTOCOLO'] == 'http' ? 'selected="selected"' : '') ?>>http</option>
                            </select><span style="font-size: 1.5em; font-weight: bold;">&nbsp;://&nbsp;</span>
                            <input type="text" name="TEST_URL" id="TEST_URL" value="<?= $params['TEST']['URL'] ?>"/><br/>
                            <label for="TEST_USUARIO">Usuario:Clave</label>
                            <input type="text" name="TEST_USUARIO" id="TEST_USUARIO" value="<?= $params['TEST']['USUARIO'] ?>" style="width: 150px !important;"/>&nbsp;<b>:</b>&nbsp;<input type="text" name="TEST_CLAVE" id="TEST_CLAVE" value="<?= $params['TEST']['CLAVE'] ?>" style="width: 150px !important;"/><br/>
                            <label for="TEST_TIMEOUT">TimeOut para Aduana:</label>
                            <input type="number" name="TEST_TIMEOUT" id="TEST_TIMEOUT" value="<?= $params['TEST']['TIMEOUT'] ?>" style="text-align: right; width: 60px !important;"/>seg                   
                        </fieldset>
                    </td>
                    <td align="center">
                        <fieldset name="produccion">
                            <h3>Valores para PRODUCCION</h3>
                            <label for="PRODUCCION_PROTOCOLO">URI:</label>
                            <select name="PRODUCCION_PROTOCOLO">
                                <option value="https" <?= ($params['PRODUCCION']['PROTOCOLO'] == 'https' ? 'selected="selected"' : '') ?>>https</option>
                                <option value="http" <?= ($params['PRODUCCION']['PROTOCOLO'] == 'http' ? 'selected="selected"' : '') ?>>http</option>
                            </select><span style="font-size: 1.5em; font-weight: bold;">&nbsp;://&nbsp;</span>
                            <input type="text" name="PRODUCCION_URL" id="PRODUCCION_URL" value="<?= $params['PRODUCCION']['URL'] ?>"/><br/>
                            <label for="PRODUCCION_USUARIO">Usuario/Clave:</label>
                            <input type="text" name="PRODUCCION_USUARIO" id="PRODUCCION_USUARIO" value="<?= $params['PRODUCCION']['USUARIO'] ?>" style="width: 150px !important;"/>&nbsp;<b>:</b>&nbsp;<input type="text" name="PRODUCCION_CLAVE" id="PRODUCCION_CLAVE" value="<?= $params['PRODUCCION']['CLAVE'] ?>" style="width: 150px !important;"/><br/>
                            <label for="PRODUCCION_TIMEOUT">TimeOut para Aduana:</label>
                            <input type="number" name="PRODUCCION_TIMEOUT" id="PRODUCCION_TIMEOUT" value="<?= $params['PRODUCCION']['TIMEOUT'] ?>" style="text-align: right; width: 60px !important;"/>seg 
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <fieldset name="general">
                            <h3>Valores GENERALES</h3>
                            <label for="TEST_TIMEOUT">Modo de WS:</label>
                            <select name="MODO">
                                <option value="test" <?= ($params['GENERAL']['MODO'] == 'test' ? 'selected="selected"' : '') ?>>Test</option>
                                <option value="prod" <?= ($params['GENERAL']['MODO'] == 'prod' ? 'selected="selected"' : '') ?>>Producci&oacute;n</option>
                            </select><br/>
                            <label for="PRODUCCION_TIMEOUT">Errores de Aduana que requieren reintentar env&iacute;o:</label>
                            <img src="imgs/add.png" class="add_field_button" alt="Agregar C&oacute;digo de error" title="Click aqu&iacute; para agregar un nuevo c&oacute;digo de error"/><br/>
                            <div class="input_fields_wrap" id="columns">
                                <?php foreach($params['GENERAL']['ERRORES_REINTENTAR'] as $key=>$val){ ?>
                                <div><input type="text" name="ERRORES_REINTENTAR[]" value="<?= $val ?>" class="codError"/><a href="#" class="remove_field">Eliminar</a></div>
                                <?php } ?>
                            </div>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" name="saveData" id="saveData" value="Guardar datos"/>
                    </td>
                </tr>
            </table>
        </form>
    </center>                
</body>
</html>