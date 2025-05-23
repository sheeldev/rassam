<?php
/* $requestUri = $_SERVER['REQUEST_URI'];
$parts = explode('/', $requestUri);
$firstPart = $parts[1];
define('LOCATION', $firstPart); */
define('SKIP_SESSION', false);
define('MCRYPT_MODE_CBC', 'CBC');
define('REFERRER', 'SHEEL');
define('MCRYPT_RIJNDAEL_128', 'rijndael-128');
define('IPADDRESS', getenv("REMOTE_ADDR"));
define('TIMESTAMPNOW', time());
define('DATETIME24H', date('Y-m-d H:i:s'));
define('TIMENOW', date('H:i:s'));

$date = date('Y-m-d H:i:s');
$invoiceduedate = date('Y-m-d H:i:s', strtotime($date . ' + 15 days'));
$yesterday = date('Y-m-d', strtotime($date . ' -1 days'));
define('DATEINVOICEDUE', $invoiceduedate);
define('DATEYESTERDAY', $yesterday);
define('DATETODAY', date('Y-m-d'));
define('MYSQL_QUERYCACHE', false);
define('PROTOCOL_REQUEST', $_SERVER['REQUEST_SCHEME']);
define('USERAGENT', $_SERVER['HTTP_USER_AGENT']);
define('VERSION', '1.0.0');
define('SVNVERSION', '1');
define('MYSQL_VERSION', '8.0.33-0ubuntu0.20.04.2');
define('MYSQL_TYPE', 'MyISAM');
define('JQUERYVERSION', '1.8.3');
define('HTTP_SERVER_ADMIN', HTTP_SERVER . 'admin/');
define('HTTPS_SERVER_ADMIN', HTTPS_SERVER . 'admin/');
define('AJAXURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? HTTPS_SERVER : HTTP_SERVER) . 'ajax');
define('DIR_TEMPLATES', __DIR__ . '/view/client/');
define('DIR_TEMPLATES_ADMIN', __DIR__ . '/view/admin/');
define('DIR_GEOIP', __DIR__ . '/assets/geoip/');
define('DIR_ATTACHMENTS', __DIR__ . '/uploads/attachments/');

define('DIR_CSS', __DIR__ . '/assets/css/');
define('DIR_FONTS', __DIR__ . '/assets/fonts/');
define('DIR_OTHER', __DIR__ . '/assets/other/');
define('DIR_CRON', __DIR__ . '/scheduler/');

define('DIR_FUNCTIONS', __DIR__ . '/');
define('DIR_VIEWS', __DIR__ . '/view/');
define('DIR_CLASSES', __DIR__ . '/model/');
define('DIR_TMP', __DIR__ . '/uploads/cache/');
define('DIR_TMP_JS', __DIR__ . '/uploads/cache/js/');
define('DIR_TMP_CSS', __DIR__ . '/uploads/cache/css/');
define('DIR_TMP_XLSX', __DIR__ . '/uploads/cache/xlsx/');
define('DIR_TMP_MODEL', __DIR__ . '/uploads/cache/staffmodels/');
define('HTTP_ATTACHMENTS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . 'application/uploads/attachments/');
define('HTTP_TMP', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . 'application/uploads/cache/');
define('HTTP_TMP_CSS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . 'application/uploads/cache/css/');
define('DIR_XML', __DIR__ . '/assets/xml/');
define('PAGEURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
define('SCRIPT_URI', '/' . str_replace(HTTPS_SERVER, "", PAGEURL));

require_once(__DIR__ . '/model/class.sheel.inc.php');
$sheel = new sheel($sheel);
require_once(__DIR__ . '/model/class.timer.inc.php');
$sheel->timer = new timer();
require_once(__DIR__ . '/model/class.crypt.inc.php');
$sheel->crypt = new crypt($sheel);
require_once(__DIR__ . '/model/autoload.php');
require_once(__DIR__ . '/model/class.cache.inc.php');
require_once(__DIR__ . '/model/class.xml.inc.php');
require_once(__DIR__ . '/model/class.coredatabase.inc.php');
require_once(__DIR__ . '/model/class.database.inc.php');
require_once(__DIR__ . '/model/class.security.inc.php');
require_once(__DIR__ . '/model/class.common.inc.php');

$sheel->sheel_database = new sheel_database($sheel);
$sheel->db = new database($sheel);
$sheel->cache = new cache($sheel);
$sheel->cachecore = new cache($sheel);
require_once(__DIR__ . '/model/class.configuration.inc.php');
$sheel->configuration = new configuration($sheel);

require_once(__DIR__ . '/model/class.sessions.inc.php');
ini_set("session.name", 's');
set_cookie('history', '1');
// token start
$token = '';
$characters = '0123456789abcdef';
$charactersLength = strlen($characters);
for ($i = 0; $i < 32; ++$i) {
    $token .= $characters[rand(0, $charactersLength - 1)];
}
set_cookie('token', $token);
define('TOKEN', $token);
// token end
if ($match['name'] != 'api') {
    $sheel->sessions->start();
}
else {
    
}
require_once(__DIR__ . '/model/class.language.inc.php');
$sheel->language = new language($sheel);
require_once(__DIR__ . '/model/class.styles.inc.php');
$sheel->styles = new styles($sheel);
require_once(__DIR__ . '/model/class.access.inc.php');
$sheel->access = new access($sheel);
$sheel->language->init_phrases();
//echo "<script>console.log('Console: " . $_COOKIE['s'].': '.$sheel->sessions->seconds_until_expiry($_COOKIE['s'])['secondsleft']."' );</script>"; 
$sheel->GPC['returnurl'] = $_SERVER['HTTP_REFERER'];
?>