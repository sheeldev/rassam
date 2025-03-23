<?php
define('SITE_ROOT', __DIR__ . '/');
if (!file_exists(__DIR__ . '/application/config.php')) {
	if (file_exists(__DIR__ . '/application/config.php.new')) {
		die('<b>Almost ready!</b> Please rename config.php.new to config.php and then set some default values in config.php. When done, refresh this page.');
	} else {
		die('<b>Warning:</b> We could not find the config.php or the config.php.new files.  Are you sure all the files are present?');
	}
}
if (!file_exists(__DIR__ . '/application/model/class.router.inc.php')) {
	die('<b>Maintenance:</b> We\'ll be right back.. performing some maintenance to make your experience fast as possible.');
}
require_once(__DIR__ . '/application/config.php');
require_once(__DIR__ . '/application/model/class.router.inc.php');
$router = new router();
$router->setBasePath(''); // <-- SUB_FOLDER

$router->map('GET|POST', '/rpc/', __DIR__ . '/application/web/client/rpc.php', 'rpc');
$router->map('GET', '/', __DIR__ . '/application/web/client/main.php', 'main');
$router->map('GET', '/javascript/', __DIR__ . '/application/web/client/javascript.php', 'javascript');
$router->map('GET|POST', '/signin/', __DIR__ . '/application/web/client/login.php', 'login');
$router->map('GET|POST', '/signout/', __DIR__ . '/application/web/client/logout.php', 'logout');
$router->map('GET', '/[content:cmd]/[*:view].html', __DIR__ . '/application/web/client/main.php', 'main_content');
$router->map('GET', '/[content:cmd]/go/[terms|privacy|cookies:go]', __DIR__ . '/application/web/client/main.php', 'main_content_redirect');
$router->map('GET|POST', '/ajax', __DIR__ . '/application/web/client/ajax.php', 'ajax');
$router->map('GET|POST', '/register/', __DIR__ . '/application/web/client/registration.php', 'registration');
$router->map('GET|POST', '/register/[activate|welcome|verification|moderation:view]/', __DIR__ . '/application/web/client/registration.php', 'registration_view');
$router->map('GET|POST', '/attachment/[captcha:do]/', __DIR__ . '/application/web/client/captcha.php', 'captcha');
$router->map('GET', '/home/', __DIR__ . '/application/web/client/home.php', 'home');


$router->map('GET|POST', '/admin/', __DIR__ . '/application/web/admin/home.php', 'admin_home');
$router->map('GET|POST', '/admin/access.php', __DIR__ . '/application/web/admin/access.php', 'admin_access');
$router->map('GET|POST', '/admin/lookup/', __DIR__ . '/application/web/admin/lookup.php', 'admin_lookup');
$router->map('GET|POST', '/admin/signin/', __DIR__ . '/application/web/admin/login.php', 'admin_login');
$router->map('GET|POST', '/admin/signin/[renew-password:cmd]/', __DIR__ . '/application/web/admin/login.php', 'admin_login_renew');
$router->map('GET|POST', '/admin/signin/[*:cmd]/', __DIR__ . '/application/web/admin/login.php', 'admin_logout');
$router->map('GET|POST', '/admin/customers/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers');
$router->map('GET|POST', '/admin/customers/[bc:cmd]/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers_bc_list');
$router->map('GET|POST', '/admin/customers/[org:cmd]/[*:no]/[*:sub]/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers_org');
$router->map('GET|POST', '/admin/customers/[view|bcview|refresh|measurementsview|sizesview:cmd]/[*:no]/', __DIR__ . '/application/web/admin/customers.php', 'admin_customers_view');
$router->map('GET|POST', '/admin/customers/[orders:cmd]/[*:no]/', __DIR__ . '/application/web/admin/orders.php', 'admin_customer_orders_list');
$router->map('GET|POST', '/admin/users/', __DIR__ . '/application/web/admin/users.php', 'admin_users');
$router->map('GET|POST', '/admin/users/[update:cmd]/[*:userid]/[*:view]/', __DIR__ . '/application/web/admin/users.php', 'admin_update_user_view');
$router->map('GET|POST', '/admin/users/[update|switch:cmd]/[*:userid]/', __DIR__ . '/application/web/admin/users.php', 'admin_update_user');
$router->map('GET|POST', '/admin/users/[add|verifications|violations|audit:cmd]/', __DIR__ . '/application/web/admin/users.php', 'admin_users_add');
$router->map('GET|POST', '/admin/users/[bulkmailer|bulkmailer/[export:cmd]/', __DIR__ . '/application/web/admin/users.php', 'admin_users_bulkmailer');
$router->map('GET|POST', '/admin/users/roles/', __DIR__ . '/application/web/admin/roles.php', 'admin_role');
$router->map('GET|POST', '/admin/users/[roles:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/roles.php', 'admin_role_add');
$router->map('GET|POST', '/admin/users/[roles:cmd]/[delete|update:subcmd]/[*:roleid]/', __DIR__ . '/application/web/admin/roles.php', 'admin_role_cmd');
$router->map('GET|POST', '/admin/users/[roles:cmd]/[access:subcmd]/[*:roleid]/', __DIR__ . '/application/web/admin/roles.php', 'admin_role_access');

