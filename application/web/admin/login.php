<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'vendor/jquery_' . JQUERYVERSION,
		'vendor/growl',
		'admin',
		'login',
        'password_strength'
	),
	'footer' => array(
		'md5',
		'others'
	)
);
$sheel->template->meta['cssinclude'] = array(
	'login',
	'vendor' => array(
		'font-awesome',
		'glyphicons',
		'growl'
	)
);
$sheel->template->meta['navcrumb'] = array($sheel->ilpage['login'] => $sheel->ilcrumbs[$sheel->ilpage['login']]);
$cmd = $sidenav = '';

if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'signout') {
	$sheel->template->meta['areatitle'] = '{_logging_out}';
	$sheel->template->meta['pagetitle'] = '{_logging_out}';

	set_cookie('lastvisit', DATETIME24H);
	set_cookie('lastactivity', DATETIME24H);
	set_cookie('userid', '', 0, false);
	set_cookie('password', '', 0, false);
	set_cookie('inlineproduct', '', 0, false);
	set_cookie('inlineservice', '', 0, false);
	set_cookie('inlineprovider', '', 0, false);
	$sheel->sessions->session_destroy(session_id());
	session_destroy();
	refresh(HTTPS_SERVER_ADMIN);
	exit();
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'renew-password') {
	$sheel->template->meta['areatitle'] = '{_admin_password_renewal_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_password_renewal_menu}';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) {
		$admin_cookie = $sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']);
	}
	$vars = array('admin_cookie' => $admin_cookie, 'sidenav' => '');
	$sheel->template->fetch('main', 'login_pwrenew.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'renew-password-otp') {
	$sheel->template->meta['areatitle'] = '{_admin_password_renewal_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_password_renewal_menu}';
	$sql = $sheel->db->query("
					SELECT user_id, email, username, first_name, last_name, phone, status
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $sheel->GPC['email'] . "'	
					AND isadmin = '1'
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
			'expiry' => $otpcode['expiry'],
			'sidenav' => ''
		);
		$sheel->template->fetch('main', 'login_pwotp.html', 1);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
		exit();
	} else {
		$sheel->show['error_login'] = '1';
		$vars = array(
			'email' => $user['email'],
			'expiry' => $otpcode['expiry'],
			'admin_cookie' => $admin_cookie,
			'sidenav' => ''
		);
		$sheel->template->fetch('main', 'login_pwrenew.html', 1);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
	}
} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'renew-password-after-otp' and isset($sheel->GPC['otp']) and isset($sheel->GPC['email'])) { // user validating otp
	$sheel->template->meta['areatitle'] = '{_admin_password_renewal_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_password_renewal_menu}';
	$otp = strip_tags($sheel->GPC['otp']);
	$email = strip_tags($sheel->GPC['email']);
	$group = 'RST';
	$sql = $sheel->db->query("
			SELECT user_id, email, username, status
			FROM " . DB_PREFIX . "users
			WHERE email = '" . $email . "'
			AND isadmin = '1'
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
			} else {
				$sheel->show['error_otp'] = '1';
				$vars = array(
					'email' => $user['email'],
					'expiry' => $dbcode['expiry'],
					'admin_cookie' => $admin_cookie,
					'sidenav' => ''
				);
				$sheel->template->fetch('main', 'login_pwotp.html', 1);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
				$sheel->template->pprint('main', $vars);
				exit();
			}
			$vars = array(
				'email' => $user['email'],
				'admin_cookie' => $admin_cookie,
				'sidenav' => ''
			);
		}
	}
	$vars = array(
		'email' => $user['email'],
		'otp' => $dbcode['code'],
		'admin_cookie' => $admin_cookie,
		'sidenav' => ''
	);
	$sheel->template->fetch('main', 'login_pwotp_after.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();

} else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'renew-password-change' and isset($sheel->GPC['email']) and isset($sheel->GPC['password']) and isset($sheel->GPC['password2'])) { // user changing password
	$email = strip_tags($sheel->GPC['email']);
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
		$sheel->show['error_email'] = '1';
		$vars = array(
			'email' => $sheel->GPC['email'],
			'otp' => $sheel->GPC['otp'],
			'admin_cookie' => $admin_cookie,
			'sidenav' => ''
		);
		$sheel->template->fetch('main', 'login_pwotp_after.html', 1);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
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
		$sheel->show['passwordchange'] = 'success';
		
		$sheel->template->meta['areatitle'] = '{_login_area_menu}';
		$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_login_area_menu}';
		$username = isset($sheel->GPC['username']) ? o($sheel->GPC['username']) : '';
		$password = isset($sheel->GPC['password']) ? o($sheel->GPC['password']) : '';
		$redirect = '/admin/';
		$vars = array('username' => $username, 'password' => $password, 'redirect' => $redirect, 'sidenav' => '');
		$sheel->template->fetch('main', 'login.html', 1);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
		exit();
	} else {
		$sheel->show['error_password'] = '1';
		$vars = array(
			'cmd' => 'renew-password-after-otp',
			'email' => $sheel->GPC['email'],
			'otp' => $sheel->GPC['otp'],
			'sidenav' => ''
		);
		$sheel->template->fetch('main', 'login_pwotp_after.html', 1);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', $vars);
		exit();
	}
} else { // sign-in
	$sheel->template->meta['areatitle'] = '{_login_area_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_login_area_menu}';
	$username = isset($sheel->GPC['username']) ? o($sheel->GPC['username']) : '';
	$password = isset($sheel->GPC['password']) ? o($sheel->GPC['password']) : '';
	$redirect = HTTPS_SERVER_ADMIN;
	if (!empty($sheel->GPC['redirect'])) {
		$redirect = trim($sheel->GPC['redirect']);
	}
	$sheel->template->meta['onload'] .= (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? 'document.login.password.focus();' : 'document.login.username.focus();';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username']) and empty($sheel->GPC['username'])) {
		$admin_cookie = $sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']);
		$username = $admin_cookie;
	}
	if (!empty($_SESSION['sheeldata']['user']['userid']) and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
		header("Location: " . HTTPS_SERVER_ADMIN);
		exit();
	}
	$vars = array('username' => $username, 'password' => $password, 'redirect' => $redirect, 'sidenav' => '');
	$sheel->template->fetch('main', 'login.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();
}


?>