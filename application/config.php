<?php
error_reporting(0);
@ini_set('display_errors', false);
/**
* Application urls
*/
define('HTTP_SERVER', 'https://staging.sheel.online/');                # <<< full url to your sheel include end slash
define('HTTPS_SERVER', 'https://staging.sheel.online/');               # <<< full url to your secure sheel include end slash

/**
* Folder paths
*/
define('DIR_SERVER_ROOT', SITE_ROOT);            # <<< full path to sheel include end slash
define('DIR_SERVER_ROOT_IMAGES', SITE_ROOT);     # <<< should be same as DIR_SERVER_ROOT
define('SUB_FOLDER_ROOT', '/');           # <<< leave it / unless installed in a sub /folder/

/**
* Marketplace identifier id
*/
define('APP_ID', '657753');         # <<< unique app identifier for the app store
define('SITE_ID', '001');                 # <<< if this is app 2 on node 2 then enter 002

/**
* Application license key
*/
// define('LICENSEKEY', 'ygrMWJZmaxWINpm');

/**
* Master database information
*/
define('DB_DATABASE', 'amazon');                 # <<< full database name
define('DB_SERVER', 'localhost');          # <<< database server hostname (default localhost)
define('DB_SERVER_PORT', '3306');          # <<< database server port (default 3306)
define('DB_SERVER_USERNAME', 'sheel');          # <<< username with access to the database
define('DB_SERVER_PASSWORD', 'cA3W@ShL1');          # <<< password with access to the database
define('DB_PERSISTANT_MASTER', 1);         # <<< persistant database connections (default true)
define('DB_SERVER_TYPE', 'mysqli');        # <<< database server type (mysql or mysqli)
define('DB_PREFIX', 'sheel_');            # <<< database table prefix (default sheel_)
define('DB_CHARSET', 'utf8');              # <<< database character set (default utf-8)
define('DB_COLLATE', 'utf8_general_ci');   # <<< database collation (default utf-8)

/**
* Master cache information
*/
define('CACHE_ENGINE', 'none'); // none, filecache, apc, memcached
define('CACHE_EXPIRY', '300');
define('CACHE_PREFIX', DB_PREFIX);

define('DIR_APPLICATION_NAME', 'application');
define('DIR_ASSETS_NAME', 'assets');
define('DIR_IMAGES_NAME', 'images');
define('DIR_ADMIN_NAME', 'admin');
define('DIR_CRON_NAME', 'scheduler');
define('DIR_TMP_NAME', 'cache');
define('DIR_SWF_NAME', 'swf');
define('DIR_XML_NAME', 'xml');
define('DIR_UPLOADS_NAME', 'uploads');
define('DIR_ATTACHMENTS_NAME', 'attachments');
define('DIR_FONTS_NAME', 'fonts');
define('DIR_SOUNDS_NAME', 'audio');
define('DIR_CERTS_NAME', 'certs');
define('DIR_CSS_NAME', 'css');
define('DIR_JS_NAME', 'js');
define('DIR_DATASTORE_NAME', 'datastore');
define('DIR_WSDL_NAME', 'wsdl');
define('DIR_PAYMENTLOG_NAME', 'paymentlog');
define('DIR_SHIPPINGLOG_NAME', 'shippinglog');
define('DIR_CONTROLLERS_NAME', 'controller');
define('DIR_VIEWS_NAME', 'view');
define('DIR_CLASSES_NAME', 'model');
define('DIR_UPGRADER_NAME', 'upgrader');
define('DIR_UPGRADELOG_NAME', 'upgradelog');
define('DIR_SMTPLOG_NAME', 'smtplog');
define('DIR_LDAPLOG_NAME', 'ldaplog');
define('DIR_GEOIP_NAME', 'geoip');
define('DIR_OTHER_NAME', 'other');
define('DIR_LIVEAUCTION_NAME', 'liveauction');
define('DIR_APPLOG_NAME', 'applog');
define('DIR_ROTATELOG_NAME', 'rotatelog');

/**
* define if we want to output the debug footer on the marketplace
*/
define('DEBUG_FOOTER', false);

/**
* defines if we should show the actual database error output to the browser within a textarea field
*/
define('DB_DEBUGMODE_TEXTAREA', false);

/**
* defines if we should hide the db error information in the textarea but actually show it in the view source as commenting?
*/
define('DB_DEBUGMODE_VIEWSOURCE', true);

/**
* defines if the admincp should check the sheel web site to see if there is a new version
*/
define('VERSIONCHECK', true);

/**
* defines if we should disable any custom api code that might be causing any issues to the framework of sheel
*/
define('DISABLE_PLUGINAPI', true);

/**
* define our line-break pattern (Windows: \r\n or Linux: \n)
*/
define('LINEBREAK', "\n");

/**
* CURL path and certificate name
*/
define('CURLPATH', '/usr/local/bin/curl');
define('CURLCERT', DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_CERTS_NAME . '/certificate.cer');

/**
* Fire up sheel 6+
*/
chdir(DIR_SERVER_ROOT . DIR_APPLICATION_NAME);

require_once('./core.php');
?>