$router->map('GET|POST', '/admin/settings/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings');
$router->map('GET|POST', '/admin/settings/[branding:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_branding');
$router->map('GET|POST', '/admin/settings/[branding:cmd]/[upload:subcmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_branding_upload');
$router->map('GET|POST', '/admin/settings/[locale:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_locale');
$router->map('GET|POST', '/admin/settings/[companies:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_companies');
$router->map('GET|POST', '/admin/settings/[companies:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_company_add');
$router->map('GET|POST', '/admin/settings/[companies:cmd]/[delete|update|default|factory:subcmd]/[*:companyid]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_company_cmd');
$router->map('GET|POST', '/admin/settings/[currency:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_currency');
$router->map('GET|POST', '/admin/settings/[currencymanager:cmd]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_cmd');
$router->map('GET|POST', '/admin/settings/[currencymanager:cmd]/[save:subcmd]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_save');
$router->map('GET|POST', '/admin/settings/[currencymanager:cmd]/[default|defaultusers|delete:subcmd]/[*:currencyid]/', __DIR__ . '/application/web/admin/currency.php', 'admin_currency_defaults');

$router->map('GET|POST', '/admin/settings/[registration:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_registration');
$router->map('GET|POST', '/admin/settings/[security:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_security');
$router->map('GET|POST', '/admin/settings/[optimization:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_optimization');
$router->map('GET|POST', '/admin/settings/[session:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_session');
$router->map('GET|POST', '/admin/settings/[photos:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_photos');
$router->map('GET|POST', '/admin/settings/[diagnosis:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_diagnosis');
$router->map('GET|POST', '/admin/settings/[serverinfo:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_serverinfo');
$router->map('GET|POST', '/admin/settings/[globalupdate:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_globalupdate');

$router->map('GET|POST', '/admin/reports/', __DIR__ . '/application/web/admin/reports.php', 'admin_reports');
$router->map('GET|POST', '/admin/dashboard/', __DIR__ . '/application/web/admin/dashboard.php', 'admin_dashboard');
$router->map('GET|POST', '/admin/dashboard/[*:view]/', __DIR__ . '/application/web/admin/dashboard.php', 'admin_dashboard_cmd');

$router->map('GET|POST', '/admin/settings/[mail:cmd]/', __DIR__ . '/application/web/admin/settings.php', 'admin_settings_email');
$router->map('GET|POST', '/admin/settings/emails/', __DIR__ . '/application/web/admin/emailtemplates.php', 'admin_email_templates');
$router->map('GET|POST', '/admin/settings/[emails:cmd]/[update:subcmd]/[*:varname]/', __DIR__ . '/application/web/admin/emailtemplates.php', 'admin_email_templates_action');
$router->map('GET|POST', '/admin/settings/[emails:cmd]/[import|export|add|save:subcmd]/', __DIR__ . '/application/web/admin/emailtemplates.php', 'admin_email_templates_impex');

