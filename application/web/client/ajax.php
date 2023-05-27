<?php
define('LOCATION', 'ajax');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array('header' => array('functions'));
$sheel->template->meta['cssinclude'] = array(
	'header',
	'thumbnail',
	'footer',
	'common',
	'vendor' => array(
		'balloon',
		'font-awesome',
		'bootstrap'
	)
);
// only skip session if method doesn't change active session in any way
$methods = array(
	'updatebulkstaff' => array('skipsession' => false),
	'updatebulkmeasurement' => array('skipsession' => false),
	'updatebulksize' => array('skipsession' => false),
	'check_email' => array('skipsession' => true),
	'quickregister' => array('skipsession' => false),
	'showstates' => array('skipsession' => true),
	'showcities' => array('skipsession' => true),
	'heropicture' => array('skipsession' => true),
	'build' => array('skipsession' => true),
	'version' => array('skipsession' => true),
	'bulkmailer' => array('skipsession' => true),
	'upgradelog' => array('skipsession' => true),
	'ldaplog' => array('skipsession' => true),
	'smtplog' => array('skipsession' => true),
	'forceautoupdate' => array('skipsession' => true),
	'consent' => array('skipsession' => false),
	'tzoffset' => array('skipsession' => true)
);

if (isset($sheel->GPC['do'])) {
	if (isset($methods[$sheel->GPC['do']]['skipsession'])) {
		define('SKIP_SESSION', $methods[$sheel->GPC['do']]['skipsession']);
	} else {
		define('SKIP_SESSION', false);
	}
	if ($sheel->GPC['do'] == 'updatebulkstaff') {
		if (!empty($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "bulk_tmp_staffs
					SET name = '" . $sheel->GPC['name'] . "',
						gender = '" . $sheel->GPC['gender'] . "',
						positioncode = '" . $sheel->GPC['position'] . "',
						departmentcode = '" . $sheel->GPC['department'] . "'
					WHERE id = '" . $sheel->GPC['id'] . "'
					", 0, null, __FILE__, __LINE__);
				$result = array(
					'error' => '0',
					'message' => 'Record Updated',
					'timestamp' => ''
				);
			} else {
				$result = array(
					'error' => '1',
					'message' => 'No Record ID Provided',
					'timestamp' => ''
				);
			}
		} else {
			$result = array(
				'error' => '1',
				'message' => 'Session expired. Please login.',
				'timestamp' => ''
			);
		}
		$json = json_encode($result);
		http_response_code(200);
		echo $json;
		exit();
	} else if ($sheel->GPC['do'] == 'updatebulkmeasurement') {
		if (!empty($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
				$sheel->db->query("
				UPDATE " . DB_PREFIX . "bulk_tmp_measurements
				SET staffcode = '" . $sheel->GPC['staffcode'] . "',
					measurementcategory = '" . $sheel->GPC['measurementcategory'] . "',
					positioncode = '" . $sheel->GPC['position'] . "',
					departmentcode = '" . $sheel->GPC['department'] . "',
					mvalue = '" . $sheel->GPC['mvalue'] . "',
					uom = '" . $sheel->GPC['uom'] . "'
				WHERE id = '" . $sheel->GPC['id'] . "'
				", 0, null, __FILE__, __LINE__);
				$result = array(
					'error' => '0',
					'message' => 'Record Updated',
					'timestamp' => ''
				);
			} else {
				$result = array(
					'error' => '1',
					'message' => 'No Record ID Provided',
					'timestamp' => ''
				);
			}
		} else {
			$result = array(
				'error' => '1',
				'message' => 'Session expired. Please login.',
				'timestamp' => ''
			);
		}
		$json = json_encode($result);
		http_response_code(200);
		echo $json;
		exit();
	} else if ($sheel->GPC['do'] == 'updatebulksize') {
		if (!empty($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
				$sheel->db->query("
				UPDATE " . DB_PREFIX . "bulk_tmp_sizes
				SET staffcode = '" . $sheel->GPC['staffcode'] . "',
					positioncode = '" . $sheel->GPC['position'] . "',
					departmentcode = '" . $sheel->GPC['department'] . "',
					fit = '" . $sheel->GPC['fit'] . "',
					cut = '" . $sheel->GPC['cut'] . "',
					size = '" . $sheel->GPC['size'] . "',
					type = '" . $sheel->GPC['type'] . "'
				WHERE id = '" . $sheel->GPC['id'] . "'
				", 0, null, __FILE__, __LINE__);
				$result = array(
					'error' => '0',
					'message' => 'Record Updated',
					'timestamp' => ''
				);
			} else {
				$result = array(
					'error' => '1',
					'message' => 'No Record ID Provided',
					'timestamp' => ''
				);
			}
		} else {
			$result = array(
				'error' => '1',
				'message' => 'Session expired. Please login.',
				'timestamp' => ''
			);
		}
		$json = json_encode($result);
		http_response_code(200);
		echo $json;
		exit();
	} else if ($sheel->GPC['do'] == 'check_email') {
		if (isset($sheel->GPC['email_user'])) {
			$add_customer['status'] = $add_customer['status1'] = true;
			$sql = $sheel->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE email = '" . $sheel->db->escape_string($sheel->GPC['email']) . "'
			", 0, null, __FILE__, __LINE__);
			$html = " ";
			if ($sheel->db->num_rows($sql) > 0) {
				$add_customer['status1'] = false;
				$html = "0";
			}
			$sql1 = $sheel->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql1) > 0) {
				$add_customer['status'] = false;
				$html = "1";
			}
			if ($sheel->db->num_rows($sql) > 0 and $sheel->db->num_rows($sql1) > 0) {
				$html = "2";
			}
			if ($sheel->db->num_rows($sql) == 0 and $sheel->db->num_rows($sql1) == 0) {
				$html = "3";
			}
			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'cbcountries') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$sql = $sheel->db->query("
				SELECT locationid, location_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "locations
				WHERE visible = '1'
				ORDER BY location_" . $_SESSION['sheeldata']['user']['slng'] . " ASC
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				$rc = 0;
				while ($res = $sheel->db->fetch_array($sql)) {
					$res['class'] = ($rc % 2) ? 'alt2' : 'alt1';
					$res['cb'] = '<input type="checkbox" name="locationid[]" value="' . $res['locationid'] . '" />';
					$res['title'] = stripslashes($res['title']);
					$countries[] = $res;
					$rc++;
				}
				$sheel->template->load_popup('head', 'popup_header.html');
				$sheel->template->load_popup('main', 'ajax_countries.html');
				$sheel->template->load_popup('foot', 'popup_footer.html');
				$sheel->template->parse_loop('main', array('countries' => $countries));
				$sheel->template->pprint('head', array('headinclude' => $sheel->template->meta['headinclude'], 'onbeforeunload' => '', 'onload' => $sheel->template->meta['onload']));
				$sheel->template->pprint('main', array());
				$sheel->template->pprint('foot', array('footinclude' => $sheel->template->meta['footinclude']));
				exit();
			} else {
				echo 'Could not fetch country list at this time.';
				exit();
			}
		}
	} else if ($sheel->GPC['do'] == 'quickregister') {
		if (isset($sheel->GPC['qusername']) and isset($sheel->GPC['qpassword']) and isset($sheel->GPC['qemail'])) {
			$sheel->GPC['source'] = 'Quick Registration';
			$sheel->GPC['output'] = 'return_userarray';
			$response = $sheel->registration->quick($sheel->GPC);
			if (!$response) {
				if (count($sheel->registration->quickregistererrors) > 0) {
					foreach ($sheel->registration->quickregistererrors as $error) {
						echo $error;
						exit();
					}
				}
				$sheel->template->templateregistry['quickregister_notice'] = '{_unknown_error}';
				echo $sheel->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			die($response); // 1 = active, 2 = unverified, 3 = moderated
		} else {
			$sheel->template->templateregistry['quickregister_notice'] = '{_please_answer_all_required_fields}';
			echo $sheel->template->parse_template_phrases('quickregister_notice');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showstates') { // show states based on selected country
		if (isset($sheel->GPC['countryname']) and !empty($sheel->GPC['countryname']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			if ($sheel->GPC['countryname'] > 0) {
				$locationid = intval($sheel->GPC['countryname']);
			} else {
				$locationid = $sheel->common_location->fetch_country_id($sheel->GPC['countryname'], $_SESSION['sheeldata']['user']['slng']);
			}
			$shortform = isset($sheel->GPC['shortform']) ? intval($sheel->GPC['shortform']) : 0;
			$extracss = ((isset($sheel->GPC['extracss']) and !empty($sheel->GPC['extracss'])) ? $sheel->GPC['extracss'] : '');
			$disablecities = isset($sheel->GPC['disablecities']) ? intval($sheel->GPC['disablecities']) : 1;
			$citiesfieldname = isset($sheel->GPC['citiesfieldname']) ? $sheel->GPC['citiesfieldname'] : 'city';
			$citiesdivid = isset($sheel->GPC['citiesdivid']) ? $sheel->GPC['citiesdivid'] : 'cityid';
			$html = $sheel->common_location->construct_state_pulldown($locationid, '', $sheel->GPC['fieldname'], false, true, $shortform, $extracss, $disablecities, $citiesfieldname, $citiesdivid);


			$sheel->template->templateregistry['showstates'] = $html;
			echo $sheel->template->parse_template_phrases('showstates');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showcities') { // show cities based on selected state
		if (isset($sheel->GPC['state']) and !empty($sheel->GPC['state']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$extracss = ((isset($sheel->GPC['extracss']) and !empty($sheel->GPC['extracss'])) ? $sheel->GPC['extracss'] : '');
			$currentcitiesclass = ((isset($sheel->GPC['currentcitiesclass'])) ? $sheel->GPC['currentcitiesclass'] : ''); // input w-350
			$currentstateclasswidth = ((isset($sheel->GPC['currentstateclasswidth'])) ? $sheel->GPC['currentstateclasswidth'] : 'w-170'); // w-350
			$html = $sheel->common_location->construct_city_pulldown($sheel->GPC['state'], $sheel->GPC['fieldname'], '', false, true, $extracss, true, $currentstateclasswidth, true, $currentcitiesclass);


			$sheel->template->templateregistry['showcities'] = $html;
			echo $sheel->template->parse_template_phrases('showcities');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'build') {
		die('001');
	} else if ($sheel->GPC['do'] == 'version') {
		die(VERSION . '.' . ((SVNVERSION == '001') ? '0' : SVNVERSION));
	} else if ($sheel->GPC['do'] == 'bulkmailer') { // admin panel bulk mailer
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				die(json_encode(array('response' => '0')));
			}
			$sheel->template->meta['areatitle'] = '{_sending_bulk_email}';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_sending_bulk_email}';
			$from = trim($sheel->GPC['from']);
			$subject = o(urldecode($sheel->GPC['subject']));
			$message = urldecode($sheel->GPC['body']);
			$messagehtml = urldecode($sheel->GPC['bodyhtml']);
			$plan = false;
			$subscriptionid = '';
			if ($sheel->GPC['subscriptionid'] != '-1') {
				$plan = true;
				$subscriptionid = $sheel->GPC['subscriptionid'];
			}
			if (isset($sheel->GPC['testmode']) and $sheel->GPC['testmode'] == 'true' and isset($sheel->GPC['testemail']) and !empty($sheel->GPC['testemail'])) { // staff sending test email
				$sheel->template->meta['areatitle'] = '{_bulk_email_test_message}';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_bulk_email_test_message}';
				$find = array(
					'{{username}}' => $_SESSION['sheeldata']['user']['username'],
					'{{firstname}}' => $_SESSION['sheeldata']['user']['firstname'],
					'{{lastname}}' => $_SESSION['sheeldata']['user']['lastname'],
					'{{plantitle}}' => 'Test Plan Title',
					'{{planprice}}' => $sheel->currency->format(0),
					'{{planlinkpayment}}' => HTTPS_SERVER . 'accounting/billing-payments/',
					'{{planbillingcycle}}' => '',
					'{{twitter}}' => $sheel->config['globalserversettings_twitterurl'],
					'{{facebook}}' => $sheel->config['globalserversettings_facebookurl'],
					'{{linkedin}}' => $sheel->config['globalserversettings_linkedin'],
					'{{youtube}}' => $sheel->config['globalserversettings_youtubeurl'],
					'{{instagram}}' => $sheel->config['globalserversettings_instaurl'],
					'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/',
					'{{https_server}}' => HTTPS_SERVER
				);
				$message = str_replace(array_keys($find), $find, $message);
				$messagehtml = str_replace(array_keys($find), $find, $messagehtml);
				$subject = str_replace(array_keys($find), $find, $subject);
				$sheel->email->mail = o($sheel->GPC['testemail']);
				$sheel->email->from = $from;
				$sheel->email->subject = $subject;
				$sheel->email->message = $message;
				$sheel->email->messagehtml = $messagehtml;
				$sheel->email->type = 'global';
				$sheel->email->dohtml = ((!empty($messagehtml)) ? 1 : 0);
				$sheel->email->send();
				die(json_encode(array('response' => '1')));
			} else { // admin dispatching bulk mail to customers
				if ($subscriptionid == 'active') { // sending to only active
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'active'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'suspended') { // sending to only suspended
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'suspended'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'cancelled') { // sending to only cancelled users
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'cancelled'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'unverified') { // sending to only unverified email
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'unverified'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'banned') { // sending to only banned
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'banned'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'moderated') { // sending to only moderated/unapproved
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'moderated'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'orphaned') { // in a specific membership..
					$sql = $sheel->db->query("
						SELECT u.user_id, s.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, s.cost
						FROM " . DB_PREFIX . "subscription_user u
						LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
						WHERE u.active = 'no'
							AND s.type = 'product'
					", 0, null, __FILE__, __LINE__);
				} else if ($plan and $subscriptionid > 0) { // in a specific membership..
					$sql = $sheel->db->query("
						SELECT u.user_id, s.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, s.cost
						FROM " . DB_PREFIX . "subscription_user u
						LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
						WHERE u.subscriptionid = '" . intval($subscriptionid) . "'
							AND u.active = 'yes'
							AND s.type = 'product'
					", 0, null, __FILE__, __LINE__);
				} else { // sending to everyone
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
					", 0, null, __FILE__, __LINE__);
				}
				// send email..
				if ($sheel->db->num_rows($sql) > 0) {
					while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) { // to each customer
						$res['title'] = ((isset($res['title'])) ? $res['title'] : '');
						$res['cost'] = ((isset($res['cost'])) ? $sheel->currency->format($res['cost']) : '');
						$sql2 = $sheel->db->query("
							SELECT username, email, first_name, last_name
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res['user_id'] . "'
								AND emailnotify = '1'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($sheel->db->num_rows($sql2) > 0) {
							$res2 = $sheel->db->fetch_array($sql2, DB_ASSOC);
							$find = array(
								'{{username}}' => $res2['username'],
								'{{firstname}}' => ucfirst($res2['first_name']),
								'{{lastname}}' => ucfirst($res2['last_name']),
								'{{plantitle}}' => $res['title'],
								'{{planprice}}' => $res['cost'],
								'{{planlinkpayment}}' => HTTPS_SERVER . 'accounting/billing-payments/',
								'{{planbillingcycle}}' => '',
								'{{twitter}}' => $sheel->config['globalserversettings_twitterurl'],
								'{{facebook}}' => $sheel->config['globalserversettings_facebookurl'],
								'{{linkedin}}' => $sheel->config['globalserversettings_linkedin'],
								'{{youtube}}' => $sheel->config['globalserversettings_youtubeurl'],
								'{{instagram}}' => $sheel->config['globalserversettings_instaurl'],
								'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/',
								'{{https_server}}' => HTTPS_SERVER
							);
							$message = str_replace(array_keys($find), $find, $message);
							$messagehtml = str_replace(array_keys($find), $find, $messagehtml);
							$subject = str_replace(array_keys($find), $find, $subject);
							$sheel->email->mail = $res2['email'];
							$sheel->email->from = $from;
							$sheel->email->subject = $subject;
							$sheel->email->message = $message;
							$sheel->email->messagehtml = $messagehtml;
							$sheel->email->type = 'global';
							$sheel->email->dohtml = ((!empty($messagehtml)) ? 1 : 0);
							$sheel->email->send();
						}
					}
					die(json_encode(array('response' => '1')));
				}
			}
		}
		die(json_encode(array('response' => '0')));
	} else if ($sheel->GPC['do'] == 'upgradelog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = UPGRADELOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'ldaplog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = LDAPLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'smtplog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = SMTPLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'forceautoupdate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$sheel->liveupdate->activate_cron();
			die('success|');
		}
	} else if ($sheel->GPC['do'] == 'consent') {
		$types = array('cookieconsent', 'termsconsent', 'privacyconsent');
		if (isset($sheel->GPC['type']) and !empty($sheel->GPC['type']) and in_array($sheel->GPC['type'], $types)) {
			set_cookie($sheel->GPC['type'], 1);
		}
	} else if ($sheel->GPC['do'] == 'tzoffset') { // set tzoffset in seconds
		if (isset($sheel->GPC['ctz']) and !empty($sheel->GPC['ctz'])) {
			set_cookie('tzoffset', $sheel->datetimes->fetch_timezone_offset($sheel->config['globalserverlocale_sitetimezone'], $sheel->GPC['ctz']));
			set_cookie('timezone', $sheel->GPC['ctz']);
		}
	}
}
?>