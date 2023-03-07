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
		'vendor/chartist',
		'vendor/growl',
		'vendor/inputtags'
	),
	'footer' => array(
		'admin_payment',
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
			$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Marketplace payment settings saved', 'Marketplace payment settings were successfully saved.');
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
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'accounting/currency/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_currency_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('globalserverlocalecurrency', HTTPS_SERVER_ADMIN . 'settings/currency/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'invoice') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Invoice &amp; Transactions';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Invoice &amp; Transactions';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_invoice';
		$currentarea = '{_invoice}';
		$settings = $sheel->admincp->construct_admin_input('invoicesystem', HTTPS_SERVER_ADMIN . 'settings/invoice/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'payment') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Payments &amp; APIs';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Payments &amp; APIs';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_payment_settings';
		$sheel->template->fetch('main', 'settings_payments.html', 1);
		$areanav = 'settings_payment';
		$currentarea = 'Payment &amp; Gateway';
		$settings = $sheel->admincp->construct_admin_input('defaultgateway', HTTPS_SERVER_ADMIN . 'settings/payment/', '', '', 'payment_groups', 'payment_configuration');
		$form = array(
			'name' => '',
			'number' => '',
			'swift' => '',
			'sort' => '100',
			'visible' => '1',
			'company_name' => '',
			'company_address' => '',
			'fee' => '',
			'custom_notes' => '',
			'phone' => '',
			'routing' => '',
			'iban' => ''
		);
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'manualpayment') {
				if ($sheel->show['ADMINCP_TEST_MODE']) {
					$sheel->template->templateregistry['message'] = '{_demo_mode_only}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
				if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
					$varname = $sheel->db->fetch_field(DB_PREFIX . "payment_methods", "id = '" . intval($sheel->GPC['xid']) . "'", "title");
					$sql = $sheel->db->query("
						SELECT id, paymethod
						FROM " . DB_PREFIX . "payment_profiles
					", 0, null, __FILE__, __LINE__);
					if ($sheel->db->num_rows($sql) > 0 and !empty($varname)) {
						while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
							if (!empty($res['paymethod']) and $sheel->is_serialized($res['paymethod'])) {
								if (strchr($res['paymethod'], $varname)) { // seller is using this offline method..
									$array = unserialize($res['paymethod']);
									$newarray = $emaillisting = array();
									foreach ($array as $paymethod) {
										if ($paymethod != '' and $paymethod != $varname) {
											$newarray[] = $paymethod;
										}
									}
									if (count($newarray) > 0) {
										$newarray = serialize($newarray);
										$sheel->db->query("
											UPDATE " . DB_PREFIX . "payment_profiles
											SET paymethod = '" . $sheel->db->escape_string($newarray) . "',
											updated = '" . DATETIME24H . "'
											WHERE id = '" . $res['id'] . "'
										", 0, null, __FILE__, __LINE__);
									} else { // blank payment methods now for listing....(seller only using offline payment methods) email auction owner to update his listing?
										$sheel->db->query("
											UPDATE " . DB_PREFIX . "payment_profiles
											SET filter_offline = '0',
											paymethod = 'a:0:{}',
											updated = '" . DATETIME24H . "'
											WHERE id = '" . $res['id'] . "'
										", 0, null, __FILE__, __LINE__);
										$emaillisting[] = $res['id'];
									}
								}
							}
						}
					}
					if (isset($emaillisting) and count($emaillisting) > 0) {
						foreach ($emaillisting as $projectid) {
							// TODO: email listing owners that they should update payment methods or risk not getting paid
							// ..
						}
					}
					$sheel->db->query("
						DELETE FROM " . DB_PREFIX . "payment_methods
						WHERE id = '" . intval($sheel->GPC['xid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$sheel->db->query("
						DELETE FROM " . DB_PREFIX . "language_phrases
						WHERE varname = '" . $sheel->db->escape_string($varname) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Payment method deleted', 'A custom payment method has been successfully deleted.');
					die(json_encode(array('response' => '1', 'message' => 'A payment method has been successfully deleted.')));
				} else {
					$sheel->template->templateregistry['message'] = 'This payment method could not be deleted.';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'addmanualpayment') {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->admincp->print_action_failed('{_demo_mode_only}', HTTPS_SERVER_ADMIN . 'settings/payment/');
				exit();
			}
			$varname = str_replace(' ', '_', $sheel->GPC['form']['title']);
			$varname = preg_replace("/[^a-zA-Z_]+/", "", $varname);
			$varname = (mb_substr($varname, 0, 1) == '_') ? $varname : '_' . $varname;
			$varname = mb_strtolower($varname);
			$text = (isset($sheel->GPC['form']['title']) ? $sheel->GPC['form']['title'] : '');
			if (!empty($text)) { // add new payment method
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "payment_methods
					(id, title, sort)
					VALUES(
					NULL,
					'" . $sheel->db->escape_string($varname) . "',
					'100')
				", 0, null, __FILE__, __LINE__);
				$query = $sheel->db->query("
					SELECT text_" . $_SESSION['sheeldata']['user']['slng'] . " AS text
					FROM " . DB_PREFIX . "language_phrases
					WHERE varname = '" . $sheel->db->escape_string($varname) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($query) == 0) {
					$field = $value = '';
					$sql_languages = $sheel->db->query("
						SELECT languagecode
						FROM " . DB_PREFIX . "language
					", 0, null, __FILE__, __LINE__);
					while ($res = $sheel->db->fetch_array($sql_languages, DB_ASSOC)) {
						$field .= ", text_" . mb_substr($res['languagecode'], 0, 3);
						$value .= ", '" . $sheel->db->escape_string($text) . "'";
					}
					$sheel->db->query("
						INSERT INTO " . DB_PREFIX . "language_phrases
						(phraseid, phrasegroup, varname, text_original" . $field . ")
						VALUES(
						NULL,
						'main',
						'" . $sheel->db->escape_string($varname) . "',
						'" . $sheel->db->escape_string($text) . "'
						" . $value . ")
					", 0, null, __FILE__, __LINE__);
				}
				if (isset($sheel->GPC['bulkemail']) and $sheel->GPC['bulkemail']) {
					$query_seller = $sheel->db->query("
						SELECT u.first_name, u.email, l.languagecode
						FROM " . DB_PREFIX . "projects AS p
						INNER JOIN " . DB_PREFIX . "users AS u ON p.user_id = u.user_id
						INNER JOIN " . DB_PREFIX . "language AS l ON l.languageid = u.languageid
						WHERE p.status = 'open'
						GROUP BY p.user_id
					", 0, null, __FILE__, __LINE__);
					$number_seller = $sheel->db->num_rows($query_seller);
					if ($number_seller > 0) {
						$text = str_replace('_', ' ', $varname);
						$text = ucfirst(ltrim($text));
						while ($seller_var = $sheel->db->fetch_array($query_seller, DB_ASSOC)) {
							$sheel->email->get('notify_new_payment_module');
							$sheel->email->mail = $seller_var['email'];
							$sheel->email->slng = mb_substr($seller_var['languagecode'], 0, 3);
							$sheel->email->get('notify_new_payment_module');
							$sheel->email->set(
								array(
									'{{firstname}}' => ucwords($seller_var['first_name']),
									'{{site_name}}' => SITE_NAME,
									'{{paymenttype}}' => $text,
									'{{http_server}}' => HTTP_SERVER
								)
							);
							$sheel->email->send();
						}
					}
				}
			}
			if (isset($sheel->GPC['title']) and !empty($sheel->GPC['title'])) { // update payment method phrases
				foreach ($sheel->GPC['title'] as $varname => $title) {
					if (!empty($title)) {
						$query = $sheel->db->query("
							SELECT text_" . $_SESSION['sheeldata']['user']['slng'] . " AS text
							FROM " . DB_PREFIX . "language_phrases
							WHERE varname = '" . $sheel->db->escape_string($varname) . "'
						", 0, null, __FILE__, __LINE__);
						if ($sheel->db->num_rows($query) > 0) {
							$sql_languages = $sheel->db->query("SELECT languagecode FROM " . DB_PREFIX . "language", 0, null, __FILE__, __LINE__);
							while ($res = $sheel->db->fetch_array($sql_languages, DB_ASSOC)) {
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "language_phrases
									SET text_" . mb_substr($res['languagecode'], 0, 3) . " = '" . $sheel->db->escape_string($title) . "'
									WHERE varname = '" . $sheel->db->escape_string($varname) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
							}
						}
					}
				}
			}
			refresh(HTTPS_SERVER_ADMIN . 'settings/payment/');
			exit();
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'updategateway') {
			foreach ($sheel->GPC['config'] as $key => $value) {
				if (isset($key) and !empty($key)) {
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "payment_configuration
						SET value = '" . $sheel->db->escape_string(trim($value)) . "',
						sort = '" . intval($sheel->GPC['sort'][$key]) . "'
						WHERE name = '" . $sheel->db->escape_string($key) . "'
							AND configgroup = '" . $sheel->db->escape_string($sheel->GPC['module']) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			refresh(HTTPS_SERVER_ADMIN . 'settings/payment/');
			exit();
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bank-deposit-accounts') {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
				if ($sheel->show['ADMINCP_TEST_MODE']) {
					$sheel->admincp->print_action_failed('{_demo_mode_only}', HTTPS_SERVER_ADMIN . 'settings/payment/');
					exit();
				}
				if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
					$name = isset($sheel->GPC['bank']['name']) ? $sheel->db->escape_string($sheel->GPC['bank']['name']) : '';
					$phone = isset($sheel->GPC['bank']['phone']) ? $sheel->db->escape_string($sheel->GPC['bank']['phone']) : '';
					$number = isset($sheel->GPC['bank']['number']) ? $sheel->db->escape_string($sheel->GPC['bank']['number']) : '';
					$swift = isset($sheel->GPC['bank']['swift']) ? $sheel->db->escape_string($sheel->GPC['bank']['swift']) : '';
					$routing = isset($sheel->GPC['bank']['routing']) ? $sheel->db->escape_string($sheel->GPC['bank']['routing']) : '';
					$iban = isset($sheel->GPC['bank']['iban']) ? $sheel->db->escape_string($sheel->GPC['bank']['iban']) : '';
					$company_name = isset($sheel->GPC['bank']['company_name']) ? $sheel->db->escape_string($sheel->GPC['bank']['company_name']) : '';
					$company_address = isset($sheel->GPC['bank']['company_address']) ? $sheel->db->escape_string($sheel->GPC['bank']['company_address']) : '';
					$custom_notes = isset($sheel->GPC['bank']['custom_notes']) ? $sheel->db->escape_string($sheel->GPC['bank']['custom_notes']) : '';
					$fee = isset($sheel->GPC['bank']['fee']) ? $sheel->db->escape_string($sheel->GPC['bank']['fee']) : '0.00';
					$visible = 1;
					$sort = 100;
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "deposit_offline_methods
						SET name = '" . $sheel->db->escape_string($name) . "',
						phone = '" . $sheel->db->escape_string($phone) . "',
						number = '" . $sheel->db->escape_string($number) . "',
						swift = '" . $sheel->db->escape_string($swift) . "',
						routing = '" . $sheel->db->escape_string($routing) . "',
						iban = '" . $sheel->db->escape_string($iban) . "',
						company_name = '" . $sheel->db->escape_string($company_name) . "',
						company_address = '" . $sheel->db->escape_string($company_address) . "',
						custom_notes = '" . $sheel->db->escape_string($custom_notes) . "',
						fee = '" . $sheel->db->escape_string($fee) . "',
						visible = '" . $sheel->db->escape_string($visible) . "',
						sort = '" . $sheel->db->escape_string($sort) . "'
						WHERE id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				} else {
					$name = isset($sheel->GPC['bank']['name']) ? $sheel->db->escape_string($sheel->GPC['bank']['name']) : '';
					$phone = isset($sheel->GPC['bank']['phone']) ? $sheel->db->escape_string($sheel->GPC['bank']['phone']) : '';
					$number = isset($sheel->GPC['bank']['number']) ? $sheel->db->escape_string($sheel->GPC['bank']['number']) : '';
					$swift = isset($sheel->GPC['bank']['swift']) ? $sheel->db->escape_string($sheel->GPC['bank']['swift']) : '';
					$routing = isset($sheel->GPC['bank']['routing']) ? $sheel->db->escape_string($sheel->GPC['bank']['routing']) : '';
					$iban = isset($sheel->GPC['bank']['iban']) ? $sheel->db->escape_string($sheel->GPC['bank']['iban']) : '';
					$company_name = isset($sheel->GPC['bank']['company_name']) ? $sheel->db->escape_string($sheel->GPC['bank']['company_name']) : '';
					$company_address = isset($sheel->GPC['bank']['company_address']) ? $sheel->db->escape_string($sheel->GPC['bank']['company_address']) : '';
					$custom_notes = isset($sheel->GPC['bank']['custom_notes']) ? $sheel->db->escape_string($sheel->GPC['bank']['custom_notes']) : '';
					$fee = isset($sheel->GPC['bank']['fee']) ? $sheel->db->escape_string($sheel->GPC['bank']['fee']) : '0.00';
					$visible = 1;
					$sort = 100;
					$sheel->db->query("
						INSERT INTO " . DB_PREFIX . "deposit_offline_methods
						(id, name, phone, number, swift, routing, iban, company_name, company_address, custom_notes, fee, visible, sort)
						VALUES(
						NULL,
						'" . $name . "',
						'" . $phone . "',
						'" . $number . "',
						'" . $swift . "',
						'" . $routing . "',
						'" . $iban . "',
						'" . $company_name . "',
						'" . $company_address . "',
						'" . $custom_notes . "',
						'" . $fee . "',
						'" . $visible . "',
						'" . $sort . "')
					", 0, null, __FILE__, __LINE__);
				}
				refresh(HTTPS_SERVER_ADMIN . 'settings/payment/');
				exit();
			} else if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'delete') {
				if ($sheel->show['ADMINCP_TEST_MODE']) {
					$sheel->template->templateregistry['message'] = '{_demo_mode_only}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
				if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
					$sheel->db->query("
						DELETE FROM " . DB_PREFIX . "deposit_offline_methods
						WHERE id = '" . intval($sheel->GPC['xid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Bank deposit account deleted', 'A bank deposit account has been successfully deleted.');
					die(json_encode(array('response' => '1', 'message' => 'A bank deposit account has been successfully deleted.')));
				} else {
					$sheel->template->templateregistry['message'] = 'This bank deposit account could not be deleted.';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'update' and isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
				$form = array();
				$sql = $sheel->db->query("
					SELECT name, phone, number, swift, routing, iban, sort, visible, company_name, company_address, fee, custom_notes
					FROM " . DB_PREFIX . "deposit_offline_methods
					WHERE id = '" . intval($sheel->GPC['id']) . "'
					ORDER BY sort ASC
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0) {
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
						$form['name'] = o($row['name']);
						$form['phone'] = o($row['phone']);
						$form['number'] = o($row['number']);
						$form['swift'] = o($row['swift']);
						$form['routing'] = o($row['routing']);
						$form['iban'] = o($row['iban']);
						$form['sort'] = o($row['sort']);
						$form['visible'] = o($row['visible']);
						$form['company_name'] = o($row['company_name']);
						$form['company_address'] = o($row['company_address']);
						$form['fee'] = o($row['fee']);
						$form['custom_notes'] = o($row['custom_notes']);
					}
				}
			}
		}
		// manual payment types
		$sheel->show['no_paytypes_rows'] = true;
		$manualpayments = array();
		$sql = $sheel->db->query("
			SELECT id, title
			FROM " . DB_PREFIX . "payment_methods
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sql) > 0) {
			while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$row['titleplain'] = '{' . stripslashes(o($row['title'])) . '}';
				$row['totalcount'] = number_format($sheel->admincp_paymodules->count_offline_payment_types($row['id']));
				$row['title'] = '<input type="text" name="title[' . $row['title'] . ']" value="{' . stripslashes(o($row['title'])) . '}" class="draw-input" />';
				$row['action'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'delete\', \'Delete this payment method?\', \'Are you sure you want to delete this payment method? ' . $row['totalcount'] . ' sellers are currently using this method within their listings.\', \'' . $row['id'] . '\', 1, \'manualpayment\', \'\')" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
				$manualpayments[] = $row;
			}
			$sheel->show['no_paytypes_rows'] = false;
		}
		$merchantgateways = $merchantgatewayoptions = array();
		$merchantgatewayoptions[''] = '{_select_merchant_gateway} &ndash;';
		$sql = $sheel->db->query("
			SELECT groupname
			FROM " . DB_PREFIX . "payment_groups
			WHERE moduletype = 'gateway'
			ORDER BY groupname ASC
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sql) > 0) {
			while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$row['blurb'] = '{_paygroup_' . $row['groupname'] . '}';
				//$row['blurb'] .= '<br /><p><a href="javascript:;" onclick="test_payment_settings(\'' . $row['groupname'] . '\')"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Test {_' . $row['groupname'] . '} API Settings</button></a></p>';
				$row['input'] = $sheel->admincp_paymodules->construct_paymodules_input($row['groupname'], HTTPS_SERVER_ADMIN . 'settings/payment/');
				$merchantgatewayoptions[$row['groupname']] = '{_' . $row['groupname'] . '}' . (($sheel->config['use_internal_gateway'] == $row['groupname']) ? ' [{_active}]' : '');
				$merchantgateways[] = $row;
			}
		}
		$paymentgateways = $paymentgatewayoptions = array();
		$paymentgatewayoptions[''] = '{_select_payment_gateway} &ndash;';
		$sql = $sheel->db->query("
			SELECT groupname
			FROM " . DB_PREFIX . "payment_groups
			WHERE (moduletype = 'ipn' OR moduletype = 'local')
			ORDER BY groupname ASC
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sql) > 0) {
			while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$row['blurb'] = '{_paygroup_' . $row['groupname'] . '}';
				$row['input'] = $sheel->admincp_paymodules->construct_paymodules_input($row['groupname'], HTTPS_SERVER_ADMIN . 'settings/payment/');
				if ($row['groupname'] == 'owner_bank_info') {
					$paymentgatewayoptions[$row['groupname']] = '{_bank_deposit_accounts_acp}';
					$row['actions'] = '<input type="button" name="addaccount" value="{_add_deposit_account}" class="btn btn-primary" onclick="location.href=\'' . HTTPS_SERVER_ADMIN . 'settings/payment/bank-deposit-accounts/add/\'">';
				} else {
					$paymentgatewayoptions[$row['groupname']] = (($row['groupname'] == 'bank') ? '{_bank_withdraw_accounts}' : '{_' . $row['groupname'] . '}');
					$row['actions'] = '<!--<button type="submit" class="btn js-btn-primary js-btn-loadable has-loading">Run Test Transaction</button>--><input type="submit" name="commit" value="{_save}" class="btn btn-primary">';
				}
				$paymentgateways[] = $row;
			}
		}
		$form['merchantgatewaypulldown'] = $sheel->construct_pulldown('gateway_provider', 'form[gateway_provider]', $merchantgatewayoptions, '', 'class="draw-select" onchange="if (fetch_js_object(\'gateway_provider\').options[fetch_js_object(\'gateway_provider\').selectedIndex].value != \'\'){fetch_js_object(\'payment-panel\').innerHTML=fetch_js_object(fetch_js_object(\'gateway_provider\').options[fetch_js_object(\'gateway_provider\').selectedIndex].value).innerHTML}else{fetch_js_object(\'payment-panel\').innerHTML=\'\';}"');
		$form['paymentgatewaypulldown'] = $sheel->construct_pulldown('payment_provider', 'form[payment_provider]', $paymentgatewayoptions, '', 'class="draw-select" onchange="if (fetch_js_object(\'payment_provider\').options[fetch_js_object(\'payment_provider\').selectedIndex].value != \'\'){fetch_js_object(\'payment-panel2\').innerHTML=fetch_js_object(fetch_js_object(\'payment_provider\').options[fetch_js_object(\'payment_provider\').selectedIndex].value).innerHTML}else{fetch_js_object(\'payment-panel2\').innerHTML=\'\';}"');
		$sheel->template->parse_loop('main', array('manualpayments' => $manualpayments, 'merchantgateways' => $merchantgateways, 'paymentgateways' => $paymentgateways));
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'tax') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Tax';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Tax';
		$sheel->template->fetch('main', 'settings_tax.html', 1);
		$areanav = 'settings_tax';
		$currentarea = '{_tax}';
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
				$sheel->GPC['invoicetypes'] = (!empty($sheel->GPC['taxtype'])) ? serialize($sheel->GPC['taxtype']) : '';
				if (empty($sheel->GPC['country'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_country_name_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				$countryid = intval($sheel->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'", "locationid"));
				if ($countryid == 0) {
					$sheel->admincp->print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['form']['taxlabel'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_title_name_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['state'])) {
					$sheel->GPC['state'] = '';
				}
				if (empty($sheel->GPC['city'])) {
					$sheel->GPC['city'] = '';
				}
				if (empty($sheel->GPC['form']['amount'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_amount_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['form']['currencyid'])) {
					$sheel->admincp->print_action_failed('Please select the default currency used for this tax type.', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				$entirecountry = ((isset($sheel->GPC['form']['entirecountry']) and $sheel->GPC['form']['entirecountry'] == 'true') ? 1 : 0);
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "taxes
					(taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry, currencyid)
					VALUES(
					NULL,
					'" . $sheel->db->escape_string($sheel->GPC['form']['taxlabel']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['state']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['country']) . "',
					'" . intval($countryid) . "',
					'" . $sheel->db->escape_string($sheel->GPC['city']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['amount']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['invoicetypes']) . "',
					'" . intval($entirecountry) . "',
					'" . intval($sheel->GPC['form']['currencyid']) . "')
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER_ADMIN . 'settings/tax/');
				exit();
			}
			$form['taxlabel'] = '';
			$form['amount'] = '';
			$form['country_js_pulldown'] = $sheel->common_location->construct_country_pulldown(0, '', 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', true, false, '', 0, 'city', 'cityid');
			$form['state_js_pulldown'] = '<div id="stateid">' . $sheel->common_location->construct_state_pulldown(0, '', 'state', false, true, 0, 'draw-select', 0, 'city', 'cityid') . '</div>';
			$form['city_js_pulldown'] = '<div id="cityid">' . $sheel->common_location->construct_city_pulldown('', 'city', '', false, true, 'draw-select') . '</div>';
			$form['currencypulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', '');
			$form['invoicetaxtype'] = '';
			$form['invoicetaxtype'] .= '<label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" /> {_member_subscription_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" /> {_escrow_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" /> {_auction_enhancement_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" /> {_insertion_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" /> {_final_value_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="buyerpremiumfee"><input type="checkbox" name="taxtype[buyerpremiumfee]" id="fbuyerpremiumfee" value="1" /> Buyer\'s premium fees</label>';
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update' and isset($sheel->GPC['taxid']) and $sheel->GPC['taxid'] > 0) {
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
				$entirecountry = ((isset($sheel->GPC['form']['entirecountry']) and $sheel->GPC['form']['entirecountry'] == 'true') ? 1 : 0);
				if ($entirecountry and empty($sheel->GPC['state'])) {
					$sheel->GPC['state'] = '';
				}
				if ($entirecountry and empty($sheel->GPC['city'])) {
					$sheel->GPC['city'] = '';
				}
				if (!empty($sheel->GPC['taxtype'])) {
					$sheel->GPC['invoicetypes'] = serialize($sheel->GPC['taxtype']);
				} else {
					$sheel->GPC['invoicetypes'] = '';
				}
				if (empty($sheel->GPC['country'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_country_name_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				$countryid = (int) $sheel->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'", "locationid");
				if ($countryid == 0) {
					$sheel->admincp->print_action_failed('{_there_is_no_country_with_this_name_in_the_system_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['form']['taxlabel'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_title_name_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['state']) and $entirecountry == 0) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_state_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['form']['amount'])) {
					$sheel->admincp->print_action_failed('{_you_must_enter_a_tax_zone_amount_please_retry}', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				if (empty($sheel->GPC['form']['currencyid'])) {
					$sheel->admincp->print_action_failed('Please select the default currency used for this tax type.', HTTPS_SERVER_ADMIN . 'settings/tax/');
					exit();
				}
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "taxes
					SET taxlabel = '" . $sheel->db->escape_string($sheel->GPC['form']['taxlabel']) . "',
					state = '" . $sheel->db->escape_string($sheel->GPC['state']) . "',
					countryname = '" . $sheel->db->escape_string($sheel->GPC['country']) . "',
					countryid = '" . intval($countryid) . "',
					city = '" . $sheel->db->escape_string($sheel->GPC['city']) . "',
					amount = '" . $sheel->db->escape_string($sheel->GPC['form']['amount']) . "',
					invoicetypes = '" . $sheel->GPC['invoicetypes'] . "',
					entirecountry = '" . $entirecountry . "',
					currencyid = '" . intval($sheel->GPC['form']['currencyid']) . "'
					WHERE taxid = '" . intval($sheel->GPC['taxid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER_ADMIN . 'settings/tax/');
				exit();
			}
			$form['taxid'] = (isset($sheel->GPC['taxid']) ? $sheel->GPC['taxid'] : '0');
			$checked1 = $checked2 = $checked3 = $checked4 = $checked5 = $checked6 = $checked7 = $checked8 = $checked9 = $checked10 = '';
			$sql = $sheel->db->query("
				SELECT taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry, currencyid
				FROM " . DB_PREFIX . "taxes
				WHERE taxid = '" . intval($sheel->GPC['taxid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$form['currencypulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', $res['currencyid']);
				$form['taxlabel'] = $res['taxlabel'];
				$form['amount'] = $res['amount'];
				$form['entirecountry'] = $res['entirecountry'];
				$countryname = $res['countryname'];
				$state = $res['state'];
				$city = $res['city'];
				$countryid = $res['countryid'];
				if ($res['entirecountry']) {
					$form['entirecountry_cb'] = 'checked="checked"';
					$sheel->template->meta['headinclude'] .= "<script>function disable_select(){document.ilform.state.disabled = true;document.ilform.city.disabled = true;}</script>";
					$sheel->template->meta['onload'] .= "return disable_select();";
				}
				if (!empty($res['invoicetypes'])) {
					$invoicetypetax = unserialize($res['invoicetypes']);
					foreach ($invoicetypetax as $invoicetype => $value) {
						switch ($invoicetype) {
							case 'storesubscription': {
									$checked1 .= 'checked="checked"';
									break;
								}
							case 'subscription': {
									$checked2 .= 'checked="checked"';
									break;
								}
							case 'commission': {
									$checked3 .= 'checked="checked"';
									break;
								}
							case 'credential': {
									$checked4 .= 'checked="checked"';
									break;
								}
							case 'portfolio': {
									$checked5 .= 'checked="checked"';
									break;
								}
							case 'enhancements': {
									$checked6 .= 'checked="checked"';
									break;
								}
							case 'lanceads': {
									$checked7 .= 'checked="checked"';
									break;
								}
							case 'insertionfee': {
									$checked8 .= 'checked="checked"';
									break;
								}
							case 'finalvaluefee': {
									$checked9 .= 'checked="checked"';
									break;
								}
							case 'buyerpremiumfee': {
									$checked10 .= 'checked="checked"';
									break;
								}
						}
					}
				}
			}
			// tax types currently supported
			$form['invoicetaxtype'] = '';
			$form['invoicetaxtype'] .= '<label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" ' . $checked2 . ' /> {_member_subscription_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" ' . $checked3 . ' /> {_escrow_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" ' . $checked6 . ' /> {_auction_enhancement_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" ' . $checked8 . ' /> {_insertion_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" ' . $checked9 . ' /> {_final_value_fees}</label>';
			$form['invoicetaxtype'] .= '<label for="buyerpremiumfee"><input type="checkbox" name="taxtype[buyerpremiumfee]" id="buyerpremiumfee" value="1" ' . $checked10 . ' /> Buyer\'s premium fees</label>';
			$form['country_js_pulldown'] = $sheel->common_location->construct_country_pulldown($countryid, $countryname, 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid');
			$form['state_js_pulldown'] = $sheel->common_location->construct_state_pulldown($countryid, $state, 'state', false, false, 0, 'draw-select', 0, 'city', 'cityid');
			$form['city_js_pulldown'] = $sheel->common_location->construct_city_pulldown($state, 'city', $city, false, true, 'draw-select');
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				$sheel->template->templateregistry['message'] = '{_demo_mode_only}';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
			if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
				$sheel->db->query("
					DELETE FROM " . DB_PREFIX . "taxes
					WHERE taxid = '" . intval($sheel->GPC['xid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Tax profile deleted', 'A tax profile has been successfully deleted.');
				die(json_encode(array('response' => '1', 'message' => 'A tax profile has been successfully deleted.')));
			} else {
				$sheel->template->templateregistry['message'] = 'This tax profile could not be deleted.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		$taxes = array();
		$sheel->show['no_taxes'] = true;
		$sqltax = $sheel->db->query("
			SELECT taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry, currencyid
			FROM " . DB_PREFIX . "taxes
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqltax) > 0) {
			while ($tax = $sheel->db->fetch_array($sqltax, DB_ASSOC)) {
				$tax['entire'] = ($tax['entirecountry']) ? '{_yes}' : '{_no}';
				$tax['currency'] = $sheel->currency->currencies[$tax['currencyid']]['currency_abbrev'];
				if (!empty($tax['invoicetypes'])) {
					$invoicetypetaxx = unserialize($tax['invoicetypes']);
					$typex = '';
					foreach ($invoicetypetaxx as $invoicetypex => $value) {
						$typex .= ucfirst($invoicetypex) . ', ';
					}
					$typex = mb_substr($typex, 0, -2);
					$tax['types'] = $typex;
				} else {
					$tax['types'] = '{_no_invoice_types_defined}';
				}
				if (empty($tax['state'])) {
					$tax['state'] = '-';
				}
				if (empty($tax['city'])) {
					$tax['city'] = '-';
				}
				$tax['actions'] = '<ul class="segmented"><li><a href="' . HTTPS_SERVER_ADMIN . 'settings/tax/update/' . $tax['taxid'] . '/" class="btn btn-slim btn--icon" title="{_update}"><span class="ico-16-svg halflings halflings-edit draw-icon" aria-hidden="true"></span></a></li><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'delete\', \'Delete this tax profile?\', \'Are you sure you want to delete this tax profile?\', \'' . $tax['taxid'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
				$taxes[] = $tax;
				$form['invoicetypetax'] = $tax['types'];
			}
			$sheel->show['no_taxes'] = false;
		}
		$sheel->template->parse_loop('main', array('taxes' => $taxes));
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
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>