$router->map('GET|POST', '/admin/settings/pages/', __DIR__ . '/application/web/admin/pages.php', 'admin_pages');
$router->map('GET|POST', '/admin/settings/[pages:cmd]/[add|add/link:subcmd]/', __DIR__ . '/application/web/admin/pages.php', 'admin_pages_add_cmd');
$router->map('GET|POST', '/admin/settings/[pages:cmd]/[update|update/link|delete:subcmd]/[*:seourl]/', __DIR__ . '/application/web/admin/pages.php', 'admin_pages_cmd');
$router->map('GET|POST', '/admin/settings/heros/', __DIR__ . '/application/web/admin/heros.php', 'admin_heros');
$router->map('GET|POST', '/admin/settings/[heros:cmd]/[upload:subcmd]/', __DIR__ . '/application/web/admin/heros.php', 'admin_heros_upload');
$router->map('GET|POST', '/admin/settings/memberships/', __DIR__ . '/application/web/admin/memberships.php', 'admin_membership');
$router->map('GET|POST', '/admin/settings/[memberships:cmd]/[add:subcmd]/[plan|permission:type]/', __DIR__ . '/application/web/admin/memberships.php', 'admin_membership_add_type_cmd');
$router->map('GET|POST', '/admin/settings/[memberships:cmd]/[delete|update:subcmd]/[plan:type]/[*:subscriptionid]/', __DIR__ . '/application/web/admin/memberships.php', 'admin_membership_delete_plan_cmd');
$router->map('GET|POST', '/admin/settings/[memberships:cmd]/[delete|update:subcmd]/[permissions:type]/[*:subscriptionid]/', __DIR__ . '/application/web/admin/memberships.php', 'admin_membership_delete_group_cmd');
$router->map('GET|POST', '/admin/settings/[memberships:cmd]/[upload:subcmd]/', __DIR__ . '/application/web/admin/memberships.php', 'admin_membership_upload_badge');
$router->map('GET|POST', '/admin/settings/motd/', __DIR__ . '/application/web/admin/motd.php', 'admin_motd');
$router->map('GET|POST', '/admin/settings/[motd:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/motd.php', 'admin_motd_add');
$router->map('GET|POST', '/admin/settings/[motd:cmd]/[delete:subcmd]/[*:id]/', __DIR__ . '/application/web/admin/motd.php', 'admin_motd_cmd');
$router->map('GET|POST', '/admin/settings/announcements/', __DIR__ . '/application/web/admin/announcements.php', 'admin_announcements');
$router->map('GET|POST', '/admin/settings/[announcements:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/announcements.php', 'admin_announcements_add');
$router->map('GET|POST', '/admin/settings/[announcements:cmd]/[delete|update:subcmd]/[*:announcementid]/', __DIR__ . '/application/web/admin/announcements.php', 'admin_announcements_cmd');
$router->map('GET|POST', '/admin/settings/attachments/', __DIR__ . '/application/web/admin/attachments.php', 'admin_attachments');
$router->map('GET|POST', '/admin/settings/languages/', __DIR__ . '/application/web/admin/languages.php', 'admin_languages');
$router->map('GET|POST', '/admin/settings/[languages:cmd]/[orphan:subcmd]/[phrase:type]/', __DIR__ . '/application/web/admin/languages.php', 'admin_orphan_languages_action');
$router->map('GET|POST', '/admin/settings/[languages:cmd]/[update|delete|default|defaultusers:subcmd]/[*:languageid]/', __DIR__ . '/application/web/admin/languages.php', 'admin_languages_action');
$router->map('GET|POST', '/admin/settings/[languages:cmd]/[import|export|add:subcmd]/', __DIR__ . '/application/web/admin/languages.php', 'admin_language_impex');
$router->map('GET|POST', '/admin/settings/locations/', __DIR__ . '/application/web/admin/locations.php', 'admin_locations');
$router->map('GET|POST', '/admin/settings/[locations:cmd]/[update:subcmd]/[region|country:mode]/[*:locationid]/', __DIR__ . '/application/web/admin/locations.php', 'admin_locations_update_cmd');
$router->map('GET|POST', '/admin/settings/sizingsystem/', __DIR__ . '/application/web/admin/sizingrules.php', 'admin_sizingrules');
$router->map('GET|POST', '/admin/settings/[sizingsystem:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/sizingrules.php', 'admin_sizingrules_add');
$router->map('GET|POST', '/admin/settings/[sizingsystem:cmd]/[update|delete|deleteline:subcmd]/[*:code]/', __DIR__ . '/application/web/admin/sizingrules.php', 'admin_sizingrules_delete');
$router->map('GET|POST', '/admin/settings/[sizingsystem:cmd]/[sizesystem|deletetype|types|deletecategory|categories:subcmd]/', __DIR__ . '/application/web/admin/sizingrules.php', 'admin_sizingtypes');
$router->map('GET|POST', '/admin/settings/genai/', __DIR__ . '/application/web/admin/genai.php', 'admin_genai');
$router->map('GET|POST', '/admin/settings/[genai:cmd]/[add:subcmd]/', __DIR__ . '/application/web/admin/genai.php', 'admin_genai_add');
$router->map('GET|POST', '/admin/settings/[genai:cmd]/[update|delete|deleteline:subcmd]/[*:id]/', __DIR__ . '/application/web/admin/genai.php', 'admin_genai_delete');
$router->map('GET|POST', '/admin/settings/[genai:cmd]/[config:subcmd]/', __DIR__ . '/application/web/admin/genai.php', 'admin_genai_config');


