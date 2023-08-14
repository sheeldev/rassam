<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params']))
{
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'admin',
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
		'growl',
		'balloon'
	)
);
$sheel->template->meta['areatitle'] = 'Admin CP | System Sessions';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | System Sessions';

if (($sidenav = $sheel->cache->fetch("sidenav_sessions")) === false)
{
	$sidenav = $sheel->admincp_nav->print('sessions');
	$sheel->cache->store("sidenav_sessions", $sidenav);
}
if (!empty($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] > 0 AND $_SESSION['sheeldata']['user']['isadmin'] == '1')
{
	if (file_exists(DIR_GEOIP . 'GeoLiteCity.dat') AND file_exists(DIR_GEOIP . 'GeoIP.dat'))
	{
		if (!function_exists('geoip_open'))
		{
			require_once(DIR_CLASSES . 'class.geoip.inc.php');
		}
		$geoip = geoip_open(DIR_GEOIP . 'GeoLiteCity.dat', GEOIP_STANDARD);
		$sheel->show['geoip'] = true;
	}
	if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'kick' AND !empty($sheel->GPC['xid']))
	{ // kick or expire session
		if (isset($sheel->GPC['xid']) AND !empty($sheel->GPC['xid']))
		{
			$sheel->db->query("
				DELETE FROM " . DB_PREFIX . "sessions
				WHERE ipaddress = '" . $sheel->db->escape_string($sheel->GPC['xid']) . "'
			", 0, null, __FILE__, __LINE__);
			die(json_encode(array('response' => 1, 'message' => 'Successfully kicked session for IP address ' . $sheel->GPC['xid'])));
		}
		else
		{
			$sheel->template->templateregistry['message'] = 'No session was selected.  Please try again.';
			die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
		}
	}
	$sheel->show['nomembers'] = $sheel->show['noguests'] = $sheel->show['noadmins'] = $sheel->show['nocrawlers'] = false;
	$guest_connection_results = $member_connection_results = $admin_connection_results = $crawler_connection_results = array();
	$row_count = 0;
	$sqlguest = $sheel->db->query("
		SELECT sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, ipaddress, url, title, firstclick, lastclick, browser, token, sesskeyapi, siteid, COUNT(ipaddress) AS connects
		FROM " . DB_PREFIX . "sessions
		WHERE userid = '0' AND isrobot = '0'
		GROUP BY token
		ORDER BY lastclick DESC
	", 0, null, __FILE__, __LINE__);
	if ($sheel->db->num_rows($sqlguest) > 0)
	{
		while ($row = $sheel->db->fetch_array($sqlguest, DB_ASSOC))
		{
			$row['checkbox'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'kick\', \'Kick session by ' . $row['ipaddress'] . '?\', \'Are you sure you want to kick this session?\', \'' . $row['ipaddress'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="Kick Session"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
			$row['username'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $row['userid'] . '/">' . $sheel->fetch_user('username', $row['userid']) . '</a>';
			$row['location_title'] = stripslashes($row['title']);
			$row['location'] = '<a href="' . (!empty($row['url']) ? substr(HTTPS_SERVER, 0, -1) . o($row['url']) : HTTPS_SERVER) . '" target="_blank" title="' . (!empty($row['url']) ? o($sheel->print_string_wrap($row['url'], 75)) : HTTPS_SERVER) . '">' . $row['location_title'] . '</a>';
			$row['os'] = $sheel->common->fetch_os_name($row['agent'], true);
			if ($sheel->common->is_tor_browser($row['ipaddress']))
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, 'tor') . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, 'tor') . '</a>';
			}
			else
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, $row['browser']) . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, $row['browser']) . '</a>';
			}
			$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
			if (isset($sheel->show['geoip']) AND $sheel->show['geoip'] == true)
			{
				$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
				$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
				$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
				$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
				$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
				$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
				unset($geo);
			}
			$row['ip_address'] = $row['ipaddress'];
			$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
			$row['lastclick'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['lastclick']);
			$row['duration'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['firstclick']);
			$row['expiresin'] = $sheel->common->sec2text($row['expiry'] - TIMESTAMPNOW);
			$row['class']  = ($row_count % 2) ? 'alt1' : 'alt1';
			$guest_connection_results[] = $row;
			$row_count++;
		}
		unset($row);
	}
	else
	{
		$sheel->show['noguests'] = true;
	}
	$guestsonline = $row_count;
	unset($sqlguest);
	$row_count = 0;
	$sqlmember = $sheel->db->query("
		SELECT sess.*, COUNT(sess.ipaddress) AS connects
		FROM " . DB_PREFIX . "users AS user,
		" . DB_PREFIX . "sessions AS sess
		WHERE sess.userid = user.user_id
			AND sess.isuser = '1'
			AND sess.userid > 0
		GROUP BY sess.token
		ORDER BY sess.lastclick DESC
	", 0, null, __FILE__, __LINE__);
	if ($sheel->db->num_rows($sqlmember) > 0)
	{
		while ($row = $sheel->db->fetch_array($sqlmember, DB_ASSOC))
		{
			$row['checkbox'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'kick\', \'Kick session by ' . $row['ipaddress'] . '?\', \'Are you sure you want to kick this session?\', \'' . $row['ipaddress'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="Kick Session"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
			$row['username'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $row['userid'] . '/">' . $sheel->fetch_user('username', $row['userid']) . '</a>';
			$row['location_title'] = stripslashes($row['title']);
			$row['location'] = '<a href="' . (!empty($row['url']) ? substr(HTTPS_SERVER, 0, -1) . o($row['url']) : HTTPS_SERVER) . '" target="_blank" title="' . (!empty($row['url']) ? o($sheel->print_string_wrap($row['url'], 75)) : HTTPS_SERVER) . '">' . $row['location_title'] . '</a>';
			$row['os'] = $sheel->common->fetch_os_name($row['agent'], true);
			if ($sheel->common->is_tor_browser($row['ipaddress']))
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, 'tor') . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, 'tor') . '</a>';
			}
			else
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, $row['browser']) . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, $row['browser']) . '</a>';
			}
			$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
			if (isset($sheel->show['geoip']) AND $sheel->show['geoip'] == true)
			{
				$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
				$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
				$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
				$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
				$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
				$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
				unset($geo);
			}
			$row['ip_address'] = $row['ipaddress'];
			$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
			$row['lastclick'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['lastclick']);
			$row['duration'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['firstclick']);
			$row['expiresin'] = $sheel->common->sec2text($row['expiry'] - TIMESTAMPNOW);
			$member_connection_results[] = $row;
			$row_count++;
		}
		unset($row);
	}
	else
	{
		$sheel->show['nomembers'] = true;
	}
	$membersonline = $row_count;
	$sqladmin = $sheel->db->query("
		SELECT sess.*, COUNT(sess.ipaddress) AS connects
		FROM " . DB_PREFIX . "users AS user,
		" . DB_PREFIX . "sessions AS sess
		WHERE sess.userid = user.user_id
			AND sess.userid > 0
			AND sess.isadmin = '1'
		GROUP BY sess.token
		ORDER BY sess.lastclick DESC
	", 0, null, __FILE__, __LINE__);
	if ($sheel->db->num_rows($sqladmin) > 0)
	{
		while ($row = $sheel->db->fetch_array($sqladmin, DB_ASSOC))
		{
			$row['checkbox'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'kick\', \'Kick session by ' . $row['ipaddress'] . '?\', \'Are you sure you want to kick this session?\', \'' . $row['ipaddress'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="Kick Session"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
			$row['username'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $row['userid'] . '/">' . $sheel->fetch_user('username', $row['userid']) . '</a>';
			$row['location_title'] = stripslashes($row['title']);
			$row['location'] = '<a href="' . (!empty($row['url']) ? substr(HTTPS_SERVER, 0, -1) . o($row['url']) : HTTPS_SERVER) . '" target="_blank" title="' . (!empty($row['url']) ? o($sheel->print_string_wrap($row['url'], 75)) : HTTPS_SERVER) . '">' . $row['location_title'] . '</a>';
			$row['os'] = $sheel->common->fetch_os_name($row['agent'], true);
			if ($sheel->common->is_tor_browser($row['ipaddress']))
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, 'tor') . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, 'tor') . '</a>';
			}
			else
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, $row['browser']) . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, $row['browser']) . '</a>';
			}
			$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
			if (isset($sheel->show['geoip']) AND $sheel->show['geoip'] == true)
			{
				$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
				$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
				$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
				$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
				$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
				$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
				unset($geo);
			}
			$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
			$row['ip_address'] = $row['ipaddress'];
			$row['lastclick'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['lastclick']);
			$row['duration'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['firstclick']);
			$row['expiresin'] = $sheel->common->sec2text($row['expiry'] - TIMESTAMPNOW);
			$admin_connection_results[] = $row;
		}
		unset($row);
	}
	else
	{
		$sheel->show['noadmins'] = true;
	}
	$staffonline = $row_count;
	unset($sqlmember);
	$row_count = 0;
	$sqlcrawlers = $sheel->db->query("
		SELECT sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, ipaddress, url, title, firstclick, lastclick, browser, token, sesskeyapi, siteid, COUNT(ipaddress) AS connects
		FROM " . DB_PREFIX . "sessions
		WHERE userid = '0'
			AND isrobot = '1'
		GROUP BY token
		ORDER BY lastclick DESC
	", 0, null, __FILE__, __LINE__);
	if ($sheel->db->num_rows($sqlcrawlers) > 0)
	{
		while ($row = $sheel->db->fetch_array($sqlcrawlers, DB_ASSOC))
		{
			$row['checkbox'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'kick\', \'Kick session by ' . $row['ipaddress'] . '?\', \'Are you sure you want to kick this session?\', \'' . $row['ipaddress'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="Kick Session"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
			$row['username'] = $sheel->common->fetch_search_crawler_title($row['agent']);
			$row['location_title'] = !empty($row['title']) ? stripslashes($row['title']) : '{_unknown}';
			$row['location'] = '<a href="' . (!empty($row['url']) ? substr(HTTPS_SERVER, 0, -1) . o($row['url']) : HTTPS_SERVER) . '" target="_blank" title="' . (!empty($row['url']) ? o($sheel->print_string_wrap($row['url'], 75)) : HTTPS_SERVER) . '">' . $row['location_title'] . '</a>';
			if ($sheel->common->is_tor_browser($row['ipaddress']))
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, 'tor') . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, 'tor') . '</a>';
			}
			else
			{
				$row['browser'] = '<a href="javascript:;" title="' . $sheel->common->fetch_browser_name(0, $row['browser']) . ': ' . stripslashes(o($row['agent'])) . '">' . $sheel->common->fetch_browser_name(1, $row['browser']) . '</a>';
			}
			$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
			if (isset($sheel->show['geoip']) AND $sheel->show['geoip'] == true)
			{
				$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
				$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
				$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
				$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
				$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
				$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
				unset($geo);
			}
			$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
			$row['ip_address'] = $row['ipaddress'];
			$row['lastclick'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['lastclick']);
			$row['duration'] = $sheel->common->sec2text(TIMESTAMPNOW - $row['firstclick']);
			$row['expiresin'] = $sheel->common->sec2text($row['expiry'] - TIMESTAMPNOW);
			$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
			$crawler_connection_results[] = $row;
			$row_count++;
		}
		unset($row);
	}
	else
	{
		$sheel->show['nocrawlers'] = true;
	}
	$robotsonline = $row_count;
	unset($sqlcrawlers);
	if (isset($sheel->show['geoip']) AND $sheel->show['geoip'])
	{
		geoip_close($geoip);
	}
	$pprint_array = array();
	$vars = array(
		'sidenav' => $sidenav,
		'guestsonline' => $guestsonline,
		'membersonline' => $membersonline,
		'staffonline' => $membersonline,
		'robotsonline' => $robotsonline
	);
	$loops = array(
		'guest_connection_results' => $guest_connection_results,
		'member_connection_results' => $member_connection_results,
		'admin_connection_results' => $admin_connection_results,
		'crawler_connection_results' => $crawler_connection_results
	);

	$sheel->template->fetch('main', 'sessions.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->parse_loop('main', $loops);
	$sheel->template->pprint('main', $vars);
	exit();
}
else
{
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>
