<?php
define('LOCATION','attachment');
@ini_set('zlib.output_compression', 'Off');
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false)
{
	@ob_end_clean();
	header('Content-Encoding:');
}
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) OR !empty($_SERVER['HTTP_IF_NONE_MATCH']))
{
	$sapi_name = php_sapi_name();
	if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
	{
		header('Status: 304 Not Modified');
	}
	else
	{
		header('HTTP/1.1 304 Not Modified');
	}
	header('Content-Type:');
	header('X-Powered-By:');
	if (!empty($_REQUEST['id']))
	{
		header('Etag: "' . $_REQUEST['id'] . '"');
	}
	exit();
}
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params']))
{
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}



// we don't want the connection activity logging any attachment activity
define('SKIP_SESSION_TITLE', true);
if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'captcha')
{


        $sheel->attachment_tools->print_captcha(8);
	exit();
}
?>