$router->map('GET|POST', '/admin/settings/bc/', __DIR__ . '/application/web/admin/bc.php', 'admin_bc');
$router->map('GET|POST', '/admin/settings/api/', __DIR__ . '/application/web/admin/api.php', 'admin_api');
$router->map('GET|POST', '/admin/settings/automation/', __DIR__ . '/application/web/admin/automation.php', 'admin_automation');
$router->map('GET|POST', '/admin/settings/[automation:cmd]/[add:subcmd]/task/', __DIR__ . '/application/web/admin/automation.php', 'admin_automation_add');
$router->map('GET|POST', '/admin/settings/[automation:cmd]/[delete|update|run:subcmd]/task/[*:cronid]/', __DIR__ . '/application/web/admin/automation.php', 'admin_automation_cmd');
$router->map('GET|POST', '/admin/settings/[automation:cmd]/[configurations:subcmd]/', __DIR__ . '/application/web/admin/automation.php', 'admin_automation_config');
$router->map('GET|POST', '/admin/sessions/', __DIR__ . '/application/web/admin/sessions.php', 'admin_connections');


if (file_exists(__DIR__ . '/router_custom.php')) {
	require_once(__DIR__ . '/router_custom.php');
}

// router match
$match = $router->match();
if (($match and is_callable($match['target'])) or ($match and stristr($match['target'], '.php'))) { // run function if we need to
	if (!is_callable($match['target']) and stristr($match['target'], '.php')) {
		if (file_exists($match['target'] . 'x')) { // duplicate .php -> .phpx for custom changes with automatic updates on
			require $match['target'] . 'x';
		}
		if (!isset($_SESSION['sheeldata']['user']['userid'])) {
			require $match['target'];
		} else {
			if ($sheel->access->has_access($_SESSION['sheeldata']['user']['userid'], $match['name'])) {
				require $match['target'];
			} else {
				if ($_SESSION['sheeldata']['user']['isadmin']) {
					require __DIR__ . '/application/web/admin/access.php';
				}
				else {
					require __DIR__ . '/application/web/client/access.php';
				}
			}
		}
	} else {
		call_user_func_array($match['target'], $match['params']);
	}
} else { // run classes as required
	$web = $action = '';
	if (isset($match['target'])) {
		list($web, $action) = explode('#', $match['target']);
	}
	if (is_callable(array($web, $action))) {
		$obj = new $web();
		call_user_func_array(array($obj, $action), array($match['params']));
	} else if ($match['target'] == '') {
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
	} else {
		echo 'Error: can not call ' . $web . '#' . $action;
	}
}

?>