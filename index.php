<?php
// TODO 3Sat 
// TODO Werder.TV
// TODO DAZN
// TODO Startzeitmarke mitgeben
// TODO Chapter-Deeplinks in Means.TV-Plugin akzeptieren

/*************************** KONFIGURATION ***************************/

ini_set('display_errors', 1);
error_reporting(E_ALL);

$TESTMODE = !empty($_GET['test']);

if ($TESTMODE) {
    $KODIURL = "http://${_SERVER['REMOTE_ADDR']}:8080";
}
else {
    $KODIURL = 'http://kodi:8080';
}
$KODIAPI = "$KODIURL/jsonrpc";

$CONVERTERS = [];
$CONVERTERS['meanstv'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?means\.tv\/.+$/';

    public function convert($url) {
        return "plugin://plugin.video.meanstv/?show=video&id=$url";
    }
};
$CONVERTERS['youtube'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)(\w+)$/';

    public function convert($url) {
        return preg_replace(self::$REG_EX, 'plugin://plugin.video.youtube/play/?video_id=\3', $url);
    }
};
$CONVERTERS['ard'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?ardmediathek\.de\/.+$/';

    public function convert($url) {
        $file = file_get_contents($url);
        if (preg_match('/\"contentId\"\:(\d+)\,/', $file, $matches) == 1) {
            $documentId = $matches[1];
            return "plugin://plugin.video.ardmediathek_de/?mode=libArdPlay&documentId=$documentId";
        }
        return $url;
    }
};
$CONVERTERS['zdf'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?zdf\.de((\/.+)\.html)$/';

    public function convert($url) {
        $file = file_get_contents($url);
        if (preg_match('/\"contentUrl\"\:\ "(.+)\"\,/', $file, $matches) == 1) {
            $docUrl = $matches[1];
            $docUrl = str_replace('{playerId}', 'ngplayer_2_3', $docUrl);
            $docUrl = urlencode($docUrl);
            return "plugin://plugin.video.zdf_de_lite/?url=$docUrl&mode=libZdfPlay";
        }
        return $url;
    }
};
$CONVERTERS['arte'] = new class extends UrlConverter {
    public static $REG_EX = '/^https?\:\/\/(www\.)?arte\.tv\/.+$/';

    public function convert($url) {
        return "plugin://plugin.video.tyl0re.arte/?mode=playVideo&url=$url";
    }
};

/*************************** MAIN ************************************/

$file = null;
$result = null;
if (!empty($_GET['file'])) {
    $file= trim($_GET['file']);
    foreach ($CONVERTERS as $converter) {
        $file = $converter->convertOrPass($file);        
    }
    $result = play($file, $KODIAPI);
    if ($result == '{"id":1,"jsonrpc":"2.0","result":"OK"}') {
        header("Location: $KODIURL");
    }
}

/********************************************************************/

function play($file, $apiUrl) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $apiUrl);
    curl_setopt($c, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_POSTFIELDS, '{ "id": 1, "jsonrpc": "2.0", "method": "Player.Open", "params": {"item": { "file": "' . $file . '" } } }');
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($c);
    curl_close($c);
    return $result;
}

abstract class UrlConverter {
    
    public static $REG_EX = '/^.*$/';
    
    public function convertOrPass($url) {
        $class = get_class($this);
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
<h2><?php echo($result); ?></h2>
<form>
	<input name="file" type="url" style="width:100%" placeholder="https://"><br>
	<button type="submit">Play</button>
</form>
</body>
</html>
