<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'admin',
		'inline',
		'vendor/chartist',
		'vendor/growl',
		'vendor/inputtags'
	),
	'footer' => array(
		'vendor/inputtags'
	)
);
$sheel->template->meta['cssinclude'] = array(
	'common',
	'vendor' => array(
		'inputtags',
		'font-awesome',
		'glyphicons',
		'chartist',
		'growl'
	)
);
$sheel->template->meta['areatitle'] = 'Admin CP | Settings';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
	if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
		$sidenav = $sheel->admincp_nav->print('settings');
		$sheel->cache->store("sidenav_settings", $sidenav);
	}
	if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'globalupdate' and isset($sheel->GPC['subcmd']) and !empty($sheel->GPC['subcmd'])) {
		$notice = '';
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == '_update-config-settings') {
			$error = '';
			if (isset($_FILES['logobig']['name']) and !empty($_FILES['logobig']['name'])) { // logo if applicable
				$ext = mb_substr($_FILES['logobig']['name'], strpos($_FILES['logobig']['name'], '.'), strlen($_FILES['logobig']['name']) - 1);
				if (in_array($ext, array('.png')) and filesize($_FILES['logobig']['tmp_name']) <= 150000) {
					$tmp = getimagesize($_FILES['logobig']['tmp_name']);
					// check if mime is png!
					if (isset($tmp['mime']) and $tmp['mime'] != 'image/png') {
						$error = 'Logo file could not be uploaded (not a valid .png file)';
					} else {
						// check if logo height exceeds 44 px!
						if (isset($tmp[1]) and $tmp[1] > 44) {
							$error = 'Logo file could not be uploaded (height exceeds 44px)';
						} else {
							if (file_exists(DIR_ATTACHMENTS . 'meta/logo.png')) {
								unlink(DIR_ATTACHMENTS . 'meta/logo.png');
							}
							if (!move_uploaded_file($_FILES['logobig']['tmp_name'], DIR_ATTACHMENTS . 'meta/logo.png')) {
								$error = 'Logo file could not be uploaded (check folder permissions)';
							}
						}
					}
				} else {
					$error = 'The logo must be a .png file and under 150kb in size.';
				}
			}
			if (isset($_FILES['logomobile']['name']) and !empty($_FILES['logomobile']['name'])) { // logo if applicable
				$ext = mb_substr($_FILES['logomobile']['name'], strpos($_FILES['logomobile']['name'], '.'), strlen($_FILES['logomobile']['name']) - 1);
				if (in_array($ext, array('.png')) and filesize($_FILES['logomobile']['tmp_name']) <= 150000) {
					$tmp = getimagesize($_FILES['logomobile']['tmp_name']);
					// check if mime is png!
					if (isset($tmp['mime']) and $tmp['mime'] != 'image/png') {
						$error = 'Mobile logo file could not be uploaded (not a valid .png file)';
					} else {
						// check if logo height exceeds 44 px!
						if (isset($tmp[1]) and $tmp[1] > 44) {
							$error = 'Mobile logo file could not be uploaded (height exceeds 44px)';
						} else {
							if (file_exists(DIR_ATTACHMENTS . 'meta/logo-mobile.png')) {
								unlink(DIR_ATTACHMENTS . 'meta/logo-mobile.png');
							}
							if (!move_uploaded_file($_FILES['logomobile']['tmp_name'], DIR_ATTACHMENTS . 'meta/logo-mobile.png')) {
								$error = 'Mobile logo file could not be uploaded (check folder permissions)';
							}
						}
					}
				} else {
					$error = 'The mobile logo must be a .png file and under 150kb in size.';
				}
			}
			if (isset($_FILES['favicon']['name']) and !empty($_FILES['favicon']['name'])) { // favicon.png if applicable
				$ext = mb_substr($_FILES['favicon']['name'], strpos($_FILES['favicon']['name'], '.'), strlen($_FILES['favicon']['name']) - 1);
				if (in_array($ext, array('.png')) and filesize($_FILES['favicon']['tmp_name']) <= 35000) {
					$tmp = getimagesize($_FILES['favicon']['tmp_name']);
					// check if mime is png!
					if (isset($tmp['mime']) and $tmp['mime'] != 'image/png') {
						$error = 'Favicon file could not be uploaded (not a valid .png file)';
					} else {
						// check if favicon is 16x16
						if (isset($tmp[1]) and $tmp[1] != 16 and isset($tmp[0]) and $tmp[0] != 16) {
							$error = 'Favicon file could not be uploaded (height or width exceeds 16px)';
						} else {
							if (file_exists(DIR_ATTACHMENTS . 'meta/favicon.png')) {
								unlink(DIR_ATTACHMENTS . 'meta/favicon.png');
							}
							if (!move_uploaded_file($_FILES['favicon']['tmp_name'], DIR_ATTACHMENTS . 'meta/favicon.png')) {
								$error = 'Favicon icon file could not be uploaded (check folder permissions)';
							}
						}
					}
				} else {
					$error = 'The favicon icon must be a .png file, under 35kb in size with dimension of 16x16px';
				}
			}
			if (!empty($error)) {
				$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Branding';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Branding';
				$sheel->template->fetch('main', 'settings_branding.html', 1);
				$areanav = 'settings_branding';
				$currentarea = '{_branding}';
				$settings = $sheel->admincp->construct_admin_input('branding', 'settings/branding/');
				$vars = array(
					'sidenav' => (isset($sidenav) ? $sidenav : ''),
					'areanav' => (isset($areanav) ? $areanav : ''),
					'currentarea' => (isset($currentarea) ? $currentarea : ''),
					'settings' => (isset($settings) ? $settings : ''),
					'moderation' => (isset($moderation) ? $moderation : ''),
					'limits' => (isset($limits) ? $limits : ''),
					'id' => (isset($sheel->GPC['id']) ? $sheel->GPC['id'] : ''),
					'error' => ((isset($error) and !empty($error)) ? $error : ''),
				);
				$sheel->template->pprint('main', $vars);
				exit();
			}
			if (isset($sheel->GPC['config'])) {
				foreach ($sheel->GPC['config'] as $varname => $value) {
					if ($varname == 'maxshipservices') { // prevent db errors (only have 10 fields to hold that data)
						if ($value > 10) {
							$value = 10;
						}
					} else if ($varname == 'shipping_regions') { // serialize array data
						$value = serialize($value);
					} else if ($varname == 'invoicesystem_disputetypes') { // serialize array data
						$value = serialize($value);
					} else if ($varname == 'globalfilters_vulgarpostfilterlist') { // remove trailing comma
						$value = trim($value);
						if (substr($value, -1) == ',') {
							$value = substr($value, 0, -1);
						}
					} else if ($varname == 'globalfilters_blockips') { // remove trailing comma
						$value = trim($value);
						if (substr($value, -1) == ',') {
							$value = substr($value, 0, -1);
						}
					} else if ($varname == 'globalfilters_blockcountries') { // remove trailing comma
						$value = trim($value);
						if (substr($value, -1) == ',') {
							$value = substr($value, 0, -1);
						}
					}
					$sort = ((isset($sheel->GPC['sort'][$varname])) ? $sheel->GPC['sort'][$varname] : '100');
					if (isset($sheel->GPC['pass'][$varname]) and $sheel->GPC['pass'][$varname] != '') { // we're a pass field
						if ($value != '') { // set new value
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "configuration
								SET value = '" . $sheel->db->escape_string($value) . "',
								sort = '" . intval($sort) . "'
								WHERE name = '" . $sheel->db->escape_string($varname) . "'
								LIMIT 1
							");
						}
					} else { // regular field
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "configuration
							SET value = '" . $sheel->db->escape_string($value) . "',
							sort = '" . intval($sort) . "'
							WHERE name = '" . $sheel->db->escape_string($varname) . "'
							LIMIT 1
						");
						if ($varname == 'escrowsystem_enabled') {
							if ($value <= 0) {
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "payment_profiles
									SET filter_escrow = '0'
								");
							}
						}
						$sql = $sheel->db->query("
							SELECT value, inputname
							FROM " . DB_PREFIX . "configuration
							WHERE name = '" . $sheel->db->escape_string($varname) . "'
								AND inputtype = 'pulldown'
						");
						if ($sheel->db->num_rows($sql) > 0) {
							$res = $sheel->db->fetch_array($sql, DB_ASSOC);
							$writepulldown = '';
							if ($res['inputname'] == 'currencyrates') {
								$writepulldown = $sheel->currency->pulldown('admin', $varname);
							} else if ($res['inputname'] == 'cryptocurrencyrates') {
								$writepulldown = $sheel->currency->cryptopulldown('admin', $varname);
							} else if ($res['inputname'] == 'disputetypes') {
								$writepulldown = $sheel->accounting_payment->disputes_pulldown($varname, $value);
							}
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "configuration
								SET inputcode = '" . $sheel->db->escape_string($writepulldown) . "'
								WHERE name = '" . $sheel->db->escape_string($varname) . "'
								LIMIT 1
							");
						}
					}
				}
			}
			$sheel->cachecore->delete("ilconfig", array('uid' => false, 'sid' => false, 'rid' => false, 'styleid' => false, 'slng' => false));
			$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Settings saved', 'Settings were successfully saved.');
			refresh(urldecode($sheel->GPC['return']));
			exit();
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == '_update-payment-settings') {
			foreach ($sheel->GPC['config'] as $key => $value) {
				if ($key == 'use_internal_gateway') { // we're updating the payment pulldown menu
					$sql = $sheel->db->query("
						SELECT id, value, inputname
						FROM " . DB_PREFIX . "payment_configuration
						WHERE name = '" . $sheel->db->escape_string($key) . "'
							AND inputtype = 'pulldown'
					", 0, null, __FILE__, __LINE__);
					if ($sheel->db->num_rows($sql) > 0) {
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						if ($res['inputname'] == 'defaultgateway') {
							$writepulldown = $sheel->admincp_paymodules->default_gateway_pulldown($value, $key);
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "payment_configuration
								SET inputcode = '" . $sheel->db->escape_string($writepulldown) . "',
								value = '" . $sheel->db->escape_string($value) . "'
								WHERE name = '" . $sheel->db->escape_string($key) . "'
									AND inputtype = 'pulldown'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($value == 'none') { // disable credit card payment selling profiles for sellers so new customers don't get confused..
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "payment_profiles
									SET filter_ccgateway = '0'
								", 0, null, __FILE__, __LINE__);
							} else { // re enable credit card payment profiles for sellers selling profiles..
								$sql2 = $sheel->db->query("
									SELECT id
									FROM " . DB_PREFIX . "payment_profiles
									WHERE paymethodcc != ''
										AND paymethodcc != 'a:0:{}'
										AND paymethodcc != '" . $sheel->db->escape_string('s:0:"";') . "'
								", 0, null, __FILE__, __LINE__);
								if ($sheel->db->num_rows($sql2) > 0) {
									while ($res2 = $sheel->db->fetch_array($sql2, DB_ASSOC)) {
										$paymethodcc = array();
										$paymethodcc[$value] = '1';
										$paymethodcc = serialize($paymethodcc);
										$sheel->db->query("
											UPDATE " . DB_PREFIX . "payment_profiles
											SET filter_ccgateway = '1',
											paymethodcc = '" . $sheel->db->escape_string($paymethodcc) . "'
											WHERE id = '" . $res2['id'] . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
									// normalize fields
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "payment_profiles
										SET paymethodcc = 'a:0:{}'
										WHERE paymethodcc = '' OR paymethodcc = '" . $sheel->db->escape_string('s:0:"";') . "'
									", 0, null, __FILE__, __LINE__);
								}
							}
						}
					}
				} else {
					if ($key != '') {
						if ($key == 'creditcard_authentication' and $value == 0) {
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "creditcards
								SET authorized = 'yes'
								WHERE authorized = 'no'
							", 0, null, __FILE__, __LINE__);
						}
						if (isset($sheel->GPC['pass'][$key]) and $sheel->GPC['pass'][$key] != '') { // we're a pass field
							if ($value != '') { // set new value
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "payment_configuration
									SET `value` = '" . $sheel->db->escape_string(trim($value)) . "',
									sort = '" . intval($sheel->GPC['sort'][$key]) . "'
									WHERE name = '" . $sheel->db->escape_string($key) . "'
										" . ((isset($sheel->GPC['module']) and !empty($sheel->GPC['module'])) ? "AND configgroup = '" . $sheel->db->escape_string($sheel->GPC['module']) . "'" : "") . "
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
							}
						} else { // regular field
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "payment_configuration
								SET `value` = '" . $sheel->db->escape_string(trim($value)) . "',
								sort = '" . intval($sheel->GPC['sort'][$key]) . "'
								WHERE name = '" . $sheel->db->escape_string($key) . "'
									" . ((isset($sheel->GPC['module']) and !empty($sheel->GPC['module'])) ? "AND configgroup = '" . $sheel->db->escape_string($sheel->GPC['module']) . "'" : "") . "
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
						}
					}
				}
			}
			$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'System payment settings saved', 'System payment settings were successfully saved.');
			refresh(HTTPS_SERVER_ADMIN . 'settings/payment/');
			exit();
		}
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'branding') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Branding';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Branding';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_branding';
		$sheel->template->fetch('main', 'settings_branding.html', 1);
		$areanav = 'settings_branding';
		$currentarea = '{_branding}';
		$form['currentdomain'] = $_SERVER['SERVER_NAME'];
		$settings = $sheel->admincp->construct_admin_input('branding', 'settings/branding/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'locale') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Locale';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Locale';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_locale';
		$currentarea = '{_locale}';
		$settings = $sheel->admincp->construct_admin_input('globalserverlocale', HTTPS_SERVER_ADMIN . 'settings/locale/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'mail') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Mail &amp; SMTP';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Mail &amp; SMTP';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_mail';
		$currentarea = '{_mail}';
		$settings = $sheel->admincp->construct_admin_input('globalserversmtp', HTTPS_SERVER_ADMIN . 'settings/mail/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'currency') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Currencies';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Currencies';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_currency';
		$currentarea = '{_currency}';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'currency/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_currency_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('globalserverlocalecurrency', HTTPS_SERVER_ADMIN . 'settings/currency/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'invoice') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Invoice &amp; Transactions';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Invoice &amp; Transactions';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_invoice';
		$currentarea = '{_invoice}';
		$settings = $sheel->admincp->construct_admin_input('invoicesystem', HTTPS_SERVER_ADMIN . 'settings/invoice/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'companies') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Companies';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Companies';
		$sheel->template->fetch('main', 'settings_companies.html', 1);
		$areanav = 'settings_companies';
		$currentarea = '{_companies}';
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
				$countryid = intval($sheel->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'", "locationid"));
				if ($countryid == 0) {
					$sheel->admincp->print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', HTTPS_SERVER_ADMIN . 'settings/companies/');
					exit();
				}
				$sql = $sheel->db->query("
				SELECT company_id
				FROM " . DB_PREFIX . "companies
				WHERE bc_code = '" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0)
				{
					$sheel->admincp->print_action_failed('Business Central Code already exist. Please choose a different Code.', HTTPS_SERVER_ADMIN . 'settings/companies/add/');
					exit();	
				}
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "companies
					(company_id, name, bc_code, about, description, status, countryid,currencyid, timezone)
					VALUES(
					NULL,
					'" . $sheel->db->escape_string($sheel->GPC['form']['name']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['about']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['status']) . "',
					'" . intval($countryid) . "',
					'" . intval($sheel->GPC['form']['currencyid']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['tz']) . "')
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER_ADMIN . 'settings/companies/');
				exit();
			}
			$form['name'] = '';
			$form['bc_code'] = '';
			$form['about'] = '';
			$form['description'] = '';
			$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
			$tzlistfinal=array();
			foreach ($tzlist as $key => $value) {
                $tzlistfinal[$value]= $value;
           }
   
			$form['tz'] = $sheel->construct_pulldown('tz', 'form[tz]', $tzlistfinal, '', 'class="draw-select"');
			$form['country_js_pulldown'] = $sheel->common_location->construct_country_pulldown(0, '', 'country', false);
			$form['currencypulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', '');
			$statuses = array('active' => '{_active}', 'inactive' => '{_inactive}');
			$form['status'] = $sheel->construct_pulldown('status', 'form[status]', $statuses, '', 'class="draw-select"');
			
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update' and isset($sheel->GPC['companyid']) and $sheel->GPC['companyid'] > 0) {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
				if (empty($sheel->GPC['country'])) {
					$sheel->admincp->print_action_failed('{_you_must_choose_country}', HTTPS_SERVER_ADMIN . 'settings/companies/');
					exit();
				}
				$countryid = (int) $sheel->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'", "locationid");
				if ($countryid == 0) {
					$sheel->admincp->print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', HTTPS_SERVER_ADMIN . 'settings/companies/');
					exit();
				}
				if (empty($sheel->GPC['form']['name'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_company_name}', HTTPS_SERVER_ADMIN . 'settings/companies/');
					exit();
				}
				if (empty($sheel->GPC['form']['bc_code'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_business_central_reference}', HTTPS_SERVER_ADMIN . 'settings/companies/');
					exit();
				}
				$sql = $sheel->db->query("
				SELECT company_id
				FROM " . DB_PREFIX . "companies
				WHERE bc_code = '" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0)
				{
					$sheel->admincp->print_action_failed('Business Central Code already exist. Please choose a different Code.', HTTPS_SERVER_ADMIN . 'settings/companies/update/'.intval($sheel->GPC['companyid']).'/');
					exit();	
				}

				$sheel->db->query("
					UPDATE " . DB_PREFIX . "companies
					SET name = '" . $sheel->db->escape_string($sheel->GPC['form']['name']) . "',
					bc_code = '" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "',
					about = '" . $sheel->db->escape_string($sheel->GPC['form']['about']) . "',
					description = '" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					countryid = '" . $countryid . "',
					currencyid = '" . intval($sheel->GPC['form']['currencyid']) . "',
					status = '" . $sheel->GPC['form']['status'] . "',
					timezone = '" . $sheel->GPC['form']['tz'] . "'
					WHERE company_id = '" . intval($sheel->GPC['companyid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER_ADMIN . 'settings/companies/');
				exit();
			}
			$sql = $sheel->db->query("
			SELECT company_id, name, bc_code, about, description, countryid, currencyid, status, timezone
			FROM " . DB_PREFIX . "companies
			WHERE company_id = '" . intval($sheel->GPC['companyid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0)
			{
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$form['companyid'] = $res['company_id'];
				$form['name'] = $res['name'];
				$form['bc_code'] = $res['bc_code'];
				$form['about'] = $res['about'];
				$form['description'] = $res['description'];
				$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
				$tzlistfinal=array();
				foreach ($tzlist as $key => $value) {
					$tzlistfinal[$value]= $value;
			   }
	   
				$form['tz'] = $sheel->construct_pulldown('tz', 'form[tz]', $tzlistfinal, $res['timezone'], 'class="draw-select"');
				$form['currencypulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', $res['currencyid']);
				$statuses = array('active' => '{_active}', 'inactive' => '{_inactive}');
				$form['status'] = $sheel->construct_pulldown('status', 'form[status]', $statuses, $res['status'], 'class="draw-select"');


				$countryid = $res['countryid'];
			}

			$form['country_js_pulldown'] = $sheel->common_location->construct_country_pulldown($countryid, $countryname, 'country', false);
			
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
			if (isset($sheel->GPC['companyid']) and $sheel->GPC['companyid'] > 0) {
				$sheel->db->query("
					DELETE FROM " . DB_PREFIX . "companies
					WHERE company_id = '" . intval($sheel->GPC['companyid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Company deleted', 'A managed company has been successfully deleted.');
				$sheel->template->templateregistry['message'] = 'A Company has been successfully deleted.';
				die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));

			} else {
				$sheel->template->templateregistry['message'] = 'This Company could not be deleted.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'default') { // update system to this new currency

			if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
				
				die(json_encode(array('response' => 1, 'message' => 'Successfully set default system company to ID ' . $sheel->GPC['xid'])));
			} else {
				$sheel->template->templateregistry['message'] = 'No company was selected.  Please try again.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		$sqlcomp = $sheel->db->query("
			SELECT company_id, name, bc_code, description, countryid, currencyid, status, timezone, isdefault
			FROM " . DB_PREFIX . "companies
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlcomp) > 0) {
			while ($comp = $sheel->db->fetch_array($sqlcomp, DB_ASSOC)) {
				$comp['currency'] = $sheel->currency->currencies[$comp['currencyid']]['currency_abbrev'];
				$comp['country'] = $sheel->common_location->print_country_name($comp['countryid'], $_SESSION['sheeldata']['user']['slng'], false, '');
				$comp['action'] = '<a href="javascript:;"' . (($comp['isdefault'] == '1') ? '' : ' data-bind-event-click="acp_confirm(\'default\', \'{_set_system_default_company}\', \'{_set_company_system_default_message}\', \'' . $comp['company_id'] . '\', 1, \'\', \'\')"') . ' class="btn btn-slim btn--icon" title="'. (($comp['isdefault'] == '1') ? '{_default_company}' : '{_set_as_default}') .'"><span class="halflings halflings-star draw-icon' . (($comp['isdefault'] == '1') ? '--sky-darker' : '') . '" aria-hidden="true"></span></a></li></ul>';

				$comps[] = $comp;;
			}
		}
		$sheel->template->parse_loop('main', array('comps' => $comps));

	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'registration') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Registration';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Registration';
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings - Registration';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings - Registration';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_settings';
		$sheel->template->fetch('main', 'settings_registration.html', 1);
		$areanav = 'settings_registration';
		$currentarea = 'Registration';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'customers/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Customer Manager</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('registrationdisplay', HTTPS_SERVER_ADMIN . 'settings/registration/', '', $buttons);
		$upsell = $sheel->admincp->construct_admin_input('registrationupsell', HTTPS_SERVER_ADMIN . 'settings/registration/');
		$api = $sheel->admincp->construct_admin_input('registrationapi', HTTPS_SERVER_ADMIN . 'settings/registration/');
		$buttons = '<p><a href="javascript:;" onclick="test_ldap_settings()"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Test LDAP Settings</button></a></p>';
		$ldap = $sheel->admincp->construct_admin_input('registrationldap', HTTPS_SERVER_ADMIN . 'settings/registration/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'attachments') {
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'attachment-manage-storagetype' and isset($sheel->GPC['form']['action'])) {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->admincp->print_action_failed('{_demo_mode_only}', HTTPS_SERVER_ADMIN . 'settings/attachments/');
				exit();
			}
			$sheel->template->meta['areatitle'] = '{_managing_attachment_storage_type}';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_managing_attachment_storage_type}';
			if ($sheel->GPC['form']['action'] == 'rebuildpictures') {
				$sheel->auction_pictures_rebuilder->activate_cron(); // runs $sheel->auction_pictures_rebuilder->process_picture_rebuilder();
				$sheel->admincp->print_action_success('{_pictures_within_attachment_system_rebuilt}', HTTPS_SERVER_ADMIN . 'settings/attachments/');
				exit();
			}
		}
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Photo &amp; Attachments';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Photo &amp; Attachments';
		$sheel->template->fetch('main', 'settings_attachments.html', 1);
		$areanav = 'settings_attachments';
		$currentarea = 'Photos &amp; Attachments';
		$form['totalattachments'] = number_format($sheel->attachment->totalattachments());
		$form['totaldiskspace'] = $sheel->attachment->totaldiskspace();
		$form['storagetype'] = $sheel->attachment->storagetype('type');
		$form['storagetypeaction'] = $sheel->attachment->storagetype('formaction');
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'marketplace/attachments/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_attachment_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('attachmentsystem', HTTPS_SERVER_ADMIN . 'settings/attachments/', '', $buttons);
		$moderation = $sheel->admincp->construct_admin_input('attachmentmoderation', HTTPS_SERVER_ADMIN . 'settings/attachments/');
		$limits = $sheel->admincp->construct_admin_input('attachmentlimit', HTTPS_SERVER_ADMIN . 'settings/attachments/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'shipping') {
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->template->templateregistry['message'] = '{_demo_mode_only}';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
			if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
				$sheel->db->query("
					DELETE FROM " . DB_PREFIX . "shippers
					WHERE shipperid = '" . intval($sheel->GPC['xid']) . "'
					LIMIT 1
				");
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Shipping service deleted', 'A shipping service has been successfully deleted.');
				die(json_encode(array('response' => '1', 'message' => 'Shipping service has been successfully deleted.')));
			} else {
				$sheel->template->templateregistry['message'] = 'This shipping service could not be deleted.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->admincp->print_action_failed('{_demo_mode_only}', HTTPS_SERVER_ADMIN . 'settings/shipping/');
				exit();
			}
			$sheel->GPC['title'] = isset($sheel->GPC['title']) ? $sheel->GPC['title'] : '';
			$sheel->GPC['shipcode'] = isset($sheel->GPC['shipcode']) ? $sheel->GPC['shipcode'] : '';
			$sheel->GPC['domestic'] = isset($sheel->GPC['domestic']) ? intval($sheel->GPC['domestic']) : 0;
			$sheel->GPC['international'] = isset($sheel->GPC['international']) ? intval($sheel->GPC['international']) : 0;
			$sheel->GPC['trackurl'] = isset($sheel->GPC['trackurl']) ? $sheel->db->escape_string($sheel->GPC['trackurl']) : '';
			$sheel->GPC['sort'] = isset($sheel->GPC['sort']) ? $sheel->db->escape_string($sheel->GPC['sort']) : 0;
			$sheel->db->query("
				INSERT INTO " . DB_PREFIX . "shippers
				(shipperid, title, shipcode, domestic, international, carrier, trackurl, sort)
				VALUES(
				NULL,
				'" . $sheel->db->escape_string($sheel->GPC['title']) . "',
				'" . $sheel->db->escape_string($sheel->GPC['shipcode']) . "',
				'" . $sheel->GPC['domestic'] . "',
				'" . $sheel->GPC['international'] . "',
				'" . $sheel->db->escape_string($sheel->GPC['carrier']) . "',
				'" . $sheel->GPC['trackurl'] . "',
				'" . $sheel->GPC['sort'] . "')
			");
			refresh(HTTPS_SERVER_ADMIN . 'settings/shipping/');
			exit();
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->admincp->print_action_failed('{_demo_mode_only}', HTTPS_SERVER_ADMIN . 'settings/shipping/');
				exit();
			}
			$sheel->GPC['international'] = isset($sheel->GPC['international']) ? $sheel->GPC['international'] : array();
			$sheel->GPC['title'] = isset($sheel->GPC['title']) ? $sheel->GPC['title'] : array();
			$sheel->GPC['domestic'] = isset($sheel->GPC['domestic']) ? $sheel->GPC['domestic'] : array();
			$sheel->GPC['carrier'] = isset($sheel->GPC['carrier']) ? $sheel->GPC['carrier'] : array();
			$sheel->GPC['shipcode'] = isset($sheel->GPC['shipcode']) ? $sheel->GPC['shipcode'] : array();
			$sheel->GPC['trackurl'] = isset($sheel->GPC['trackurl']) ? $sheel->GPC['trackurl'] : array();
			$sheel->GPC['sort'] = isset($sheel->GPC['sort']) ? $sheel->GPC['sort'] : array();
			foreach ($sheel->GPC['title'] as $shipperid => $title) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET title = '" . $sheel->db->escape_string($title) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET domestic = '0', international = '0'
			");
			foreach ($sheel->GPC['domestic'] as $shipperid => $value) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET domestic = '" . intval($value) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			foreach ($sheel->GPC['international'] as $shipperid => $value) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET international = '" . intval($value) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			foreach ($sheel->GPC['carrier'] as $shipperid => $title) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET carrier = '" . $sheel->db->escape_string($title) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			foreach ($sheel->GPC['shipcode'] as $shipperid => $title) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET shipcode = '" . $sheel->db->escape_string($title) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			foreach ($sheel->GPC['trackurl'] as $shipperid => $trackurl) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET trackurl = '" . $sheel->db->escape_string($trackurl) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			foreach ($sheel->GPC['sort'] as $shipperid => $sort) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "shippers
					SET sort = '" . $sheel->db->escape_string($sort) . "'
					WHERE shipperid = '" . intval($shipperid) . "'
				");
			}
			refresh(HTTPS_SERVER_ADMIN . 'settings/shipping/');
			exit();
		}
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Shipping &amp; APIs';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Shipping &amp; APIs';
		$sheel->template->fetch('main', 'settings_shipping.html', 1);
		$areanav = 'settings_shipping';
		$currentarea = '{_shipping}';
		$settings = $sheel->admincp->construct_admin_input('shippingsettings', HTTPS_SERVER_ADMIN . 'settings/shipping/');
		$settings .= $sheel->admincp->construct_admin_input('shippingapiservices', HTTPS_SERVER_ADMIN . 'settings/shipping/');
		$sql = $sheel->db->query("
			SELECT shipperid, title, shipcode, domestic, international, carrier, trackurl, sort
			FROM " . DB_PREFIX . "shippers
			ORDER BY sort ASC
		");
		if ($sheel->db->num_rows($sql) > 0) {
			$row_count = 0;
			while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$row['title'] = '<input type="text" name="title[' . $row['shipperid'] . ']" value="' . stripslashes(o($row['title'])) . '" class="draw-input" />';
				$row['shipcode'] = '<input type="text" name="shipcode[' . $row['shipperid'] . ']" value="' . stripslashes(o($row['shipcode'])) . '" class="draw-input" style="text-align:center" />';
				$row['domestic'] = '<div class="draw-input-wrapper draw-input-wrapper--inline"><input type="checkbox" name="domestic[' . $row['shipperid'] . ']" value="1" ' . ($row['domestic'] ? 'checked="checked"' : '') . ' class="draw-checkbox" /><span class="draw-checkbox--styled"></span></div>';
				$row['international'] = '<div class="draw-input-wrapper draw-input-wrapper--inline"><input type="checkbox" name="international[' . $row['shipperid'] . ']" value="1" ' . ($row['international'] ? 'checked="checked"' : '') . ' class="draw-checkbox" /><span class="draw-checkbox--styled"></span></div>';
				$row['carrier'] = '<input type="text" name="carrier[' . $row['shipperid'] . ']" value="' . stripslashes(o($row['carrier'])) . '" class="draw-input" />';
				$row['trackurl'] = '<input type="text" name="trackurl[' . $row['shipperid'] . ']" value="' . stripslashes(o($row['trackurl'])) . '" style="background-color:#ebebeb;color:#555" class="draw-input" />';
				$row['sort'] = '<input type="text" name="sort[' . $row['shipperid'] . ']" value="' . stripslashes(intval($row['sort'])) . '" class="draw-input" style="text-align:center" />';
				$shippers[] = $row;
				$row_count++;
			}
		}
		$row2['title'] = '<input type="text" name="title" value="" class="draw-input" />';
		$row2['shipcode'] = '<input type="text" name="shipcode" value="" class="draw-input" style="text-align:center" placeholder="{_optional}" />';
		$row2['domestic'] = '<div class="draw-input-wrapper draw-input-wrapper--inline"><input type="checkbox" name="domestic" value="1" class="draw-checkbox" checked="checked" /><span class="draw-checkbox--styled"></span></div>';
		$row2['international'] = '<div class="draw-input-wrapper draw-input-wrapper--inline"><input type="checkbox" name="international" value="1" class="draw-checkbox" /><span class="draw-checkbox--styled"></span></div>';
		$row2['carrier'] = '<input type="text" name="carrier" value="" class="draw-input" placeholder="{_optional}" />';
		$row2['trackurl'] = '<input type="text" name="trackurl" value="" style="background-color:#ebebeb;color:#555" class="draw-input" placeholder="{_optional}" />';
		$row2['sort'] = '<input type="text" name="sort" value="10" class="draw-input" style="text-align:center" />';
		$newshipper[] = $row2;
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'escrow') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Escrow';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Escrow';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_escrow';
		$currentarea = '{_escrow}';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'accounting/escrow/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Escrow Manager</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('escrowsystem', HTTPS_SERVER_ADMIN . 'settings/escrow/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'feedback') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Feedback';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Feedback';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_feedback';
		$currentarea = '{_feedback}';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'marketplace/feedback/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Feedback Manager</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('feedback', HTTPS_SERVER_ADMIN . 'settings/feedback/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'listings') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Bulk CSV';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Selling &amp; Bulk CSV';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_listings';
		$currentarea = '{_selling_bulk_csv}';
		$settings = $sheel->admincp->construct_admin_input('globalfiltersrfp', HTTPS_SERVER_ADMIN . 'settings/listings/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bidding') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Bidding';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Bidding';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_bidding';
		$currentarea = 'Bidding Settings';
		$settings = $sheel->admincp->construct_admin_input('productbid', HTTPS_SERVER_ADMIN . 'marketplace/bids/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'pmb') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Private Messages';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Private Messages';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_pmb';
		$currentarea = '{_private_message}';
		$settings = $sheel->admincp->construct_admin_input('globalfilterspmb', HTTPS_SERVER_ADMIN . 'settings/pmb/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'censor') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Censor';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Censor';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_censor';
		$currentarea = '{_censor}';
		$settings = $sheel->admincp->construct_admin_input('globalcensor', HTTPS_SERVER_ADMIN . 'settings/censor/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'blacklist') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Blacklist';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Blacklist';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_blacklist';
		$currentarea = '{_blacklist}';
		$settings = $sheel->admincp->construct_admin_input('globalblacklist', HTTPS_SERVER_ADMIN . 'settings/blacklist/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'categories') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Categories';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Categories';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_categories';
		$currentarea = '{_categories}';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'categories/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_category_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('globalcategorysettings', HTTPS_SERVER_ADMIN . 'settings/categories/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'seo') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; SEO';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; SEO';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_seo';
		$currentarea = 'SEO';
		$settings = $sheel->admincp->construct_admin_input('globalseo', HTTPS_SERVER_ADMIN . 'setttings/seo/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'search') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Search';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Search';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_search';
		$currentarea = '{_search}';
		$settings = $sheel->admincp->construct_admin_input('search', HTTPS_SERVER_ADMIN . 'settings/search/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'security') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Security';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Security';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_security';
		$currentarea = '{_security}';
		$settings = $sheel->admincp->construct_admin_input('globalsecurity', HTTPS_SERVER_ADMIN . 'settings/security/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'distance') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Distance &amp; GeoData';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Distance &amp; GeoData';
		$sheel->template->fetch('main', 'settings_distance.html', 1);
		$areanav = 'settings_distance';
		$currentarea = '{_distance}';
		$settings = $sheel->admincp->construct_admin_input('globalserverdistanceapi', HTTPS_SERVER_ADMIN . 'settings/distance/');
		$installedcountries = $sheel->distance->fetch_installed_countries();
		$sheel->template->parse_loop('main', array('installedcountries' => $installedcountries), false);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'session') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Sessions';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Sessions';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_session';
		$currentarea = '{_session}';
		$settings = $sheel->admincp->construct_admin_input('globalserversession', HTTPS_SERVER_ADMIN . 'settings/session/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'license') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; License';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; License';
		$sheel->template->fetch('main', 'settings_license.html', 1);
		$areanav = 'settings_license';
		$currentarea = '{_license}';
		$license['title'] = $sheel->config['license_tier'] . ' (' . $sheel->config['license_type'] . ')';
		$license['status'] = (($sheel->show['EXPIRED_LICENSE']) ? '{_expired}' : '{_active}');
		if ($sheel->config['license_suspended']) {
			$license['status'] = '<span class="type--danger">{_suspended}</span>';
		}
		if ($sheel->config['license_payment_in_process']) {
			$license['status'] = 'Processing Payment';
		}
		$license['expiredate'] = $sheel->common->print_date($sheel->config['license_expiry'], 'F j, Y', 0, 0);
		$license['licensekey'] = LICENSEKEY;
		$license['platformtype'] = '{_' . $sheel->config['platform_type'] . '}';
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'license/renewal') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; License';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; License';
		$sheel->template->fetch('main', 'settings_license_renewal.html', 1);
		$areanav = 'settings_license_renewal';
		$currentarea = '{_license_renewal}';
		$license['title'] = $sheel->config['license_tier'] . ' (' . $sheel->config['license_type'] . ')';
		$license['status'] = (($sheel->show['EXPIRED_LICENSE']) ? '{_expired}' : '{_active}');
		$license['expiredate'] = $sheel->common->print_date($sheel->config['license_expiry'], 'F j, Y', 0, 0);
		$license['licensekey'] = LICENSEKEY;
		$license['platformtype'] = '{_' . $sheel->config['platform_type'] . '}';
		$license['tier'] = strtolower($sheel->config['license_tier']);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'billing/create') { // trial user create new billing cycle
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; License';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; License';
		$sheel->template->fetch('main', 'settings_license_billing_update.html', 1);
		$areanav = 'settings_license_renewal';
		$currentarea = 'Billing Creation';
		$form['license'] = ((isset($sheel->GPC['license']) and !empty($sheel->GPC['license'])) ? $sheel->GPC['license'] : '1GB');
		$form['platform'] = ((isset($sheel->GPC['platform']) and !empty($sheel->GPC['platform'])) ? $sheel->GPC['platform'] : 'singleseller');
		$license['licensekey'] = LICENSEKEY;
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'billing/update') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; License';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; License';
		$sheel->template->fetch('main', 'settings_license_billing_update.html', 1);
		$areanav = 'settings_license_renewal';
		$currentarea = 'Billing Update';
		$form['license'] = ((isset($sheel->GPC['license']) and !empty($sheel->GPC['license'])) ? $sheel->GPC['license'] : '1GB');
		$form['platform'] = ((isset($sheel->GPC['platform']) and !empty($sheel->GPC['platform'])) ? $sheel->GPC['platform'] : 'singleseller');
		$form['profileid'] = ((isset($sheel->GPC['profileid']) and !empty($sheel->GPC['profileid'])) ? $sheel->GPC['profileid'] : '');
		$license['licensekey'] = LICENSEKEY;
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'license/plans') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; License';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; License';
		$sheel->template->fetch('main', 'settings_license_plans.html', 1);
		$areanav = 'settings_license_plans';
		$currentarea = 'Choose a plan';
		$license['title'] = $sheel->config['license_tier'] . ' (' . $sheel->config['license_type'] . ')';
		$license['status'] = (($sheel->show['EXPIRED_LICENSE']) ? '{_expired}' : '{_active}');
		$license['expiredate'] = $sheel->common->print_date($sheel->config['license_expiry'], 'F j, Y', 0, 0);
		$license['licensekey'] = LICENSEKEY;
		$license['platformtype'] = '{_' . $sheel->config['platform_type'] . '}';
		$form['profileid'] = ((isset($sheel->GPC['profileid']) and !empty($sheel->GPC['profileid'])) ? $sheel->GPC['profileid'] : '');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'updates') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Updates';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Updates';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_updates';
		$sheel->template->fetch('main', 'settings_updates.html', 1);
		$areanav = 'settings_updates';
		$currentarea = '{_updates}';
		$form['latestversion'] = $sheel->latest_version($sheel->config['versionsurl'] . '/sheel/');
		$form['lastupdate'] = ((empty($sheel->config['last_automatic_update'])) ? '{_never}' : $sheel->common->print_date($sheel->config['last_automatic_update'], 'l F j, Y g:i A', 0, 0));
		$form['lastbackup'] = ((empty($sheel->config['last_automatic_backup'])) ? '{_never}' : $sheel->common->print_date($sheel->config['last_automatic_backup'], 'l F j, Y g:i A', 0, 0));
		$form['lastupdateattempt'] = ((empty($sheel->config['last_automatic_update_attempt'])) ? '{_never}' : $sheel->common->print_date($sheel->config['last_automatic_update_attempt'], 'l F j, Y g:i A', 0, 0));
		$form['lastupdateresponse'] = ((empty($sheel->config['last_automatic_update_response'])) ? '{_not_available}' : o($sheel->config['last_automatic_update_response']));
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'serverinfo') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Server Info';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Server Info';
		$sheel->template->fetch('main', 'settings_serverinfo.html', 1);
		$areanav = 'settings_serverinfo';
		$currentarea = '{_server_info}';
		$interface = 'enp0s3'; // eth0, eth1, etc.

		// cpu stat
		$prevVal = @shell_exec("cat /proc/stat");
		$prevArr = explode(' ', trim($prevVal));
		$prevTotal = $prevArr[2] + $prevArr[3] + $prevArr[4] + $prevArr[5];
		$prevIdle = $prevArr[5];
		usleep(0.15 * 1000000);

		$val = @shell_exec("cat /proc/stat");
		$arr = explode(' ', trim($val));
		$total = $arr[2] + $arr[3] + $arr[4] + $arr[5];
		$idle = $arr[5];
		$intervalTotal = intval($total - $prevTotal);

		$stat2 = array();
		$stat['cpu'] = intval(100 * (($intervalTotal - ($idle - $prevIdle)) / $intervalTotal));
		$cpu_result = @shell_exec("cat /proc/cpuinfo | grep model\ name");
		$stat['cpu_model'] = strstr($cpu_result, "\n", true);
		$stat['cpu_model'] = str_replace("model name	: ", "", $stat['cpu_model']);

		// memory stat
		$stat['mem_percent'] = round(@shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"), 2);

		$mem_result = @shell_exec("cat /proc/meminfo | grep MemTotal");
		$stat['mem_total'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
		$mem_result = @shell_exec("cat /proc/meminfo | grep MemFree");
		$stat['mem_free'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
		$stat['mem_used'] = $stat['mem_total'] - $stat['mem_free'];

		// hdd stat
		$stat['hdd_free'] = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
		$stat['hdd_total'] = round(disk_total_space("/") / 1024 / 1024 / 1024, 2);
		$stat['hdd_used'] = $stat['hdd_total'] - $stat['hdd_free'];
		$stat['hdd_percent'] = round(sprintf('%.2f', ($stat['hdd_used'] / $stat['hdd_total']) * 100), 2);

		// software
		function phpinfo2array()
		{
			$entitiesToUtf8 = function ($input) {
				return preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
					return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }
					, $input);
			};
			$plainText = function ($input) use ($entitiesToUtf8) {
				return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
			};
			$titlePlainText = function ($input) use ($plainText) {
				return '# ' . $plainText($input);
			};
			ob_start();
			phpinfo(-1);
			$phpinfo = array('phpinfo' => array());
			// strip everything after the <h1>Configuration</h1> tag (other h1's)
			if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
				return array();
			}
			$input = $matches[1];
			$matches = array();
			if (
				preg_match_all(
					'#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' .
					'(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
					$input,
					$matches,
					PREG_SET_ORDER
				)
			) {
				foreach ($matches as $match) {
					$fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
					if (strlen($match[1])) {
						$phpinfo[$match[1]] = array();
					} else if (isset($match[3])) {
						$keys1 = array_keys($phpinfo);
						$phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
					} else {
						$keys1 = array_keys($phpinfo);
						$phpinfo[end($keys1)][] = $fn($match[2]);
					}
				}
			}
			return $phpinfo;
		}
		$stat2 = phpinfo2array();
		$stat2['phpinfo']['configure'] = ((isset($stat2['phpinfo']['Configure Command'])) ? $stat2['phpinfo']['Configure Command'] : 'n/a');
		$stat2['phpinfo']['serverapi'] = ((isset($stat2['phpinfo']['Server API'])) ? $stat2['phpinfo']['Server API'] : 'n/a');
		$stat2['phpinfo']['webserver'] = ((isset($stat2['Environment']['SERVER_SOFTWARE'])) ? $stat2['Environment']['SERVER_SOFTWARE'] : 'n/a');
		$stat2['phpinfo']['database'] = 'MySQL v' . MYSQL_VERSION . ' <span class="type--subdued">(' . MYSQL_TYPE . ')</span>';
		$stat2['phpinfo']['current_sql_version'] = $sheel->config['current_sql_version'];
		$stat2['phpinfo']['current_version'] = $sheel->config['current_version'];
		$stat2['phpinfo']['current_build'] = SVNVERSION;
		$extensions = get_loaded_extensions();
		$stat2['phpinfo']['extensions'] = "<div>";
		foreach ($extensions as $extension) {
			$stat2['phpinfo']['extensions'] .= "<div class=\"sb badge badge--complete\">$extension</div> ";
		}
		$stat2['phpinfo']['extensions'] .= "</div>";
		$sheel->template->parse_hash('main', array('hardware' => $stat, 'software' => $stat2['phpinfo']));
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'diagnosis') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Diagnosis';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Diagnosis';
		$sheel->template->fetch('main', 'settings_diagnosis.html', 1);
		$areanav = 'settings_diagnosis';
		$currentarea = '{_app_diagnosis}';
		$files = array();
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'scan') {
			include_once(DIR_TMP . 'checkup/filelist.php');
			$files = array();
			foreach ($sheel_md5 as $folder => $filesarray) {
				$x['file'] = $folder;
				$x['md5'] = '';
				$x['passed'] = '';
				$files[] = $x;
				foreach ($filesarray as $file => $md5) {
					$y['md5'] = $md5;
					$productfilename = mb_substr(DIR_SERVER_ROOT, 0, -1) . $folder . '/' . $file;
					$y['md5vs'] = @md5_file($productfilename);
					if ($y['md5vs'] != $y['md5']) {
						$y['md5'] = '<div class="sl">' . $y['md5'] . '</div><div class="type--danger">' . $y['md5vs'] . '</div>';
						$y['file'] = '<div class="sl type--danger">' . $file . '</div>';
						$y['passed'] = '<span class="badge badge--critical" style="min-width:30px">Fail</span>';
					} else {
						$y['md5'] = $md5;
						$y['file'] = '<div class="sl">' . $file . '</div>';
						$y['passed'] = '<span class="badge badge--success" style="min-width:30px">Pass</span>';
					}
					$files[] = $y;
				}
			}
			$sheel->template->parse_loop('main', array('files' => $files), false);
		}
	} else {
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_general';
		$currentarea = '{_general}';
		$settings = $sheel->admincp->construct_admin_input('globalserversettings', 'settings/');
	}
;	$vars = array(
		'sidenav' => (isset($sidenav) ? $sidenav : ''),
		'areanav' => (isset($areanav) ? $areanav : ''),
		'currentarea' => (isset($currentarea) ? $currentarea : ''),
		'url' => $_SERVER['REQUEST_URI'],
		'settings' => (isset($settings) ? $settings : ''),
		'moderation' => (isset($moderation) ? $moderation : ''),
		'api' => (isset($api) ? $api : ''),
		'ldap' => (isset($ldap) ? $ldap : ''),
		'upsell' => (isset($upsell) ? $upsell : ''),
		'limits' => (isset($limits) ? $limits : ''),
		'id' => (isset($sheel->GPC['id']) ? $sheel->GPC['id'] : ''),
		'error' => ((isset($error) and !empty($error)) ? $error : ''),
		
	);

	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage,'form' => (isset($form) ? $form : array())));
	$sheel->template->pprint('main', $vars);
	exit();
} else {
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode('/admin/settings/'));
	exit();
}
?>