<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$TESTMODE = true;

$file = '';

if (!empty($_GET['file'])) {
    $file= trim($_GET['file']);
    play($file);
}

function play($file) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, getKodiUrl());
    curl_setopt($c, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_POSTFIELDS, '{ "id": 1, "jsonrpc": "2.0", "method": "Player.Open", "params": {"item": { "file": "' . $file . '" } } }');
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($c);
    curl_close($c);
}

function getKodiUrl() {
    global $TESTMODE;
    if ($TESTMODE) {
        return "http://${_SERVER['REMOTE_ADDR']}:8080/jsonrpc";
    }
    else {
        return 'http://kodi:8080/jsonrpc';
    }
}
?>
<html>
<body>
<h1>Play in Kodi</h1>
<h2><?php echo($file); ?></h2>
</body>
</html>
