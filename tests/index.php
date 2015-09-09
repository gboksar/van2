<?php include_once '../app.params.php'; ?>
<html>
    <head>
        <title>VANv2.0 - ADAU</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
        <style>
            body{font-family: Arial, sans-serif, Helvetica;}
            form{border: 1px solid grey; padding: 10px; width: 500px;}
            input, select {font-size: 1.2em; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0 0 4px rgba(0,0,0, .75); -moz-box-shadow: 0 0 4px rgba(0,0,0, .75); box-shadow: 0 0 4px rgba(0,0,0, .75);}
            input[type=file],input[type=text], select, input[type=number]{font-weight: bold; background-color: lightgray; border:1px solid darkslategrey; }
            input[type=file]:focus,input[type=text]:focus, select:focus, input[type=number]:focus{background-color: beige; border:1px solid goldenrod;}
            input[type=text]:focus, select:focus, input[type=number]:focus{background-color: beige; border:1px solid goldenrod;}
            input[type=submit]{ color: white; cursor:pointer; padding:5px 25px; text-decoration: none; font-weight: bold; background:#55f; border:1px solid #55a;}
            input[type=submit]:hover{background:#f55; border:1px solid #a55;}
            br {margin-bottom: 15px;} 
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript">
            var protTest = '<?= TEST_PROTOCOLO ?>';
            var urlTest = '<?= TEST_URL ?>';
            var userTest = '<?= TEST_USUARIO ?>';
            var passTest = '<?= TEST_CLAVE ?>';
            var toutTest = '<?= TEST_TIMEOUT ?>';
            var protProd = '<?= PRODUCCION_PROTOCOLO ?>';
            var urlProd = '<?= PRODUCCION_URL ?>';
            var userProd = '<?= PRODUCCION_USUARIO ?>';
            var passProd = '<?= PRODUCCION_CLAVE ?>';
            var toutProd = '<?= PRODUCCION_TIMEOUT ?>';
            $(document).ready(function() {
                $("#host").change(function(){
                    s=$(this).val();
                    if(s=='TEST') {
                        $("#protocolo").empty().html(protTest);
                        $("#url").empty().html(urlTest);
                        $("#auth").empty().html(userTest + ':' + passTest + '@');
                        $("#user").empty().html(userTest);
                        $("#pass").empty().html(passTest);
                        $("#timeout").empty().html(toutTest);
                        
                    }else{
                        $("#protocolo").empty().html(protProd);
                        $("#auth").empty().html(userProd + ':' + passProd + '@');
                        $("#url").empty().html(urlProd);
                        $("#user").empty().html(userProd);
                        $("#pass").empty().html(passProd);
                        $("#timeout").empty().html(toutProd);
                    }
                });
            });
        </script>
    </head>
    <body>
    <center>
        <a href="http://www.adau.com.uy"><img src="/logo_adau.jpg" alt="ADAU - Asoc. de Despachantes de Aduana del Uruguay" title="Haga click aqu&iacute; si desea acceder al portal de la ADAU"/></a>
        <h1>Bienvenidos al servicio VAN v2.0</h1>
        <hr style="margin-top: 25px; margin-bottom: 25px;"/>
        <Form action="cs2.php" method="POST" enctype="multipart/form-data" >
            Seleccione destino: <select name="host" id="host">
                <option value="TEST">Sitio de pruebas de Aduana</option>
                <option value="PRODUCCION">Sitio Oficial de Aduana</option>
            </select><br/>
            Usuario: <input type="text" id="user" name="user" value="<?= TEST_USUARIO ?>" style="width:150px;"/>
            Clave: <input type="text" id="pass" name="pass" value="<?= TEST_CLAVE ?>" style="width:150px;"/><br/>
            Time-Out: <input type="number" id="timeout" name="timeout" value="<?= TEST_TIMEOUT ?>" style="width:60px; text-align: right;"/> seg<br/>
            Max. File Size: <input type="number" name="MAX_FILE_SIZE" value="500000" style="width:100px; text-align: right;" /> bytes<br/>
            XML: <input name="docXML" type="file" /><br/>
            WS: <!--<select name="ws">
                <option value="aws_menstock.aspx">menstock</option>
                <option value="awsarchivadosobre.aspx">archivadosobre</option>
                <option value="aWSCertOrigen.aspx">CertOrigen</option>
                <option value="aws_mencor.aspx">mencor</option>
                <option value="aws_MensManifiesto.aspx">MensManifiesto</option>
                <option value="awsmensimp.aspx">mensimp</option>
                <option value="awspedidocanal.aspx">pedidocanal</option>
                <option value="awsRespuestaReliquidacion.aspx">RespuestaReliquidacion</option>
                <option value="awsteledes.aspx">teledes</option>
                <option value="awsTramitesDespachante.aspx">TramitesDespachante</option>
                <option value="awsviajes.aspx">viajes</option>
                <option value="aWSImagenYDocGral.aspx">Imagenes y Docs. en General</option>
                </select> --> Se toma del XML recibido seg&uacute;n documento.<br/>
            Metodo: <select name="metodo">
                <option value="Directo">Directo</option>
                <option value="WS">WS</option>
            </select><br/><br/>
            <p><b>Va contra:<br/><font color="green"><span id="protocolo"><?= TEST_PROTOCOLO ?></span>://<span id="auth"><?= TEST_USUARIO ?>:<?= TEST_CLAVE ?>@</span><span id="url"><?= TEST_URL ?></span></font></b></p>
            <Input type="submit" value="Probar">
        </Form>
    </center> 
</body>
</html>
