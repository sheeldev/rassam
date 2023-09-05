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
		'md5',
		'vendor/chartist',
		'vendor/growl'
	),
	'footer' => array(
	)
);
$sheel->template->meta['cssinclude'] = array(
	'common',
	'vendor' => array(
		'font-awesome',
		'glyphicons',
		'chartist',
		'growl',
		'balloon'
	)
);
$sheel->template->meta['areatitle'] = 'Admin CP | Users';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users';

if (($sidenav = $sheel->cache->fetch("sidenav_users")) === false) {
	$sidenav = $sheel->admincp_nav->print('users');
	$sheel->cache->store("sidenav_users", $sidenav);
}
if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
	if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bulkmailer') {
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_bulkemail';
		$areanav = 'users_bulkmailer';
		$currentarea = 'Bulk Mailer';
		$plans = $sheel->subscription->admincp_subscription_radios('form_who', 1);
		$statuses = $sheel->subscription->admincp_status_radios('form_who', 1);
		$vars = array(
			'sidenav' => $sidenav,
			'prevnext' => (isset($prevnext) ? $prevnext : ''),
			'areanav' => $areanav,
			'currentarea' => $currentarea,
			'id' => (isset($sheel->GPC['id']) ? intval($sheel->GPC['id']) : ''),
			'emailcount' => number_format($sheel->subscription->status_count('everyone')),
			'plans' => $plans,
			'statuses' => $statuses
		);
		$loops = array(
			'questions' => (isset($questions) ? $questions : array()),
			'languages' => (isset($languages) ? $languages : array())
		);
		$sheel->template->fetch('main', 'users_bulkmailer.html', 1);
		$sheel->template->parse_loop('main', $loops);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => (isset($form) ? $form : '')));
		$sheel->template->pprint('main', $vars);
		exit();
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bulkmailer/export') {

		switch ($sheel->GPC['form']['method']) {
			case 'newline': {
					$sql = $sheel->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					WHERE email != ''
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
					if ($sheel->db->num_rows($sql) > 0) {
						$txt = '';
						while ($emails = $sheel->db->fetch_array($sql)) {
							$txt .= trim($emails['email']) . LINEBREAK;
						}
					}
					$ext = '.txt';
					$mime = 'text/plain';
					break;
				}
			case 'csv': {
					$sql = $sheel->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					WHERE email != ''
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
					if ($sheel->db->num_rows($sql) > 0) {
						$txt = '';
						while ($emails = $sheel->db->fetch_array($sql)) {
							$txt .= '"' . trim($emails['email']) . '",' . LINEBREAK;
						}
					}
					$ext = '.csv';
					$mime = 'text/x-csv';
					break;
				}
		}
		$sheel->common->download_file($txt, "email-list" . $ext, $mime);
		exit();
	} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'audit') {
		$areanav = 'users_audit';
		$currentarea = 'Audit';
		$vars = array(
			'sidenav' => $sidenav,
			'prevnext' => (isset($prevnext) ? $prevnext : ''),
			'areanav' => $areanav,
			'currentarea' => $currentarea,
			'id' => (isset($sheel->GPC['id']) ? intval($sheel->GPC['id']) : ''),
		);
		
		
		exit();
	} else {
		if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'switch' and isset($sheel->GPC['userid']) and $sheel->GPC['userid'] > 0) {
			$sql = $sheel->db->query("
				SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE u.user_id = '" . intval($sheel->GPC['userid']) . "'
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$userinfo = $sheel->db->fetch_array($sql, DB_ASSOC);
				$customer = $sheel->customers->get_customer_details($userinfo['customerid']);
				$userinfo['subscriptionid'] = $customer['subscriptionid'];
				$sheel->sessions->build_user_session($userinfo);
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Users account switched into', 'A staff person has successfully switched into a users account successfully.');
				refresh(HTTPS_SERVER . '?note=sw:ac');
				exit();
			}
		} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'add') {
			$form = array();
			$sheel->template->meta['areatitle'] = 'Admin CP | Users - Add';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Add';
			$sheel->template->meta['jsinclude']['header'][] = 'admin_users';
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
				if (!isset($sheel->GPC['form']['password']) or !isset($sheel->GPC['form']['password2']) or empty($sheel->GPC['form']['password']) or empty($sheel->GPC['form']['password2']) or $sheel->GPC['form']['password'] != $sheel->GPC['form']['password2']) {
					$sheel->admincp->print_action_failed('{_passwords_are_empty_or_do_not_match}', HTTPS_SERVER_ADMIN . 'users/add/');
					exit();
				}
				// make sure username doesn't conflict with another user
				$sqlusercheck = $sheel->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE username IN ('" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "')
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sqlusercheck) > 0) { // woops! change username for new user automatically.
					$sheel->GPC['form']['username'] = $sheel->GPC['form']['username'] . ' ' . rand(1000, 999999); // Peter 39918
				}
				$sql = $sheel->db->query("
					SELECT locationid
					FROM " . DB_PREFIX . "locations
					WHERE location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'
					LIMIT 1
				");
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				if ($sheel->GPC['form']['customerid'] == '-1') {
					$sheel->GPC['form']['customerid'] = '0';
				}
				$salt = $sheel->construct_password_salt(5);
				$pass = md5(md5($sheel->GPC['form']['password']) . $salt);
				$sheel->GPC['form']['isadmin'] = $sheel->role->is_role_admin($sheel->GPC['form']['roleid']);
				$sheel->GPC['form']['languageid'] = isset($sheel->GPC['form']['languageid']) ? $sheel->GPC['form']['languageid'] : $sheel->language->fetch_default_languageid();
				$sheel->GPC['form']['useapi'] = ((isset($sheel->GPC['form']['useapi']) and $sheel->GPC['form']['useapi']) ? 1 : 0);
				$sheel->GPC['form']['emailnotify'] = ((isset($sheel->GPC['form']['emailnotify']) and $sheel->GPC['form']['emailnotify']) ? 1 : 0);
				$sheel->GPC['form']['dob'] = ((isset($sheel->GPC['form']['dob']) and !empty($sheel->GPC['form']['dob'])) ? $sheel->GPC['form']['dob'] : '0000-00-00');
				$sheel->GPC['form']['gender'] = ((isset($sheel->GPC['form']['gender']) and !empty($sheel->GPC['form']['gender'])) ? $sheel->GPC['form']['gender'] : '');



				$newuserid = $sheel->admincp_users->construct_new_member(
					$sheel->GPC['form']['username'],
					$sheel->GPC['form']['customerid'],
					$sheel->GPC['form']['roleid'],
					$pass,
					$salt,
					$sheel->GPC['form']['email'],
					$sheel->GPC['form']['firstname'],
					$sheel->GPC['form']['lastname'],
					$sheel->GPC['form']['address'],
					$sheel->GPC['form']['address2'],
					$sheel->GPC['city'],
					$sheel->GPC['state'],
					$sheel->GPC['form']['zipcode'],
					$sheel->GPC['form']['phone'],
					$res['locationid'],
					$sheel->GPC['form']['dob'],
					$sheel->GPC['form']['languageid'],
					$sheel->GPC['form']['currencyid'],
					$sheel->GPC['form']['timezone'],
					$sheel->GPC['form']['isadmin'],
				);
				if ($newuserid > 0) {
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET useapi = '" . intval($sheel->GPC['form']['useapi']) . "',
						emailnotify = '" . intval($sheel->GPC['form']['emailnotify']) . "',
						registersource = 'Admin Panel',
						apikey = '" . $sheel->db->escape_string($sheel->GPC['form']['apikey']) . "',
						gender = '" . $sheel->db->escape_string($sheel->GPC['form']['gender']) . "'
						WHERE user_id = '" . intval($newuserid) . "'
						LIMIT 1
					");

				}

				if (isset($sheel->GPC['form']['notifyregister']) and $sheel->GPC['form']['notifyregister']) {
					$sheel->email->mail = $sheel->GPC['form']['email'];
					$sheel->email->slng = $sheel->language->fetch_user_slng($newuserid);
					$sheel->email->get('register_welcome_email_admincp');
					$sheel->email->set(
						array(
							'{{username}}' => $sheel->GPC['form']['username'],
							'{{user_id}}' => $newuserid,
							'{{first_name}}' => $sheel->GPC['form']['firstname'],
							'{{last_name}}' => $sheel->GPC['form']['lastname'],
							'{{phone}}' => $sheel->GPC['form']['phone']
						)
					);
					$sheel->email->send();
				}
				if (isset($sheel->GPC['form']['notifywelcome']) and $sheel->GPC['form']['notifywelcome']) {
					$sheel->email->mail = SITE_CONTACT;
					$sheel->email->slng = $sheel->language->fetch_site_slng();
					$sheel->email->get('register_welcome_email_admin_admincp');
					$sheel->email->set(
						array(
							'{{username}}' => $sheel->GPC['form']['username'],
							'{{user_id}}' => $newuserid,
							'{{first_name}}' => $sheel->GPC['form']['firstname'],
							'{{last_name}}' => $sheel->GPC['form']['lastname'],
							'{{phone}}' => $sheel->GPC['form']['phone'],
							'{{emailaddress}}' => $sheel->GPC['form']['email'],
						)
					);
					$sheel->email->send();
				}
				refresh(HTTPS_SERVER_ADMIN . 'users/');
				exit();
			}
			$form['username'] = '';
			$form['first_name'] = '';
			$form['last_name'] = '';
			$form['dob'] = '';
			$form['email'] = '';
			$form['phone'] = '';
			$form['address'] = '';
			$form['address2'] = '';
			$form['zip_code'] = '';
			$form['ipaddress'] = '';
			$form['apikey'] = md5($sheel->construct_password(32));
			$userstatuses = array('active' => '{_active_can_signin}', 'suspended' => '{_suspended_cannot_signin}', 'unverified' => '{_unverified_email_cannot_signin}', 'banned' => '{_banned_cannot_signin}', 'moderated' => '{_moderated_cannot_signin}');
			$form['userstatus'] = $sheel->construct_pulldown('status', 'form[status]', $userstatuses, '', 'class="draw-select"');
			$form['role_pulldown'] = $sheel->role->print_role_pulldown('', 1, 0, '', '', 'draw-select', 'form_roleid', 'form[roleid]', false);
			$form['customer_pulldown'] = $sheel->admincp_customers->print_customer_pulldown('', 1, '', '', 'draw-select', 'form_customerid', 'form[customerid]', false);
			$form['oldroleid'] = '';

			$planactions = array(
				'active' => '{_add_plan_invoice_mark_paid}',
				'activepaid' => '{_add_plan_invoice_mark_paid_for_amount}',
				'inactive' => '{_add_plan_invoice_mark_unpaid_for_amount}'
			);
			$countryid = $sheel->common_location->fetch_country_id($sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']);
			$form['country_pulldown'] = $sheel->common_location->construct_country_pulldown(0, '', 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', true, false, '', 0, 'city', 'cityid');
			$form['state_pulldown'] = '<div id="stateid">' . $sheel->common_location->construct_state_pulldown(0, '', 'state', false, true, 0, 'draw-select', 0, 'city', 'cityid') . '</div>';
			$form['city_pulldown'] = '<div id="cityid">' . $sheel->common_location->construct_city_pulldown('', 'city', '', false, true, 'draw-select') . '</div>';
			$form['language_pulldown'] = $sheel->language->construct_language_pulldown('languageid', $sheel->language->fetch_default_languageid(), 'draw-select', 'form[languageid]');
			$form['timezone_pulldown'] = $sheel->datetimes->timezone_pulldown('timezone', $sheel->config['globalserverlocale_sitetimezone'], false, true, 'draw-select', 'form[timezone]');
			$form['currency_pulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', '');
			$form['gender_pulldown'] = $sheel->construct_pulldown('gender', 'form[gender]', array('male' => '{_male}', 'female' => '{_female}', '' => '{_unknown}'), '', 'class="draw-select"');
			$form['notifyregister'] = ' checked="checked"';
			$form['emailnotify'] = ' checked="checked"';

		} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'update') {
			$sheel->template->meta['jsinclude']['header'][] = 'admin_users';
			$form['userid'] = (isset($sheel->GPC['userid']) ? $sheel->GPC['userid'] : '0');

			$sheel->template->meta['areatitle'] = 'Admin CP | Users - Update';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Update';
			if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
				$country = $sheel->db->escape_string($sheel->GPC['country']);
				$sheel->GPC['form']['locationid'] = intval($sheel->common_location->fetch_country_id($country));


				$ipres = ((isset($sheel->GPC['form']['iprestrict']) and $sheel->GPC['form']['iprestrict']) ? '1' : '0');
				$passwordsql = '';
				if (!empty($sheel->GPC['form']['password'])) {
					$newsalt = $sheel->construct_password_salt(5);
					$newpassword = md5(md5($sheel->GPC['form']['password']) . $newsalt);
					$passwordsql = "password = '" . $sheel->db->escape_string($newpassword) . "',";
					$passwordsql .= "salt = '" . $sheel->db->escape_string($newsalt) . "',";
					$passwordsql .= "password_lastchanged = '" . DATETIME24H . "',";
					$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Users account password changed', 'The users account password was successfully changed.');
				}
				$status = ((isset($sheel->GPC['form']['status'])) ? $sheel->db->escape_string($sheel->GPC['form']['status']) : 'active');
				$dob = ((isset($sheel->GPC['form']['dob'])) ? $sheel->db->escape_string($sheel->GPC['form']['dob']) : '0000-00-00');
				$isadmin = ((isset($sheel->GPC['form']['isadmin'])) ? intval($sheel->GPC['form']['isadmin']) : '0');
				$useapi = ((isset($sheel->GPC['form']['useapi'])) ? intval($sheel->GPC['form']['useapi']) : '0');
				$emailnotify = ((isset($sheel->GPC['form']['emailnotify'])) ? intval($sheel->GPC['form']['emailnotify']) : '0');
				// detect if admin is changing status from 'moderated' to 'active'
				$oldstatus = $sheel->fetch_user('status', intval($sheel->GPC['userid']));
				$username_history = $sheel->fetch_user('username_history', intval($sheel->GPC['userid']));
				if ($oldstatus == 'moderated' and $status == 'active') {
					$activatedusers = $sheel->admincp_users->activate_user(array($sheel->GPC['userid']));
					$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Users account status verified', 'The users account status was successfully verified.');
				}
				$sheel->show['error_username'] = $sheel->show['error_username_exists'] = $sheel->show['error_new_email_exists'] = false;
				if (isset($sheel->GPC['form']['email']) and isset($sheel->GPC['form']['oldemail']) and $sheel->GPC['form']['email'] != $sheel->GPC['form']['oldemail']) { // changing to a new email..
					$sqlemailcheck = $sheel->db->query("
							SELECT user_id
							FROM " . DB_PREFIX . "users
							WHERE email IN ('" . $sheel->db->escape_string(htmlspecialchars_uni($sheel->GPC['form']['email'])) . "')
								AND user_id != '" . intval($sheel->GPC['userid']) . "'
						");
					if ($sheel->db->num_rows($sqlemailcheck) > 0) {
						$sheel->show['error_new_email_exists'] = true;
					}
				}
				if (isset($sheel->GPC['form']['username']) and $sheel->GPC['form']['username'] != '') { // quick username checkup
					if ($sheel->common->is_username_banned($sheel->GPC['form']['username'])) { // username ban checkup
						$sheel->show['error_username'] = true;
					} else { // the username isn't banned
						$sqlusercheck = $sheel->db->query("
								SELECT user_id
								FROM " . DB_PREFIX . "users
								WHERE username IN ('" . $sheel->db->escape_string(htmlspecialchars_uni($sheel->GPC['form']['username'])) . "')
									AND user_id != '" . intval($sheel->GPC['userid']) . "'
							");
						if ($sheel->db->num_rows($sqlusercheck) > 0) {
							$sheel->show['error_username_exists'] = true;
						} else { // does not exist- we're good to go
							if ($sheel->GPC['form']['oldusername'] != $sheel->GPC['form']['username']) { // admin is changing users username
								if (!empty($username_history) and $sheel->is_serialized($username_history)) {
									$username_history = unserialize($username_history);
									$username_history[] = array(
										'username' => $sheel->GPC['form']['oldusername'],
										'datetime' => DATETIME24H
									);
									$username_history = serialize($username_history);
								} else {
									$username_history = array(
										array(
											'username' => $sheel->GPC['form']['oldusername'],
											'datetime' => DATETIME24H
										)
									);
									$username_history = serialize($username_history);
								}
							}
						}
					}
				} else {
					$sheel->show['error_username'] = true;
				}
				if ($sheel->show['error_username']) {
					$sheel->admincp->print_action_failed('{_sorry_the_username_you_entered_appears_to_be_in_the_username_ban_list}', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
					exit();
				}
				if ($sheel->show['error_username_exists']) {
					$sheel->admincp->print_action_failed('This username appears to already exist.  Please enter a different username.', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
					exit();
				}
				if ($sheel->show['error_new_email_exists']) {
					$sheel->admincp->print_action_failed('This email address appears to already exist.  Please enter a different email address.', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
					exit();
				}
				$gendersql = '';
				if (isset($sheel->GPC['form']['gender']) and !empty($sheel->GPC['form']['gender'])) {
					$gendersql = "gender = '" . $sheel->db->escape_string($sheel->GPC['form']['gender']) . "',";
				}
				$sheel->GPC['form']['languageid'] = ((isset($sheel->GPC['form']['languageid'])) ? $sheel->GPC['form']['languageid'] : $sheel->language->fetch_default_languageid());
				$sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "',
						customerid = '" . intval($sheel->GPC['form']['customerid']) . "',
						roleid = '" . intval($sheel->GPC['form']['roleid']) . "',
						$passwordsql
						email = '" . $sheel->db->escape_string($sheel->GPC['form']['email']) . "',
						first_name = '" . $sheel->db->escape_string($sheel->GPC['form']['firstname']) . "',
						last_name = '" . $sheel->db->escape_string($sheel->GPC['form']['lastname']) . "',
						address = '" . $sheel->db->escape_string($sheel->GPC['form']['address']) . "',
						address2 = '" . $sheel->db->escape_string($sheel->GPC['form']['address2']) . "',
						city = '" . $sheel->db->escape_string($sheel->GPC['city']) . "',
						state = '" . $sheel->db->escape_string($sheel->GPC['state']) . "',
						zip_code = '" . $sheel->db->escape_string($sheel->GPC['form']['zipcode']) . "',
						timezone = '" . $sheel->db->escape_string($sheel->GPC['form']['timezone']) . "',
						phone = '" . $sheel->db->escape_string($sheel->GPC['form']['phone']) . "',
						country = '" . intval($sheel->GPC['form']['locationid']) . "',
						ipaddress = '" . $sheel->db->escape_string($sheel->GPC['form']['ipaddress']) . "',
						iprestrict = '" . $sheel->db->escape_string($ipres) . "',
						status = '" . $sheel->db->escape_string($status) . "',
						dob = '" . $sheel->db->escape_string($dob) . "',
						$gendersql
						isadmin = '" . intval($isadmin) . "',
						useapi = '" . intval($useapi) . "',
						apikey = '" . $sheel->db->escape_string($sheel->GPC['form']['apikey']) . "',
						emailnotify = '" . intval($emailnotify) . "',
						username_history = '" . $sheel->db->escape_string($username_history) . "',
						usernameslug = '" . $sheel->db->escape_string($sheel->seo->construct_seo_url_name($sheel->GPC['form']['username'])) . "',
						languageid = '" . intval($sheel->GPC['form']['languageid']) . "',
						currencyid = '" . intval($sheel->GPC['form']['currencyid']) . "'
						WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
						LIMIT 1
					");

				if (isset($sheel->GPC['form']['emailuser']) and $sheel->GPC['form']['emailuser']) { // staff emailing user with new random or entered password
					if (empty($sheel->GPC['form']['password'])) { // staff sending randomly generated new password to user
						$sheel->GPC['form']['password'] = $sheel->construct_password(8);
						$newsalt = $sheel->construct_password_salt(5);
						$newpassword = md5(md5($sheel->GPC['form']['password']) . $newsalt);
						$passwordsql = "password = '" . $sheel->db->escape_string($newpassword) . "',";
						$passwordsql .= "salt = '" . $sheel->db->escape_string($newsalt) . "',";
						$passwordsql .= "password_lastchanged = '" . DATETIME24H . "'";
						$sheel->db->query("
								UPDATE " . DB_PREFIX . "users
								SET $passwordsql
								WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
								LIMIT 1
							");



						$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff generated a new account password for user', 'The users account password was successfully changed.');
					}
					$sheel->email->mail = $sheel->GPC['form']['email'];
					$sheel->email->slng = $sheel->language->fetch_user_slng(intval($sheel->GPC['userid']));
					$sheel->email->get('update_user_profile');
					$sheel->email->set(
						array(
							'{{username}}' => $sheel->GPC['form']['username'],
							'{{password}}' => $sheel->GPC['form']['password'],
						)
					);
					$sheel->email->send();
				}

				refresh(HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
				exit();
			} 

			$sql = $sheel->db->query("
					SELECT
					*,
					l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS location
					FROM " . DB_PREFIX . "users u
					LEFT JOIN " . DB_PREFIX . "locations l ON (u.country = l.locationid)
					WHERE u.user_id = '" . intval($sheel->GPC['userid']) . "'
					LIMIT 1
				");
			if ($sheel->db->num_rows($sql) == 0) {
				$sheel->admincp->print_action_failed('{_the_user_account_no_longer_exists}', HTTPS_SERVER_ADMIN . 'users/');
				exit();
			}
			$sheel->show['usernamehistory'] = false;
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$res['registersource'] = ((!empty($res['registersource'])) ? o($res['registersource']) : '-');
				$res['username'] = o($res['username']);
				$username = $res['username'];
				$usernamehistory = '';
				if (!empty($res['username_history'])) {
					$res['username_history'] = unserialize($res['username_history']);
					foreach ($res['username_history'] as $array) {
						$usernamehistory .= '<div class="sb" title="{_profile_changed_on_x::' . $sheel->common->print_date($array['datetime']) . '}">' . $array['username'] . '</div>';
					}
					$sheel->show['usernamehistory'] = true;
				}
				$res['usernamehistory'] = $usernamehistory;
				$res['first_name'] = o($res['first_name']);
				$res['last_name'] = o($res['last_name']);
				$res['phone'] = o($res['phone']);
				$res['address'] = o($res['address']);
				$res['address2'] = o($res['address2']);
				$res['city'] = o(ucfirst($res['city']));
				$res['zip_code'] = o($res['zip_code']);
				$res['restrict'] = ($res['iprestrict'] == '1') ? '<input type="checkbox" name="iprestrict" value="1" checked="checked" />' : '<input type="checkbox" name="iprestrict" value="1" />';
				$res['added'] = $sheel->common->print_date($res['date_added'], $sheel->config['globalserverlocale_globaltimeformat'], 0, 0);
				$res['lastseen'] = ($res['lastseen'] != '0000-00-00 00:00:00') ? $sheel->common->print_date($res['lastseen'], $sheel->config['globalserverlocale_globaltimeformat'], 0, 0) : '{_never}';
				$res['localtime'] = $sheel->common->print_date(DATETIME24H, 'h:i A', true, false, $res['timezone']);
				$username = "(" . $res['username'] . ")";
				$userstatuses = array('active' => '{_active_can_signin}', 'suspended' => '{_suspended_cannot_signin}', 'unverified' => '{_unverified_email_cannot_signin}', 'banned' => '{_banned_cannot_signin}', 'moderated' => '{_moderated_cannot_signin}');
				$res['userstatus'] = $sheel->construct_pulldown('status', 'form[status]', $userstatuses, $res['status'], 'class="draw-select"');
				$res['iprestrict'] = (($res['iprestrict']) ? 'checked="checked"' : '');
				$res['isadmin'] = (($res['isadmin']) ? 'checked="checked"' : '');
				$res['posthtml'] = (($res['posthtml']) ? 'checked="checked"' : '');
				$res['useapi'] = (($res['useapi']) ? 'checked="checked"' : '');
				$res['emailnotify'] = (($res['emailnotify']) ? 'checked="checked"' : '');
				$res['apikey'] = o((is_null($res['apikey'])) ? '' : $res['apikey']);
				$form = $res;
				$form['userid'] = intval($sheel->GPC['userid']);
				$form['role_pulldown'] = $sheel->role->print_role_pulldown($res['roleid'], 0, 0, '', '', 'draw-select', 'form_roleid', 'form[roleid]', false);
				$form['customer_pulldown'] = $sheel->admincp_customers->print_customer_pulldown($res['customerid'], 0, '', '', 'draw-select', 'form_customerid', 'form[customerid]', false);
				
				
				
				$countryid = $sheel->common_location->fetch_country_id($res['location'], $_SESSION['sheeldata']['user']['slng']);
				$form['country_pulldown'] = $sheel->common_location->construct_country_pulldown($countryid, $res['location'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid');
				$form['state_pulldown'] = '<div id="stateid">' . $sheel->common_location->construct_state_pulldown($countryid, $res['state'], 'state', false, false, 0, 'draw-select', 0, 'city', 'cityid') . '</div>';
				$form['city_pulldown'] = '<div id="cityid">' . $sheel->common_location->construct_city_pulldown($res['state'], 'city', $res['city'], false, false, 'draw-select') . '</div>';
				$form['language_pulldown'] = $sheel->language->construct_language_pulldown('languageid', $res['languageid'], 'draw-select', 'form[languageid]');
				$form['timezone_pulldown'] = $sheel->datetimes->timezone_pulldown('timezone', $res['timezone'], false, true, 'draw-select', 'form[timezone]');
				$form['currency_pulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', '');
				$form['gender_pulldown'] = $sheel->construct_pulldown('gender', 'form[gender]', array('male' => '{_male}', 'female' => '{_female}', '' => '{_unknown}'), $res['gender'], 'class="draw-select"');
				$sqll = $sheel->db->query("
						SELECT landingpage
						FROM " . DB_PREFIX . "visits
						WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						ORDER BY lasthit DESC
						LIMIT 1
					");
				if ($sheel->db->num_rows($sqll) > 0) {
					$resl = $sheel->db->fetch_array($sqll, DB_ASSOC);
					$form['lastlocation'] = '<a href="' . HTTPS_SERVER . $resl['landingpage'] . '" target="_blank" title="' . o($resl['landingpage']) . '">' . $sheel->shorten($resl['landingpage'], 55) . '</a>';
				} else {
					$form['lastlocation'] = '-';
				}
			}
			$consentlog = array();
			$sql = $sheel->db->query("
					SELECT datetime, message
					FROM " . DB_PREFIX . "audit
					WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
						AND type = 'consent'
					ORDER BY logid DESC
				");
			if ($sheel->db->num_rows($sql) > 0) {
				$altrows = 0;
				while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$row['datetime'] = $sheel->common->print_date($sheel->datetimes->fetch_datetime_from_timestamp($row['datetime']), 'd-M-Y', false, false);
					$row['description'] = stripslashes(o($row['message']));
					$consentlog[] = $row;
				}
			}

		} else {
			if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'deleteusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->remove_user($ids, false);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'deleteusersnoemail') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->remove_user($ids, true);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'activateusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->activate_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'suspendusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->suspend_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'unsuspendusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->unsuspend_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'banusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->ban_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'unbanusers') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->unban_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			} else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'canceldeleterequest') {
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->reject_user_delete_request($ids, true);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				} else {
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			$prevnext = '';
			$users = array();
			$sheel->GPC['sort'] = (isset($sheel->GPC['sort']) ? $sheel->GPC['sort'] : '');
			$sheel->GPC['q'] = (isset($sheel->GPC['q']) ? trim($sheel->GPC['q']) : '');
			$sheel->GPC['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
			$sheel->GPC['filter'] = (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : '');
			$sheel->GPC['page'] = (!isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
			$sheel->GPC['pp'] = (!isset($sheel->GPC['pp']) or isset($sheel->GPC['pp']) and $sheel->GPC['pp'] <= 0) ? $sheel->config['globalfilters_maxrowsdisplay'] : intval($sheel->GPC['pp']);
			$where = "u.user_id > 0";
			$extrasql = '';
			if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'staff') {
				$where = "u.isadmin = '1'";
			} else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'suspended') {
				$where = "u.status = 'suspended'";
			} else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'banned') {
				$where = "u.status = 'banned'";
			} else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'moderated') {
				$where = "u.status = 'moderated'";
			} else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'unverified') {
				$where = "u.status = 'unverified'";
			} else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'deleterequests') {
				$where = "u.requestdeletion = '1'";
			}
			if (isset($sheel->GPC['filter']) and isset($sheel->GPC['q']) and $sheel->GPC['q'] != '') {
				switch ($sheel->GPC['filter']) {
					case 'name': // Full name / Username
						{
							$sheel->GPC['q1'] = $sheel->GPC['q2'] = '';
							if (strrchr($sheel->GPC['q'], ' ')) {
								$tmp = explode(' ', trim($sheel->GPC['q']));
								$sheel->GPC['q1'] = trim($tmp[0]);
								$sheel->GPC['q2'] = trim($tmp[1]);
								$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q1']) . "%' AND u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q2']) . "%'))";
							} else {
								$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')";
							}
							break;
						}
					case 'location': // city, state or country or zipcode
						{
							$extrasql = "AND (l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.city LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.state LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.zip_code LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')";
							break;
						}
					case 'email': {
							$extrasql = "AND u.email LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%'";
							break;
						}
					case 'customer': {
							$extrasql = "AND u.customerid = '" . $sheel->db->escape_string($sheel->GPC['q']) . "'";
							break;
						}
				}
			}
			$sql = $sheel->db->query("
				SELECT
				u.user_id, u.username, u.first_name, u.last_name, u.customerid, u.email, u.phone, u.city, u.state, u.zip_code, u.status,  u.roleid, u.isadmin, u.permissions, u.registersource, u.lastseen, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, l.cc
				FROM " . DB_PREFIX . "users u
				LEFT JOIN " . DB_PREFIX . "locations l ON (u.country = l.locationid)
				WHERE $where
				$extrasql
				GROUP BY user_id
				ORDER BY user_id DESC
				LIMIT " . (($sheel->GPC['page'] - 1) * $sheel->GPC['pp']) . "," . $sheel->GPC['pp']
			);
			
			$sql2 = $sheel->db->query("
				SELECT
				u.user_id
				FROM " . DB_PREFIX . "users u
				LEFT JOIN " . DB_PREFIX . "locations l ON (u.country = l.locationid)
				WHERE $where
				$extrasql
				GROUP BY user_id
				ORDER BY user_id DESC
			");
			$number = (int) $sheel->db->num_rows($sql2);
			$form['number'] = number_format($number);
			if ($sheel->db->num_rows($sql) > 0) {
				$row_count = 0;
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$flags = '';
					if ($res['status'] == 'suspended') {
						$flags .= '<span title="{_suspended}"><i class="badge badge--warning" aria-hidden="true">S</i></span>';
					} else if ($res['status'] == 'banned') {
						$flags .= '<span title="{_banned}"><i class="badge badge--critical" aria-hidden="true">B</i></span>';
					} else if ($res['status'] == 'unverified' or empty($res['email']) or $res['email'] == '{_unknown}') {
						$flags .= '<span title="{_email_unverified}"><i class="badge badge--critical" aria-hidden="true">EU</i></span>';
					} else if ($res['status'] == 'moderated') {
						$flags .= '<span title="Moderation pending user"><i class="badge badge--info" aria-hidden="true">M</i></span>';
					}
					if (empty($res['registersource'])) {
						$res['registersource'] = 'n/a';
					}
					$res['flags'] = $flags;
					$res['switch'] = '<span title="{_switch_to_another_user}"><a href="' . HTTPS_SERVER_ADMIN . 'users/switch/' . $res['user_id'] . '/" data-no-turbolink>{_sign_in}</a></span>';
					$res['icon'] = '<img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($res['cc']) . '.png" border="0" alt="" id="" />';
					$res['lastseen'] = $sheel->common->print_date($res['lastseen'], 'M j \@ g:ia', 0, 0);
					$customer = $sheel->admincp_customers->get_customer_details($res['customerid']);
					$res['customer_ref'] = $customer['customer_ref'];
					$res['customername'] = $customer['customername'];
					$res['plan'] = $res['isadmin'] == '1' ? 'Admin' : $sheel->subscription->getname($customer['subscriptionid']);
					$res['role'] = $sheel->role->print_role($res['roleid']);
					$users[] = $res;
					$row_count++;
				}
			} else {
				$sheel->show['no_users'] = true;
			}
			$pageurl = PAGEURL;
			$prevnext = $sheel->admincp->pagination($number, $sheel->GPC['pp'], $sheel->GPC['page'], $pageurl);
			$filter_options = array(
				'' => '{_select_filter} &ndash;',
				'name' => '{_full_name} / {_username}',
				'location' => '{_location} ({_city}, {_state}, {_country})',
				'email' => '{_email}',
				'customer' => '{_customer}'
			);
			$form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
			$filter_options = array(
				'10' => '{_per_page} &ndash;',
				'10' => '{_x_per_page::10}',
				'20' => '{_x_per_page::20}',
				'50' => '{_x_per_page::50}',
				'100' => '{_x_per_page::100}',
				'250' => '{_x_per_page::250}',
				'500' => '{_x_per_page::500}',
				'1000' => '{_x_per_page::1000}',
			);
			$form['pp_pulldown'] = $sheel->construct_pulldown('pp', 'pp', $filter_options, (isset($sheel->GPC['pp']) ? $sheel->GPC['pp'] : ''), 'class="draw-select" onchange="this.form.submit()"');
			$form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
			unset($filter_options);
		}
	}
	$vars = array(
		'sidenav' => $sidenav,
		'prevnext' => (isset($prevnext) ? $prevnext : ''),
	);
	$loops = array(
		'users' => (isset($users) ? $users : array()),
		'transactions' => (isset($transactions) ? $transactions : array()),
		'pointstransactions' => (isset($pointstransactions) ? $pointstransactions : array()),
		'consentlog' => ((isset($consentlog)) ? $consentlog : array())
	);



	$sheel->template->fetch('main', 'users.html', 1);
	$sheel->template->parse_loop('main', $loops);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => (isset($form) ? $form : '')));
	$sheel->template->pprint('main', $vars);
	exit();
} else {
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>