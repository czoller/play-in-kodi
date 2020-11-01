<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$TESTMODE = true;

$CONVERTERS = [];

$CONVERTERS['youtube'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)(\w+)$/';

    public function convert($url) {
        echo("MATCH");
        return preg_replace(self::$REG_EX, 'plugin://plugin.video.youtube/play/?video_id=\3', $url);
    }
};

if (!empty($_GET['file'])) {
    $file= trim($_GET['file']);
    foreach ($CONVERTERS as $converter) {
        $file = $converter->convertOrPass($file);        
    }
    var_dump($file);
    play($file);
}

/********* FUNCTIONS ***********/

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

abstract class UrlConverter {
    
    public static $REG_EX = '/^.*$/';
    
    public function convertOrPass($url) {
        $class = get_class($this);
        var_dump($class);
        var_dump($class::$REG_EX);
        if (preg_match($class::$REG_EX, $url)) {
            return $this->convert($url);
        }
        return $url;
    }
    
    abstract public function convert($url);
}

?>
<html>
<body>
<h1>Play in Kodi</h1>
<h2><?php echo($file); ?></h2>
</body>
</html>
