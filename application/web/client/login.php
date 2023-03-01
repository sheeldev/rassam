<?php
require_once(SITE_ROOT . 'application/config.php');

if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
define('LOCATION', 'login');
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'vendor/jquery_' . JQUERYVERSION,
		'vendor/lazyload',
		'vendor/growl',
		'login',
        'password_strength',
	),
	'footer' => array(
		'v5',
		'md5',
		'autocomplete',
		'others'
	)
);
$sheel->template->meta['cssinclude'] = array(
	'vendor' => array(
		'bootstrap3.3.5',
		'font-awesome',
		'spinner',
		'tablesaw',
		'growl',
		'breadcrumb',
		'slidein',
		'color'
	),
	'general',
	'theme',
	'timeline'
);
$sheel->template->meta['area'] = 'login';

$sheel->template->meta['navcrumb'] = array($sheel->ilpage['login'] => $sheel->ilcrumbs[$sheel->ilpage['login']]);

$redirect = isset($sheel->GPC['redirect']) ? strip_tags($sheel->GPC['redirect']) : '';

if (isset($sheel->GPC['login_process']) and $sheel->GPC['login_process'] > 0) { // authenticate
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_submitting_login_information}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_submitting_login_information}';
	$badusername = $badpassword = true;
	$userinfo = array();
	$sheel->GPC['username'] = trim($sheel->GPC['username']);
	if (!empty($sheel->GPC['username'])) {
		// default subscription params
		$userinfo['roleid'] = -1;
		$userinfo['subscriptionid'] = $userinfo['cost'] = 0;
		$userinfo['active'] = 'no';
		$sql = $sheel->db->query("
			SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			WHERE (u.username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "' OR u.email = '" . $sheel->db->escape_string($sheel->GPC['username']) . "')
			GROUP BY u.username
			LIMIT 1
		");
		if ($sheel->db->num_rows($sql) > 0) {
			$userinfo = $sheel->db->fetch_array($sql, DB_ASSOC);
			$badusername = $badpassword = false;
			if (
				$userinfo['password'] != iif($sheel->GPC['password'] and !$sheel->GPC['md5pass'], md5(md5($sheel->GPC['password']) . $userinfo['salt']), '') and
				$userinfo['password'] != md5($sheel->GPC['md5pass'] . $userinfo['salt']) and
				$userinfo['password'] != iif($sheel->GPC['md5pass_utf'], md5($sheel->GPC['md5pass_utf'] . $userinfo['salt']), '')
			) {
				$badpassword = true;
			}
		} else {
		}
		if (!$badusername and !$badpassword) { // are we already logged in from somewhere else?
			$lsql = $sheel->db->query("
				SELECT ipaddress
				FROM " . DB_PREFIX . "sessions
				WHERE userid = '" . $userinfo['user_id'] . "'
					AND isadmin = '0'
			");
			if ($sheel->db->num_rows($lsql) > 0) {
				$lres = $sheel->db->fetch_array($lsql, DB_ASSOC);
				//the below two lines if disabled will dissalow multiple login using the same username
				//refresh(HTTPS_SERVER . 'signin/?error=asi&note=' . mb_substr($lres['ipaddress'], 0, 6) . '&uid=' . $userinfo['user_id']);
				//exit();
			}
			// update last seen for this member
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "',
				failedlogins = '0'
				WHERE user_id = '" . $userinfo['user_id'] . "'
			");
			if ($userinfo['status'] == 'active') { // ip restriction
				if ($userinfo['iprestrict'] and !empty($userinfo['ipaddress'])) {
					if (IPADDRESS == $userinfo['ipaddress']) {
						refresh(HTTPS_SERVER . 'signin/?error=iprestrict');
						exit();
					}
				}

				// default shipping & billing profile
				$userinfo['shipprofileid'] = $sheel->user->fetch_default_ship_profileid($userinfo['user_id']);
				$userinfo['billprofileid'] = $sheel->user->fetch_default_bill_profileid($userinfo['user_id']);

				// create valid user session
				$sheel->sessions->build_user_session($userinfo);
				if (isset($sheel->GPC['remember']) and $sheel->GPC['remember']) { // user has chosen the marketplace to remember them for 24 hours
					set_cookie('userid', $sheel->crypt->encrypt($userinfo['user_id']), false, true, false, 1);
					set_cookie('password', $sheel->crypt->encrypt($userinfo['password']), false, true, false, 1);
					set_cookie('username', $sheel->crypt->encrypt($userinfo['username']), false, true, false, 1);
				}
				// remember users last visit and last hit activity regardless of remember me preference
				set_cookie('lastvisit', DATETIME24H);
				set_cookie('lastactivity', DATETIME24H);
				set_cookie('radiuszip', o($sheel->format_zipcode($userinfo['zip_code'])));
				$sheel->log_event($userinfo['user_id'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), $userinfo['username'] . ' just signed in', $userinfo['username'] . ' just signed into the marketplace.');
				if (!empty($redirect)) {
					$landing = $redirect;
					if ($redirect[0] === '/') {
						$landing = substr($landing, 1);
					}
				} else {
					$landing = ''; // account/
				}
				if ($sheel->GPC['login_process'] <= 1) { // message of the day redirector if we're not admin
					$motd = $sheel->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
					if ((!empty($_COOKIE[COOKIE_PREFIX . 'motd']) and $_COOKIE[COOKIE_PREFIX . 'motd'] != DATETODAY and !empty($motd) and $motd != '') or (empty($_COOKIE[COOKIE_PREFIX . 'motd']) and !empty($motd) and $motd != '')) {
						set_cookie('motd', DATETODAY);
						$sheel->template->meta['navcrumb'] = array();
						$sheel->template->meta['navcrumb'][''] = '{_message_of_the_day}';
						$motd = stripslashes($motd);
						$motd = $sheel->bbcode->bbcode_to_html($motd);
						$vars = array('motd' => $motd, 'landing' => $landing);
						$sheel->template->fetch('main', 'main_motd.html');
						$sheel->template->pprint('main', $vars);
						exit();
					}
				}

				$landing = HTTPS_SERVER . $landing;
				refresh($landing);
				exit();
			} else if ($userinfo['status'] == 'banned') {
				$sheel->print_notice_popup_login('{_you_have_been_banned}' . ' ' . SITE_NAME, '{_you_have_been_banned}' . ' ' . SITE_NAME, HTTPS_SERVER . 'signin/?cmd=contact&amp;subcmd=banned', '{_contact_customer_support}');
				exit();
			} else {
				refresh(HTTPS_SERVER . 'signin/?error=' . $userinfo['status']);
				exit();
			}

		} else { // incorrect username and/or password entered by the user
			$sheel->GPC['username'] = isset($sheel->GPC['username']) ? $sheel->GPC['username'] : '';
			$sheel->GPC['password'] = isset($sheel->GPC['password']) ? $sheel->GPC['password'] : '';
			$sheel->db->query("
				INSERT INTO " . DB_PREFIX . "failed_logins
				(id, attempted_username, attempted_password, ip_address, datetime_failed)
				VALUES(
				NULL,
				'" . $sheel->db->escape_string($sheel->GPC['username']) . "',
				'" . $sheel->db->escape_string($sheel->GPC['password']) . "',
				'" . $sheel->db->escape_string(IPADDRESS) . "',
				'" . DATETIME24H . "')
			");
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "users
				SET failedlogins = failedlogins + 1
				WHERE username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "' OR email = '" . $sheel->db->escape_string($sheel->GPC['username']) . "'
				LIMIT 1
			");
			$sheel->log_event(0, basename(__FILE__), "failure\n" . $sheel->array2string($sheel->GPC), 'Failed signin attempt', $sheel->GPC['username'] . ' just attempted to signin and failed from IP ' . IPADDRESS . '.');
			if ($sheel->config['globalsecurity_emailonfailedlogins']) { // count number of login attempts since last successful login
				$sqlf = $sheel->db->query("
					SELECT failedlogins
					FROM " . DB_PREFIX . "users
					WHERE username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "' OR email = '" . $sheel->db->escape_string($sheel->GPC['username']) . "'
					LIMIT 1
				");
				$res = $sheel->db->fetch_array($sqlf, DB_ASSOC);
				if ($res['failedlogins'] >= $sheel->config['globalsecurity_numfailedloginattempts']) { // send site admin an email informing them of a suspicious login attempt
					$userid = $sheel->db->fetch_field(DB_PREFIX . "users", "username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "' OR email = '" . $sheel->db->escape_string($sheel->GPC['username']) . "'", "user_id");
					if ($userid > 0) { // valid user id
						// did we already sent email for this user to admin today?
						$sqle = $sheel->db->query("
							SELECT emaillogid
							FROM " . DB_PREFIX . "emaillog
							WHERE varname = 'failed_login_attempt_admin'
								AND user_id = '" . intval($userid) . "'
								AND `date` LIKE '%" . DATETODAY . "%'
							LIMIT 1
						");
						if ($sheel->db->num_rows($sqle) == 0) {
							$sheel->email->mail = SITE_CONTACT;
							$sheel->email->slng = $sheel->language->fetch_site_slng();
							$sheel->email->get('failed_login_attempt_admin');
							$sheel->email->set(
								array(
									'{{remote_addr}}' => IPADDRESS,
									'{{num_attempts}}' => $res['failedlogins'],
									'{{date_time}}' => DATETIME24H,
									'{{username}}' => $sheel->GPC['username'],
									'{{password}}' => $sheel->GPC['password']
								)
							);
							$sheel->email->send();
						}
					}
				}
				$landing = '';
				if (!empty($redirect)) {
					$landing = '&redirect=' . urlencode($redirect);
				}
				if ($sheel->GPC['login_process'] == '2') {
					refresh(HTTPS_SERVER_ADMIN . 'signin/?error=1' . $landing);
					exit();
				} else {
					refresh(HTTPS_SERVER . 'signin/?error=1' . $landing);
					exit();
				}
			} else {
				$landing = '';
				if (!empty($redirect)) {
					$landing = '&redirect=' . urlencode($redirect);
				}
				if ($sheel->GPC['login_process'] == '2') {
					refresh(HTTPS_SERVER_ADMIN . 'signin/?error=1' . $landing);
					exit();
				} else {
					refresh(HTTPS_SERVER . 'signin/?error=1' . $landing);
					exit();
				}
			}
		}
	} else {
		$landing = '';
		if (!empty($redirect)) {
			$landing = '&redirect=' . urlencode($redirect);
		}
		refresh(HTTPS_SERVER . 'signin/?error=1' . $landing);
		exit();
	}
}
if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == '_pw-renew') { // renew password
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_request_account_password}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password}';
	$sheel->show['nobreadcrumb'] = true;
	$vars = array(
		'header_text' => '{_recover_my_password}'
	);

	$sheel->template->fetch_popup('main', 'login_password_renewal.html');
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();



} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == '_do-pw-request' and isset($sheel->GPC['email']) and !empty($sheel->GPC['email'])) { // user requesting password
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_account_password_verification}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_account_password_verification}';
	$sheel->show['nobreadcrumb'] = true;
	$sheel->GPC['email'] = trim($sheel->GPC['email']);
	if ($sheel->common->is_email_valid($sheel->GPC['email'])) {
		$sql = $sheel->db->query("
					SELECT user_id, email, username, first_name, last_name, phone, status
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $sheel->GPC['email'] . "'
					LIMIT 1
				");
		if ($sheel->db->num_rows($sql) > 0) {
			$user = $sheel->db->fetch_array($sql, DB_ASSOC);
			$otpcode = $sheel->code->get_code($user['user_id'], '2', 5);
			if ($otpcode['expired'] == 1) {
				$sheel->email->mail = $user['email'];
				$sheel->email->slng = $sheel->language->fetch_user_slng($user['user_id']);
				$sheel->email->get('forgot_password');
				$sheel->email->set(
					array(
						'{{username}}' => $user['username'],
						'{{user_id}}' => $user['user_id'],
						'{{first_name}}' => $user['first_name'],
						'{{last_name}}' => $user['last_name'],
						'{{code}}' => $otpcode['code']
					)
				);
				$sheel->email->send();
			}
			$vars = array(
				'email' => $user['email'],
				'username' => $user['username'],
				'expiry' => $otpcode['expiry'],
				'header_text' => '{_recover_my_password}'
			);
			$sheel->template->fetch_popup('main', 'login_password_change.html');
			$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
			$sheel->template->pprint('main', $vars);
			exit();
		} else {
			$sheel->print_notice_popup_login('{_please_enter_a_valid_email_address}', '{_please_enter_a_valid_email_address}', HTTPS_SERVER . 'signin/?cmd=_pw-renew', '{_retry}');
		}
	}
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_account_password_verification} - {_request_account_password_denied}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password_denied}';
	$sheel->print_notice_popup_login('{_request_account_password_denied}', '{_request_account_password_denied}', HTTPS_SERVER . 'signin/?cmd=_pw-renew', '{_retry}');
	exit();
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == '_do-pw-after-otp' and isset($sheel->GPC['otp']) and isset($sheel->GPC['email']) and isset($sheel->GPC['username'])) { // user requesting password
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_account_password_verification}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_account_password_verification}';
	$sheel->show['nobreadcrumb'] = true;
	$otp = strip_tags($sheel->GPC['otp']);
	$email = strip_tags($sheel->GPC['email']);
	$username = strip_tags($sheel->GPC['username']);
	$group = 'RST';
	$sql = $sheel->db->query("
			SELECT user_id, email, username, status
			FROM " . DB_PREFIX . "users
			WHERE email = '" . $email . "'
			LIMIT 1
			");
	if ($sheel->db->num_rows($sql) > 0) {
		$user = $sheel->db->fetch_array($sql, DB_ASSOC);
		$sqlgroup = $sheel->db->query("
			SELECT id
			FROM " . DB_PREFIX . "code_groups
			WHERE code = '" . $group . "'
			LIMIT 1
			");

		if ($sheel->db->num_rows($sqlgroup) > 0) {
			$codegroup = $sheel->db->fetch_array($sqlgroup, DB_ASSOC);
			$dbcode = $sheel->code->get_code($user['user_id'], $codegroup['id']);
			
			if ($otp == $dbcode['code']) {
				$sheel->code->set_verified($dbcode['id']);
			}
			else {
				$sheel->print_notice_popup_login('{_wrong_otp_code}', '{_request_account_password_denied}', HTTPS_SERVER . 'signin/?cmd=_do-pw-request&email='.$email, '{_retry}');
			}
			$vars = array(
				'email' => $user['email'],
				'username' => $user['username'],
				'header_text' => '{_recover_my_password}'
			);
			$sheel->template->fetch_popup('main', 'login_password_after_otp.html');
			$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
			$sheel->template->pprint('main', $vars);
			exit();
		} else {
			$sheel->print_notice_popup_login('{_wrong_otp_group}', '{_request_account_password_denied}', HTTPS_SERVER . 'signin/?cmd=_pw-renew', '{_retry}');
		}
	}
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_account_password_verification} - {_request_account_password_denied}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password_denied}';
	$sheel->print_notice_popup_login('{_request_account_password_denied}', '{_request_account_password_denied}', HTTPS_SERVER . 'signin/?cmd=_pw-renew', '{_retry}');
	exit();

} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'password-change' and isset($sheel->GPC['email']) and isset($sheel->GPC['username']) and isset($sheel->GPC['password']) and isset($sheel->GPC['password2'])) { // user changing password
	$email = strip_tags($sheel->GPC['email']);
	$username = strip_tags($sheel->GPC['username']);
	$password =  strip_tags($sheel->GPC['password']);
	$password2 =  strip_tags($sheel->GPC['password2']);
	$sql = $sheel->db->query("
		SELECT user_id
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $sheel->db->escape_string($email) . "'
	");
	if ($sheel->db->num_rows($sql) > 0) {
		$res = $sheel->db->fetch_array($sql, DB_ASSOC);
		$userid = $res['user_id'];
	} else {
		$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_account_password_verification} - {_request_account_password_denied}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password_denied}';
		$sheel->print_notice_popup_login('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . 'signin/?cmd=_pw-renew', '{_retry}');
		exit();
	}
	if ($password == $password2) {
		$salt = $sheel->construct_password_salt(5);
		$newpasswordmd5 = md5(md5($password) . $salt);
		$sheel->db->query("
			UPDATE " . DB_PREFIX . "users
			SET password = '" . $sheel->db->escape_string($newpasswordmd5) . "',
			salt = '" . $sheel->db->escape_string($salt) . "',
			password_lastchanged = '" . DATETIME24H . "'
			WHERE user_id = '" . intval($userid) . "'
		");
		$sheel->email->mail = $email;
		$sheel->email->slng = $_SESSION['sheeldata']['user']['slng'];
		$sheel->email->get('password_renewed');
		$sheel->email->set(
			array(
				'{{username}}' => $username
			)
		);
		$sheel->email->send();
		$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_account_password_renewal_success}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_account_password_renewal_success}';
		$sheel->print_notice_popup_login('{_your_account_password_was_changed}', '{_you_have_successfully_renewed_the_password_for_your_online_account}', HTTPS_SERVER . 'signin/', '{_login_to_your_account}');
		exit();
	} else {
		$sql = $sheel->db->query("
			SELECT email
			FROM " . DB_PREFIX . "users
			WHERE username = '" . $sheel->db->escape_string($username) . "'
		");
		if ($sheel->db->num_rows($sql) > 0) {
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			$sheel->email->mail = $res['email'];
			$sheel->email->slng = $_SESSION['sheeldata']['user']['slng'];
			$sheel->email->get('password_recovery_denied');
			$sheel->email->set(
				array(
					'{{username}}' => $username,
					'{{ipaddress}}' => IPADDRESS,
					'{{agent}}' => USERAGENT
				)
			);
			$sheel->email->send();
			$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_request_account_password_denied}</div>';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password_denied}';
			$sheel->print_notice_popup_login('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . 'signin/', '{_sign_in}');
			exit();
		} else {
			$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_request_account_password_denied}</div>';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_request_account_password_denied}';
			$sheel->print_notice_popup_login('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . 'signin/', '{_sign_in}');
			exit();
		}
	}
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == '_ip-reset') { // ip address security reset
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_ip_preference}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_ip_preference}';
	$sheel->show['nobreadcrumb'] = true;
	$vars = array(
		'header_text' => '{_ip_reset}'
	);
	$sheel->template->fetch_popup('main', 'login_ipaddress_renewal.html');
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == '_do-ip-change' and isset($sheel->GPC['email']) and !empty($sheel->GPC['email'])) { // user requesting ip preference change
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_ip_preference}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_ip_preference}';
	$sheel->show['nobreadcrumb'] = true;
	$sql = $sheel->db->query("
		SELECT username
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $sheel->db->escape_string($sheel->GPC['email']) . "'
	");
	if ($sheel->db->num_rows($sql) > 0) {
		$res = $sheel->db->fetch_array($sql);
		$email = $sheel->GPC['email'];
		$username = stripslashes($res['username']);
		$vars = array('email' => $email, 'username' => $username);
		$sheel->template->fetch_popup('main', 'login_ipaddress_change.html');
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
		exit();
	} else {
		$sheel->template->meta['areatitle'] = '{_signin}<div class="type--subdued">{_change_ip_preference_denied}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_ip_preference_denied}';
		$sheel->print_notice_popup_login('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . 'signin/?cmd=_ip-reset', '{_retry}');
		exit();
	}
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'ipaddress-change' and isset($sheel->GPC['secretanswer']) and isset($sheel->GPC['email']) and isset($sheel->GPC['username'])) { // user changing ip preference
	$secretanswer = strip_tags($sheel->GPC['secretanswer']);
	$secretanswermd5 = md5($secretanswer);
	$email = strip_tags($sheel->GPC['email']);
	$username = strip_tags($sheel->GPC['username']);
	$sql = $sheel->db->query("
		SELECT user_id, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $sheel->db->escape_string($email) . "'
	");
	if ($sheel->db->num_rows($sql) > 0) {
		$res = $sheel->db->fetch_array($sql, DB_ASSOC);
		$userid = $res['user_id'];
		$secretanswerdb = stripslashes($res['secretanswer']);
	} else {
		$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_ip_preference_denied}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_ip_preference_denied}';
		$sheel->print_notice_popup_login('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . 'signin/?cmd=_ip-reset', '{_retry}');
		exit();
	}
	if ($secretanswermd5 == $secretanswerdb) {
		$sheel->db->query("
			UPDATE " . DB_PREFIX . "users
			SET iprestrict = '0'
			WHERE user_id = '" . intval($userid) . "'
			LIMIT 1
		");
		$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_ip_address_preference_changed}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_ip_address_preference_changed}';
		$sheel->print_notice_popup_login('{_ip_address_preference_changed}', '{_you_successfully_reset_the_ip_address_preference_for_your_account}', HTTPS_SERVER . 'signin/', '{_login_to_your_account}');
		exit();
	} else {
		$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_change_ip_preference_denied}</div>';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_change_ip_preference_denied}';
		$sheel->print_notice_popup_login('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . 'signin/?cmd=_ip-reset', '{_retry}');
		exit();
	}
} else { // sign-in
	$sheel->show['slimheader'] = true;
	$sheel->show['slimfooter'] = true;
	$sheel->show['nobreadcrumb'] = true;
	$sheel->template->meta['areatitle'] = '{_sign_in}<div class="type--subdued">{_login_area_menu}</div>';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_login_area_menu}';
	if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
		refresh(HTTPS_SERVER);
		exit();
	} else {
		$rolepulldown = '';
		if ($sheel->config['registrationdisplay_quickregistration']) {
			$sqlrolepulldown = $sheel->db->query("
				SELECT roleid, purpose_" . $_SESSION['sheeldata']['user']['slng'] . " AS purpose, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, custom, roletype, roleusertype, active
				FROM " . DB_PREFIX . "roles
				WHERE active = '1'
			", 0, null, __FILE__, __LINE__);
			$rerole = $sheel->db->fetch_array($sqlrolepulldown, DB_ASSOC);
			$rolesql = $sheel->db->num_rows($sqlrolepulldown);
			if ($rolesql > 1) {
				$sheel->show['rolescount'] = true;
				$rolepulldown = $sheel->role->print_role_pulldown(0, 1, 0, 0, '', $_SESSION['sheeldata']['user']['slng'], 'draw-select', 'roleid', 'roleid');
			} else {
				$sheel->show['rolescount'] = false;
				if ($rolesql == 1) { // only 1 visible role available, use it as default since we don't show the pull down if there is only one available
					$rolepulldown = '<input type="hidden" name="roleid" id="roleid" value="' . $rerole['roleid'] . '" />';
				} else { // all plans hidden from registration..
					if ($sheel->config['subscriptions_defaultroleid_product'] > 0) {
						$defaultroleid = $sheel->config['subscriptions_defaultroleid_product'];
					} else {
						$defaultroleid = '-1';
					}
					$rolepulldown = '<input type="hidden" name="roleid" id="roleid" value="' . $defaultroleid . '" />';
				}
			}
		}
		$user_cookie = ((!empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? $sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']) : '');
		$lastvisit = ((!empty($_COOKIE[COOKIE_PREFIX . 'lastvisit'])) ? $sheel->common->print_date($_COOKIE[COOKIE_PREFIX . 'lastvisit'], $sheel->config['globalserverlocale_globaltimeformat'], 0, 0) : '{_never}');
		$username = ((isset($sheel->GPC['username'])) ? o($sheel->GPC['username']) : $user_cookie);
		$password = ((isset($sheel->GPC['password'])) ? o($sheel->GPC['password']) : '');
		$note = ((isset($sheel->GPC['note'])) ? o($sheel->GPC['note']) : '');
		$uid = ((isset($sheel->GPC['uid'])) ? intval($sheel->GPC['uid']) : '');
		$redirect_page = ((isset($sheel->GPC['redirect'])) ? '?redirect=' . urlencode($sheel->GPC['redirect']) : '');
		$cc = ((isset($_SERVER['GEOIP_COUNTRYCODE']) and !empty($_SERVER['GEOIP_COUNTRYCODE'])) ? $_SERVER['GEOIP_COUNTRYCODE'] : $sheel->common_location->fetch_country_id($sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']));
		$country = ((isset($_SERVER['GEOIP_COUNTRY']) and !empty($_SERVER['GEOIP_COUNTRY'])) ? $_SERVER['GEOIP_COUNTRY'] : $sheel->common_location->fetch_country_id($sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']));
		$vars = array(
			'rolepulldown' => $rolepulldown,
			'username' => $username,
			'password' => $password,
			'redirect_page' => $redirect_page,
			'lastvisit' => $lastvisit,
			'redirect' => 'home/',
			'user_cookie' => $user_cookie,
			'note' => $note,
			'uid' => $uid,
			'cc' => $cc,
			'country' => $country,
			'header_text' => '{_login}'
		);

		$sheel->template->fetch_popup('main', 'login.html');
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
		exit();
	}
}
?>