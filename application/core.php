<?php
define('SKIP_SESSION', false);
define('MCRYPT_MODE_CBC', 'CBC');
define('REFERRER', 'SHEEL');
define('MCRYPT_RIJNDAEL_128', 'rijndael-128');
define('IPADDRESS', getenv("REMOTE_ADDR"));
define('TIMESTAMPNOW', time());
define('DATETIME24H', date('Y-m-d H:i:s'));
define('TIMENOW', date('H:i:s'));

$date = date('Y-m-d H:i:s');
$invoiceduedate = date('Y-m-d H:i:s', strtotime($date. ' + 15 days'));
$yesterday = date('Y-m-d', strtotime($date. ' -1 days'));
define('DATEINVOICEDUE', $invoiceduedate);
define('DATEYESTERDAY', $yesterday);
define('DATETODAY', date('Y-m-d'));
define('MYSQL_QUERYCACHE', false);
define('PROTOCOL_REQUEST', $_SERVER['REQUEST_SCHEME']);
define('USERAGENT', $_SERVER['HTTP_USER_AGENT']);
define('VERSION', '1.0.0');
define('SVNVERSION', '0');
define('JQUERYVERSION', '1.8.3');
define('HTTP_SERVER_ADMIN', HTTP_SERVER.'admin/');
define('HTTPS_SERVER_ADMIN', HTTPS_SERVER.'admin/');
define('AJAXURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? HTTPS_SERVER : HTTP_SERVER) .'ajax');
define('DIR_TEMPLATES', __DIR__.'/view/client/');
define('DIR_TEMPLATES_ADMIN', __DIR__.'/view/admin/');
define('DIR_GEOIP', __DIR__.'/assets/geoip/');
define('DIR_ATTACHMENTS', __DIR__.'/uploads/attachments/');

define('DIR_CSS', __DIR__.'/assets/css/');
define('DIR_FONTS', __DIR__.'/assets/fonts/');
define('DIR_OTHER', __DIR__.'/assets/other/');


define('DIR_FUNCTIONS', __DIR__.'/');
define('DIR_VIEWS', __DIR__.'/view/');
define('DIR_CLASSES', __DIR__.'/model/');
define('DIR_TMP', __DIR__.'/uploads/cache/');
define('DIR_TMP_JS', __DIR__.'/uploads/cache/js/');
define('DIR_TMP_CSS', __DIR__.'/uploads/cache/css/');
define('HTTP_TMP', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/".'application/uploads/cache/');
define('HTTP_TMP_CSS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/".'application/uploads/cache/css/');
define('DIR_XML', __DIR__.'/assets/xml/');
define('PAGEURL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

require_once(__DIR__ . '/model/class.sheel.inc.php');
$sheel = new sheel($sheel);
require_once (__DIR__ . '/model/class.timer.inc.php');
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

require_once(__DIR__ . '/model/class.template.inc.php');
$sheel->template = new template($sheel);
require_once(__DIR__ . '/model/class.template_debug.inc.php');
$sheel->template_debug = new template_debug($sheel);

require_once(__DIR__ . '/model/class.configuration.inc.php');
$sheel->configuration = new configuration($sheel);

require_once(__DIR__ . '/model/class.sessions.inc.php');
$sheel->sessions = new sessions($sheel);

require_once(__DIR__ . '/model/class.language.inc.php');
$sheel->language = new language($sheel);

require_once(__DIR__ . '/model/class.styles.inc.php');
$sheel->styles = new styles($sheel);


if (!$sheel->sessions->session_get_key(session_id())) {
    $sheel->sessions->generateToken();	
}

define('TOKEN', $_SESSION['token']);
$sheel->sessions->session_write(session_id(),'sheeldata|'.serialize($_SESSION['sheeldata']));
?>