<?php
define('SITE_ROOT', __DIR__ . '/');
if (!file_exists(__DIR__ . '/application/config.php'))
{
	if (file_exists(__DIR__ . '/application/config.php.new'))
	{
		die('<b>Almost ready!</b> Please rename config.php.new to config.php and then set some default values in config.php. When done, refresh this page.');
	}
	else
	{
		die('<b>Warning:</b> We could not find the config.php or the config.php.new files.  Are you sure all the files are present?');
	}
}
if (!file_exists(__DIR__ . '/application/model/class.router.inc.php'))
{
	die('<b>Maintenance:</b> We\'ll be right back.. performing some maintenance to make your experience fast as possible.');
}
require_once(__DIR__ . '/application/model/class.router.inc.php');
$router = new router();
$router->setBasePath(''); // <-- SUB_FOLDER

$router->map('GET',      '/', __DIR__ . '/application/web/client/main.php', 'main');
$router->map('GET',      '/javascript/', __DIR__ . '/application/web/client/javascript.php', 'javascript');
$router->map('GET|POST', '/signin/', __DIR__ . '/application/web/client/login.php', 'login');
$router->map('GET|POST', '/signout/', __DIR__ . '/application/web/client/logout.php', 'logout');
$router->map('GET',      '/[content:cmd]/[*:view].html', __DIR__ . '/application/web/client/main.php', 'main_content');
$router->map('GET',      '/[content:cmd]/go/[terms|privacy|cookies:go]', __DIR__ . '/application/web/client/main.php', 'main_content_redirect');
$router->map('GET|POST', '/ajax', __DIR__ . '/application/web/client/ajax.php', 'ajax');
$router->map('GET|POST', '/register/', __DIR__ . '/application/web/client/registration.php', 'registration');
$router->map('GET|POST', '/register/[activate|welcome|verification|moderation:view]/', __DIR__ . '/application/web/client/registration.php', 'registration_view');
$router->map('GET|POST', '/attachment/[captcha:do]/', __DIR__ . '/application/web/client/captcha.php', 'captcha');
$router->map('GET',      '/home/', __DIR__ . '/application/web/client/home.php', 'home');





$router->map('GET|POST', '/admin/', __DIR__ . '/application/web/admin/home.php', 'admin_home');
$router->map('GET|POST', '/admin/signin/', __DIR__ . '/application/web/admin/login.php', 'admin_login');
$router->map('GET|POST', '/admin/signin/[renew-password:cmd]/', __DIR__ . '/application/web/admin/login.php', 'admin_login_renew');
$router->map('GET|POST', '/admin/signin/[*:cmd]/', __DIR__ . '/application/web/admin/login.php', 'admin_logout');
$router->map('GET|POST', '/admin/customers/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers');
$router->map('GET|POST', '/admin/customers/[bc:cmd]/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers_bc_list');
$router->map('GET|POST', '/admin/customers/[view|bcview|refresh|departments|positions|staff:cmd]/[*:no]/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers_view');


$router->map('GET|POST', '/admin/settings/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings');
$router->map('GET|POST', '/admin/settings/[companies|branding|locale|mail|currency|invoice|payment|registration|escrow|feedback|shipping|listings|bidding|pmb|privacy|censor|blacklist|categories|seo|search|security|distance|cache|session|attachments|license|license/plans|license/renewal|billing/update|billing/create|billing/cancel|updates|diagnosis|serverinfo|globalupdate:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_cmd');
$router->map('POST',     '/admin/settings/[branding:cmd]/[upload:subcmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_branding_upload');
$router->map('GET|POST', '/admin/settings/[companies:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_company_add');
$router->map('GET|POST', '/admin/settings/[companies:cmd]/[delete|update:subcmd]/[*:companyid]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_company_cmd');

$router->map('GET|POST', '/admin/[currency:cmd]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_cmd');
$router->map('POST',     '/admin/[currency:cmd]/[save:subcmd]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_save');
$router->map('GET|POST', '/admin/[currency:cmd]/[default|defaultusers|delete:subcmd]/[*:currencyid]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_defaults');

if (file_exists(__DIR__ . '/router_custom.php'))
{
	require_once(__DIR__ . '/router_custom.php');
}

// router match
$match = $router->match();
if (($match AND is_callable($match['target'])) OR ($match AND stristr($match['target'], '.php')))
{ // run function if we need to
    if (!is_callable($match['target']) AND stristr($match['target'], '.php'))
	{
	    
        if (file_exists($match['target'] . 'x'))
		{ // duplicate .php -> .phpx for custom changes with automatic updates on
			require $match['target'] . 'x';
		}
//        var_dump($match['target']); exit();
        require $match['target'];
    }
	else
	{
        call_user_func_array($match['target'], $match['params']);
	}
}
else
{ // run classes as required
	$web = $action = '';
	if (isset($match['target']))
	{
		list($web, $action) = explode('#', $match['target']);
	}
	if (is_callable(array($web, $action)))
	{
		$obj = new $web();
		call_user_func_array(array($obj, $action), array($match['params']));
	}
	else if ($match['target'] == '')
	{
		$template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>This page does not exist.</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
<!--
body { background-color: white; color: black; }
#container { width: 400px; }
#message   { width: 400px; color: black; background-color: #FFFFCC; }
#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
-->
</style>
</head>
<body>
<table cellpadding="3" cellspacing="5" id="container">
<tr>
        <td id="bodytitle" width="100%">Page not found.</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">This page no longer exists or was recently removed.</td>
</tr>
<tr>
        <td colspan="2"><hr /></td>
</tr>
<tr>
        <td class="bodytext" colspan="2">
                Please try the following:
                <ul>
                        <li><a href="/">Load the homepage</a> again.</li>
                        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
                </ul>
        </td>
</tr>
<tr>
        <td class="bodytext" colspan="2">We apologise for any inconvenience.</td>
</tr>
</table>
</body>
</html>';
                        // tell the search engines that our service is temporarily unavailable to prevent indexing db errors
                        header('HTTP/1.1 404 Service Temporarily Unavailable');
                        header('Status: 404 Service Temporarily Unavailable');
                        header('Retry-After: 3600');
                        die($template);
	}
	else
	{
		echo 'Error: can not call ' . $web . '#' . $action;
	}
}

?>
