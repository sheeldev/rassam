<?php
define('LOCATION', 'admin');
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
				$sheel->template->meta['areatitle'] = 'Admin CP | Branding';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Branding';
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
			$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Settings saved', 'Settings were successfully saved.');
			refresh(urldecode($sheel->GPC['return']));
			exit();
		} 
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'branding') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Branding';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Branding';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_branding';
		$sheel->template->fetch('main', 'settings_branding.html', 1);
		$areanav = 'settings_branding';
		$currentarea = '{_branding}';
		$form['currentdomain'] = $_SERVER['SERVER_NAME'];
		$settings = $sheel->admincp->construct_admin_input('branding', 'settings/branding/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'locale') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Locale';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Locale';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_locale';
		$currentarea = '{_locale}';
		$settings = $sheel->admincp->construct_admin_input('globalserverlocale', HTTPS_SERVER_ADMIN . 'settings/locale/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'mail') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Mail &amp; SMTP';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Mail &amp; SMTP';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_mail';
		$currentarea = '{_mail}';
		$settings = $sheel->admincp->construct_admin_input('globalserversmtp', HTTPS_SERVER_ADMIN . 'settings/mail/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'currency') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Currencies';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Currencies';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_currency';
		$currentarea = '{_currency}';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'settings/currencymanager/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_currency_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('globalserverlocalecurrency', HTTPS_SERVER_ADMIN . 'settings/currency/', '', $buttons);
	}  else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'companies') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Companies';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Companies';
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
					(company_id, name, bc_code, about, description, status, eventstart, countryid,currencyid, timezone)
					VALUES(
					NULL,
					'" . $sheel->db->escape_string($sheel->GPC['form']['name']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['about']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['status']) . "',
					'" . strtotime($sheel->GPC['form']['eventstart']) . "',
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
			$form['eventstart'] ='';
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

				$sheel->db->query("
					UPDATE " . DB_PREFIX . "companies
					SET name = '" . $sheel->db->escape_string($sheel->GPC['form']['name']) . "',
					bc_code = '" . $sheel->db->escape_string($sheel->GPC['form']['bc_code']) . "',
					about = '" . $sheel->db->escape_string($sheel->GPC['form']['about']) . "',
					description = '" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					countryid = '" . $countryid . "',
					currencyid = '" . intval($sheel->GPC['form']['currencyid']) . "',
					status = '" . $sheel->GPC['form']['status'] . "',
					eventstart = '" . strtotime($sheel->GPC['form']['eventstart']) . "',
					timezone = '" . $sheel->GPC['form']['tz'] . "'
					WHERE company_id = '" . intval($sheel->GPC['companyid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER_ADMIN . 'settings/companies/');
				exit();
			}
			$sql = $sheel->db->query("
			SELECT company_id, name, bc_code, about, description, countryid, currencyid, eventstart, status, timezone
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
				$form['eventstart'] = date('Y-m-d', $res['eventstart']);
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
		} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'default') {

			if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "companies
					SET isdefault = '0'
					WHERE company_id != '" . intval($sheel->GPC['xid']) . "'
				", 0, null, __FILE__, __LINE__);
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "companies
					SET isdefault = '1'
					WHERE company_id = '" . intval($sheel->GPC['xid']) . "'
				", 0, null, __FILE__, __LINE__);
				die(json_encode(array('response' => 1, 'message' => 'Successfully set default system company to ID ' . $sheel->GPC['xid'])));
			} else {
				$sheel->template->templateregistry['message'] = 'No company was selected.  Please try again.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'factory') {
			if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
				$sqlcomp = $sheel->db->query("
					SELECT isfactory
					FROM " . DB_PREFIX . "companies
					WHERE company_id = '" . intval($sheel->GPC['xid']) . "'
				", 0, null, __FILE__, __LINE__);

				if ($sheel->db->num_rows($sqlcomp) > 0) {
					$rescomp = $sheel->db->fetch_array($sqlcomp, DB_ASSOC);
					$isfactory = $rescomp['isfactory'];
					$newIsfactory = ($isfactory == '1') ? '0' : '1';
					$message = ($isfactory == '1') ? 'Successfully removed manufacturing capabilities from company to ID ' . $sheel->GPC['xid'] : 'Successfully added manufacturing capabilities to company to ID ' . $sheel->GPC['xid'];
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "companies
						SET isfactory = '" . $newIsfactory . "'
						WHERE company_id = '" . intval($sheel->GPC['xid']) . "'
					", 0, null, __FILE__, __LINE__);
				}
				die(json_encode(array('response' => 1, 'message' => $message)));
			} else {
				$sheel->template->templateregistry['message'] = 'No company was selected.  Please try again.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		$sqlcomp = $sheel->db->query("
			SELECT company_id, name, bc_code, description, countryid, currencyid, status, eventstart, timezone, isfactory, isdefault
			FROM " . DB_PREFIX . "companies
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlcomp) > 0) {
			while ($comp = $sheel->db->fetch_array($sqlcomp, DB_ASSOC)) {
				$comp['currency'] = $sheel->currency->currencies[$comp['currencyid']]['currency_abbrev'];
				$comp['country'] = $sheel->common_location->print_country_name($comp['countryid'], $_SESSION['sheeldata']['user']['slng'], false, '');
				$comp['action'] = '<a href="javascript:;"' . ' data-bind-event-click="acp_confirm(\'factory\', \'{_set_company_as_factory}\', \'{_set_company_as_factory_message}\', \'' . $comp['company_id'] . '\', 1, \'\', \'\')"' . ' class="btn btn-slim btn--icon" title="'. (($comp['isfactory'] == '1') ? '{_is_factory}' : '{_set_as_factory}') .'"><span class="halflings halflings-factory draw-icon' . (($comp['isfactory'] == '1') ? '--yellow-dark' : '') . '" aria-hidden="true"></span></a>
				<a href="javascript:;"' . (($comp['isdefault'] == '1') ? '' : ' data-bind-event-click="acp_confirm(\'default\', \'{_set_system_default_company}\', \'{_set_company_system_default_message}\', \'' . $comp['company_id'] . '\', 1, \'\', \'\')"') . ' class="btn btn-slim btn--icon" title="'. (($comp['isdefault'] == '1') ? '{_default_company}' : '{_set_as_default}') .'"><span class="halflings halflings-star draw-icon' . (($comp['isdefault'] == '1') ? '--sky-darker' : '') . '" aria-hidden="true"></span></a>';

				$comps[] = $comp;
			}
		}
		$sheel->template->parse_loop('main', array('comps' => $comps));

	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'registration') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Registration';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Registration';
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings - Registration';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings - Registration';
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_settings';
		$sheel->template->fetch('main', 'settings_registration.html', 1);
		$areanav = 'settings_registration';
		$currentarea = 'Registration';
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'users/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Users Manager</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('registrationdisplay', HTTPS_SERVER_ADMIN . 'settings/registration/', '', $buttons);
		$buttons = '<p><a href="javascript:;" onclick="test_ldap_settings()"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">Test LDAP Settings</button></a></p>';
		$ldap = $sheel->admincp->construct_admin_input('registrationldap', HTTPS_SERVER_ADMIN . 'settings/registration/', '', $buttons);
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'photos') {
		if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'attachment-manage-storagetype' and isset($sheel->GPC['form']['action'])) {
			$sheel->template->meta['areatitle'] = '{_managing_attachment_storage_type}';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_managing_attachment_storage_type}';
			if ($sheel->GPC['form']['action'] == 'rebuildpictures') {
				$sheel->auction_pictures_rebuilder->activate_cron(); // runs $sheel->auction_pictures_rebuilder->process_picture_rebuilder();
				$sheel->admincp->print_action_success('{_pictures_within_attachment_system_rebuilt}', HTTPS_SERVER_ADMIN . 'settings/attachments/');
				exit();
			}
		}
		$sheel->template->meta['areatitle'] = 'Admin CP | Photo &amp; Attachments';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Photo &amp; Attachments';
		$sheel->template->fetch('main', 'settings_photos.html', 1);
		$areanav = 'settings_attachments';
		$currentarea = 'Photos &amp; Attachments';
		$form['totalattachments'] = number_format($sheel->attachment->totalattachments());
		$form['totaldiskspace'] = $sheel->attachment->totaldiskspace();
		$form['storagetype'] = $sheel->attachment->storagetype('type');
		$form['storagetypeaction'] = $sheel->attachment->storagetype('formaction');
		$buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'settings/attachments/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_attachment_manager}</button></a></p>';
		$settings = $sheel->admincp->construct_admin_input('attachmentsystem', HTTPS_SERVER_ADMIN . 'settings/attachments/', '', $buttons);
		$moderation = $sheel->admincp->construct_admin_input('attachmentmoderation', HTTPS_SERVER_ADMIN . 'settings/attachments/');
		$limits = $sheel->admincp->construct_admin_input('attachmentlimit', HTTPS_SERVER_ADMIN . 'settings/attachments/');
	} else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'security') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Selling &amp; Security';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Security';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_security';
		$currentarea = '{_security}';
		$settings = $sheel->admincp->construct_admin_input('globalsecurity', HTTPS_SERVER_ADMIN . 'settings/security/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'session') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Sessions';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Sessions';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_session';
		$currentarea = '{_session}';
		$settings = $sheel->admincp->construct_admin_input('globalserversession', HTTPS_SERVER_ADMIN . 'settings/session/');
	}  	else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'optimization') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Settings &ndash; Optimization';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings &ndash; Optimization';
		$sheel->template->fetch('main', 'settings.html', 1);
		$areanav = 'settings_optimization';
		$currentarea = 'Optimization';
		$settings = $sheel->admincp->construct_admin_input('globaloptimization', HTTPS_SERVER_ADMIN . 'setttings/optimization/');
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'serverinfo') {
		$sheel->template->meta['areatitle'] = 'Admin CP | Server Info';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Server Info';
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
		$sheel->template->meta['areatitle'] = 'Admin CP | Diagnosis';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Diagnosis';
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
		'ldap' => (isset($ldap) ? $ldap : ''),
		'limits' => (isset($limits) ? $limits : ''),
		'id' => (isset($sheel->GPC['id']) ? $sheel->GPC['id'] : ''),
		'error' => ((isset($error) and !empty($error)) ? $error : ''),
		
	);

	$sheel->template->parse_hash('main', array('slpage' => $sheel->slpage,'form' => (isset($form) ? $form : array())));
	$sheel->template->pprint('main', $vars);
	exit();
} else {
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>