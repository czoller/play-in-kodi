<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$TESTMODE = true;

if ($TESTMODE) {
    $URL = "http://${_SERVER['REMOTE_ADDR']}:8080/jsonrpc";
}
else {
    $URL = 'http://kodi:8080/jsonrpc';
}

$c = curl_init();
curl_setopt($c, CURLOPT_URL, $URL);
curl_setopt($c, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json'
));
curl_setopt($c, CURLOPT_POST, 1);
curl_setopt($c, CURLOPT_POSTFIELDS, '{ "id": 1, "jsonrpc": "2.0", "method": "Player.Open", "params": {"item": { "file": "https://mediandr-a.akamaihd.net/progressive_geo/2020/1027/TV-20201027-1316-3400.hd.mp4" } } }');
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($c);
curl_close($c);
?>
<html>
<body>
<h1>Play in Kodi</h1>
<h2><?php echo($URL); ?></h2>
</body>
</html>
