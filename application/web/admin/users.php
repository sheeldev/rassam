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

if (($sidenav = $sheel->cache->fetch("sidenav_users")) === false)
{
	$sidenav = $sheel->admincp_nav->print('users');
	$sheel->cache->store("sidenav_users", $sidenav);
}
if (!empty($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] > 0 AND $_SESSION['sheeldata']['user']['isadmin'] == '1')
{
	if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'bulkmailer')
	{
		$sheel->template->meta['jsinclude']['footer'][] = 'admin_bulkemail';
		$areanav = 'users_bulkmailer';
		$currentarea = 'Bulk Mailer';
		$plans = $sheel->subscription->admincp_plans_radios('form_who', 1);
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
	}
	else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'bulkmailer/export')
	{

		switch ($sheel->GPC['form']['method'])
		{
			case 'newline':
			{
				$sql = $sheel->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					WHERE email != ''
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0)
				{
					$txt = '';
					while ($emails = $sheel->db->fetch_array($sql))
					{
						$txt .= trim($emails['email']) . LINEBREAK;
					}
				}
				$ext = '.txt';
				$mime = 'text/plain';
				break;
			}
			case 'csv':
			{
				$sql = $sheel->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					WHERE email != ''
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0)
				{
					$txt = '';
					while ($emails = $sheel->db->fetch_array($sql))
					{
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
	}
	else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'audit')
	{
		$areanav = 'users_audit';
		$currentarea = 'Audit';
		$vars = array(
			'sidenav' => $sidenav,
			'prevnext' => (isset($prevnext) ? $prevnext : ''),
			'areanav' => $areanav,
			'currentarea' => $currentarea,
			'id' => (isset($sheel->GPC['id']) ? intval($sheel->GPC['id']) : ''),
		);
		$loops = array(
			'questions' => (isset($questions) ? $questions : array()),
			'languages' => (isset($languages) ? $languages : array())
		);



		$sheel->template->fetch('main', 'users_audit.html', 1);
		$sheel->template->parse_loop('main', $loops);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => (isset($form) ? $form : '')));
		$sheel->template->pprint('main', $vars);
		exit();
	}
	else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'violations')
	{
		$areanav = 'users_violations';
		$currentarea = 'Violation Reports';
		if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'resolveviolations')
		{ // resolve violations
			if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
			{
				$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
				$response = array();
				$response = $sheel->admincp_violations->resolve_violation($ids);
				$sheel->template->templateregistry['success'] = $response['success'];
				$sheel->template->templateregistry['errors'] = $response['errors'];
				set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
				die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])));
			}
			else
			{
				$sheel->template->templateregistry['message'] = '{_no_violations_were_selected_for_removal_please_try_again}';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'unresolveviolations')
		{ // unresolve violations
			if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
			{
				$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
				$response = array();
				$response = $sheel->admincp_violations->unresolve_violation($ids);
				$sheel->template->templateregistry['success'] = $response['success'];
				$sheel->template->templateregistry['errors'] = $response['errors'];
				set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
				die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])));
			}
			else
			{
				$sheel->template->templateregistry['message'] = '{_no_violations_were_selected_for_removal_please_try_again}';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'deleteviolations')
		{ // delete violations
			if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
			{
				$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
				$response = array();
				$response = $sheel->admincp_violations->remove_violation($ids);
				$sheel->template->templateregistry['success'] = $response['success'];
				$sheel->template->templateregistry['errors'] = $response['errors'];
				set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
				die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])));
			}
			else
			{
				$sheel->template->templateregistry['message'] = '{_no_violations_were_selected_for_removal_please_try_again}';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		$violations = array();
		$row_count = 0;
		$sheel->GPC['q'] = (isset($sheel->GPC['q']) ? trim($sheel->GPC['q']) : '');
		$sheel->GPC['filter'] = (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : '');
		$sheel->GPC['page'] = (!isset($sheel->GPC['page']) OR isset($sheel->GPC['page']) AND $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
		$where = "WHERE a.username != ''";
		$extrasql = '';
		if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'pending')
		{
			$where = "WHERE a.status = '1'";
		}
		else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'resolved')
		{
			$where = "WHERE a.status = '0'";
		}
		if (isset($sheel->GPC['filter']) AND isset($sheel->GPC['q']) AND $sheel->GPC['q'] != '')
		{
			switch ($sheel->GPC['filter'])
			{
				case 'reporter': // Full name / Username
				{
					$sheel->GPC['q1'] = $sheel->GPC['q2'] = '';
					if (strrchr($sheel->GPC['q'], ' '))
					{
						$tmp = explode(' ', trim($sheel->GPC['q']));
						$sheel->GPC['q1'] = trim($tmp[0]);
						$sheel->GPC['q2'] = trim($tmp[1]);
						$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q1']) . "%' AND u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q2']) . "%'))";
					}
					else
					{
						$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')";
					}
					break;
				}
				case 'reportid': // city, state or country or zipcode
				{
					$extrasql = "AND a.abuseid = '" . intval($sheel->GPC['q']) . "'";
					break;
				}
				case 'date':
				{
					$extrasql = "AND a.dateadded LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%'";
					break;
				}
			}
		}
		$sql = $sheel->db->query("
			SELECT u.user_id, u.first_name, u.last_name, u.email, a.abuseid, a.regarding, a.username, a.itemid, a.status, a.dateadded, a.type, a.abusetype, a.pageurl
			FROM " . DB_PREFIX . "abuse_reports a
			LEFT JOIN " . DB_PREFIX . "users u ON (a.username = u.username)
			$where
			$extrasql
			ORDER BY dateadded DESC
			LIMIT " . (($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay']) . "," . $sheel->config['globalfilters_maxrowsdisplay']
		);
		$sql2 = $sheel->db->query("
			SELECT u.user_id
			FROM " . DB_PREFIX . "abuse_reports a
			LEFT JOIN " . DB_PREFIX . "users u ON (a.username = u.username)
			$where
			$extrasql
			ORDER BY dateadded DESC
		");
		$number = (int)$sheel->db->num_rows($sql2);
		if ($number > 0)
		{
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$res['fullregarding'] = trim(stripslashes(strip_tags($res['regarding'])));
				$res['regarding'] = $sheel->short_string($res['regarding'], 75, ' .....');
				$res['url'] = urldecode($res['pageurl']);
				if ($res['abusetype'] == 'listing')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">' . o($sheel->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res['itemid'] . "'", "project_title")) . '</a>';
				}
				else if ($res['abusetype'] == 'bid')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">' . o($sheel->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res['itemid'] . "'", "project_title")) . '</a>';
				}
				else if ($res['abusetype'] == 'bpublic')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">{_client_info}</a>';
				}
				else if ($res['abusetype'] == 'profile')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">' . $res['itemid'] . '</a>';
				}
				else if ($res['abusetype'] == 'feedback')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">' . o($sheel->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res['itemid'] . "'", "project_title")) . '</a>';
				}
				else if ($res['abusetype'] == 'pmb')
				{
					$res['ref'] = '<a href="' . urldecode($res['pageurl']) . '" target="_blank">xxxx</a>';
				}
				else if ($res['abusetype'] == 'payment')
				{
					$res['ref'] = '<a href="' . HTTPS_SERVER_ADMIN . 'orders/view/' . $res['itemid'] . '/">#' . $res['itemid'] . '</a>';
					$res['url'] = HTTPS_SERVER_ADMIN . 'orders/view/' . $res['itemid'] . '/';
				}
				$res['type'] = '{_' . $res['abusetype'] . '}';
				$res['date'] = $sheel->common->print_date($res['dateadded'], 'M j Y @ g:ia', 0, 0);
				$res['status'] = (($res['status'] == '1') ? '<span class="badge badge--attention">{_pending}</span>' : '<span class="badge badge--info">{_resolved}</span>');
				$res['recipient'] = '&ndash;';
				$violations[] = $res;
				$row_count++;
			}
		}
		$pageurl = PAGEURL;
		$prevnext = $sheel->admincp->pagination($number, $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl);
		$filter_options = array(
			'' => 'Select filter &ndash;',
			'reportid' => '{_report_id}',
			'reporter' => '{_reporter}',
			'date' => '{_date} (YYYY-MM-DD)'
		);
		$form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
		$form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
		unset($filter_options);
		$vars = array(
			'sidenav' => $sidenav,
			'prevnext' => (isset($prevnext) ? $prevnext : ''),
			'areanav' => $areanav,
			'currentarea' => $currentarea,
			'id' => (isset($sheel->GPC['id']) ? intval($sheel->GPC['id']) : ''),
		);
		$loops = array(
			'violations' => (isset($violations) ? $violations : array())
		);



		$sheel->template->fetch('main', 'users_violations.html', 1);
		$sheel->template->parse_loop('main', $loops);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => (isset($form) ? $form : '')));
		$sheel->template->pprint('main', $vars);
		exit();
	}
	else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'questions')
	{
		$form = array();
		if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'add')
		{
			if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
			{ // submit new question
				$sheel->admincp->insert_registration_question($sheel->GPC);
				refresh(HTTPS_SERVER_ADMIN . 'users/questions/');
				exit();
			}
			$languages = array();
			$sql = $sheel->db->query("
				SELECT languagecode, title, textdirection
				FROM " . DB_PREFIX . "language
			");
			while ($language = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$language['slng'] = $languagecode = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['languagecode'] = $languagecode;
				$language['rslng'] = strtoupper(substr($language['languagecode'], 0, 2));
				$language['language'] = stripslashes($language['title']);
				$language['question'] = '';
				$language['description'] = '';
				$form['flag_' . $languagecode] = $language['rslng'];
				$form['textdirection_' . $languagecode] = $language['textdirection'];
				$languages[] = $language;
			}
			$form['formname'] = $sheel->admincp->construct_form_name(14);
			$form['register_page_pulldown'] = '<select name="form[pageid]" class="draw-select"><option value="1">{_page_1_member_details}</option><option value="2">{_page_2_personal_details}</option><option value="3">{_page_3_subscription_details}</option></select>';
			$form['role_option'] = '';
			$sql = $sheel->db->query("
				SELECT r.roleid, r.purpose_" . $_SESSION['sheeldata']['user']['slng'] . " AS purpose, r.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, r.custom, r.roletype, r.roleusertype, r.active
		                FROM " . DB_PREFIX . "subscription_roles r
		                LEFT JOIN " . DB_PREFIX . "subscription s ON (s.roleid = r.roleid)
		                WHERE r.active = '1'
					AND r.roletype = 'product'
		                	AND s.active = 'yes'
		                	AND s.visible_registration = '1'
		                GROUP BY r.roleid ASC
			");
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$form['role_option'] .= '<input type="checkbox" id="' . $res['roleid'] . '" name="form[roleid][]" value="' . $res['roleid'] . '" checked="checked" /> ' . o($res['title']) . ' - ' . o($res['purpose']) . '<br />';
			}
			$form['regformdefault'] = '';
			$form['multiplechoice'] = '';
			$form['cbvisible'] = '';
			$form['cbrequired'] = '';
			$form['cbprofile'] = '';
			$form['cbguests'] = '';
			$form['regsort'] = '100';
			$form['regprofile_inputtype_pulldown'] = '<select name="form[inputtype]" class="draw-select"><option value="yesno">{_radio_selection_box_yes_or_no_type_question}</option><option value="int">{_integer_field_numbers_only}</option><option value="textarea">{_textarea_field_multiline}</option><option value="text">{_input_text_field_singleline}</option><option value="multiplechoice">{_multiple_choice_enter_values_below}</option><option value="pulldown">{_pulldown_menu_enter_values_below}</option></select>';
		}
		else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'update' AND isset($sheel->GPC['id']) AND $sheel->GPC['id'] > 0)
		{
			if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
			{ // submit new question
				$sheel->admincp->update_registration_question($sheel->GPC);
				refresh(HTTPS_SERVER_ADMIN . 'users/questions/');
				exit();
			}
			$languages = array();
			$sql = $sheel->db->query("
				SELECT languagecode, title, textdirection
				FROM " . DB_PREFIX . "language
			");
			while ($language = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$language['slng'] = $languagecode = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = stripslashes($language['title']);
				$language['languagecode'] = $languagecode;
				$language['rslng'] = strtoupper(substr($language['languagecode'], 0, 2));
				$language['language'] = stripslashes($language['title']);
				$sql2 = $sheel->db->query("
					SELECT question_$language[slng] AS question, description_$language[slng] AS description
					FROM " . DB_PREFIX . "register_questions
					WHERE questionid = '" . intval($sheel->GPC['id']) . "'
				");
				if ($sheel->db->num_rows($sql2) > 0)
				{
					while ($res = $sheel->db->fetch_array($sql2, DB_ASSOC))
					{
						$form['flag_' . $languagecode] = $language['rslng'];
						$form['textdirection_' . $languagecode] = $language['textdirection'];
						$language['question'] = o($res['question']);
						$language['description'] = o($res['description']);
					}
				}
				$languages[] = $language;
			}
			$sql = $sheel->db->query("
				SELECT *
				FROM " . DB_PREFIX . "register_questions
				WHERE questionid = '" . intval($sheel->GPC['id']) . "'
				ORDER BY sort ASC
			");
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			$form['formname'] = $res['formname'];
			$form['register_page_pulldown'] = '<select name="form[pageid]" class="draw-select"><option value="1"' . (($res['pageid'] == 1) ? ' selected="selected"' : '') . '>{_page_1_member_details}</option><option value="2"' . (($res['pageid'] == 2) ? ' selected="selected"' : '') . '>{_page_2_personal_details}</option><option value="3"' . (($res['pageid'] == 3) ? ' selected="selected"' : '') . '>{_page_3_subscription_details}</option></select>';
			$form['role_option'] = '';
			$sql = $sheel->db->query("
				SELECT r.roleid, r.purpose_" . $_SESSION['sheeldata']['user']['slng'] . " AS purpose, r.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, r.custom, r.roletype, r.roleusertype, r.active
				FROM " . DB_PREFIX . "subscription_roles r
				LEFT JOIN " . DB_PREFIX . "subscription s ON (s.roleid = r.roleid)
				WHERE r.active = '1'
					AND r.roletype = 'product'
					AND s.active = 'yes'
					AND s.visible_registration = '1'
				GROUP BY r.roleid ASC
			");
			while ($resx = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$checked = '';
				$roles = explode('|', $res['roleid']);
				if (is_array($roles))
				{
					foreach ($roles AS $key => $value)
					{
						if ($value == $resx['roleid'])
						{
							$checked = 'checked="checked"';
						}
					}
				}
				$form['role_option'] .= '<input type="checkbox" id="' . $resx['roleid'] . '" name="form[roleid][]" value="' . $resx['roleid'] . '" ' . $checked . ' /> ' . o($resx['title']) . ' - ' . o($resx['purpose']) . '<br />';
			}
			$form['regformdefault'] = o($res['formdefault']);
			$form['multiplechoice'] = o($res['multiplechoice']);
			$form['regsort'] = intval($res['sort']);
			$form['cbvisible'] = (($res['visible'] > 0) ? ' checked="checked"' : '');
			$form['cbrequired'] = (($res['required'] > 0) ? ' checked="checked"' : '');
			$form['cbprofile'] = (($res['profile'] > 0) ? ' checked="checked"' : '');
			$form['cbguests'] = (($res['guests'] > 0) ? ' checked="checked"' : '');
			$form['regprofile_inputtype_pulldown'] = '<select name="form[inputtype]" class="draw-select">
			<option value="yesno"' . (($res['inputtype'] == 'yesno') ? ' selected="selected"' : '') . '>{_radio_selection_box_yes_or_no_type_question}</option>
			<option value="int"' . (($res['inputtype'] == 'int') ? ' selected="selected"' : '') . '>{_integer_field_numbers_only}</option>
			<option value="textarea"' . (($res['inputtype'] == 'textarea') ? ' selected="selected"' : '') . '>{_textarea_field_multiline}</option>
			<option value="text"' . (($res['inputtype'] == 'text') ? ' selected="selected"' : '') . '>{_input_text_field_singleline}</option>
			<option value="multiplechoice"' . (($res['inputtype'] == 'multiplechoice') ? ' selected="selected"' : '') . '>{_multiple_choice_enter_values_below}</option>
			<option value="pulldown"' . (($res['inputtype'] == 'pulldown') ? ' selected="selected"' : '') . '>{_pulldown_menu_enter_values_below}</option>
			</select>';
		}
		else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'delete')
		{
			if (isset($sheel->GPC['xid']) AND $sheel->GPC['xid'] > 0)
			{
				$sheel->admincp->remove_registration_question(intval($sheel->GPC['xid']));
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Custom profile question deleted', 'A custom profile question has been successfully deleted.');
				die(json_encode(array('response' => '1', 'message' => 'A custom profile question has been successfully deleted.')));
			}
			else
			{
				$sheel->template->templateregistry['message'] = 'This profile question could not be deleted.';
				die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
			}
		}
		$sheel->show['no_register_questions'] = true;
		$questions = array();
		$sql = $sheel->db->query("
			SELECT *
			FROM " . DB_PREFIX . "register_questions
			ORDER BY sort ASC
		");
		$sql2 = $sheel->db->query("
			SELECT *
			FROM " . DB_PREFIX . "register_questions
			ORDER BY sort ASC
		");
		if ($sheel->db->num_rows($sql) > 0)
		{
			$sheel->show['no_register_questions'] = false;
			while ($rows = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$rows['question'] = stripslashes($rows['question_' . $_SESSION['sheeldata']['user']['slng']]);
				$rows['active'] = ($rows['visible']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />';
				$rows['required'] = ($rows['required']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />';
				$rows['inputtype'] = mb_strtolower($rows['inputtype']);
				$rows['sortinput'] = '<input type="text" name="sort[' . $rows['questionid'] . ']" value="' . $rows['sort'] . '" class="draw-input" size="3" style="text-align:center" />';
				$rows['visible'] = ($rows['profile']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />';
				$rows['guests'] = ($rows['guests']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />';
				$rows['actions'] = '<ul class="segmented"><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'delete\', \'Delete this profile question?\', \'Are you sure you want to delete this profile question? All associated user answers will also be deleted.\', \'' . $rows['questionid'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
				$questions[] = $rows;
			}
		}
		$areanav = 'users_questions';
		$currentarea = '{_profile_questions}';
		$vars = array(
			'sidenav' => $sidenav,
			'prevnext' => (isset($prevnext) ? $prevnext : ''),
			'areanav' => $areanav,
			'currentarea' => $currentarea,
			'id' => (isset($sheel->GPC['id']) ? intval($sheel->GPC['id']) : ''),
		);
		$loops = array(
			'questions' => (isset($questions) ? $questions : array()),
			'languages' => (isset($languages) ? $languages : array())
		);


		$sheel->template->fetch('main', 'users_questions.html', 1);
		$sheel->template->parse_loop('main', $loops);
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => (isset($form) ? $form : '')));
		$sheel->template->pprint('main', $vars);
		exit();
	}
	else
	{
		if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'switch' AND isset($sheel->GPC['userid']) AND $sheel->GPC['userid'] > 0)
		{
			$sql = $sheel->db->query("
				SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
				LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				LEFT JOIN " . DB_PREFIX . "stores st ON su.user_id = st.user_id
				WHERE u.user_id = '" . intval($sheel->GPC['userid']) . "'
					AND sp.type = 'product'
				GROUP BY username
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0)
			{
				$userinfo = $sheel->db->fetch_array($sql, DB_ASSOC);
				$userinfo['shipprofileid'] = $sheel->shipping->fetch_default_ship_profileid($userinfo['user_id']);
				$userinfo['billprofileid'] = $sheel->shipping->fetch_default_bill_profileid($userinfo['user_id']);
				$sheel->sessions->build_user_session($userinfo);
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Users account switched into', 'A staff person has successfully switched into a users account successfully.');
				refresh(HTTPS_SERVER . '?note=sw:ac');
				exit();
			}
		}
		else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'add')
		{
			$form = array();
			$sheel->template->meta['areatitle'] = 'Admin CP | Users - Add';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Add';
			$sheel->template->meta['jsinclude']['header'][] = 'admin_users';
			if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'save')
			{
				if (!isset($sheel->GPC['form']['password']) OR !isset($sheel->GPC['form']['password2']) OR empty($sheel->GPC['form']['password']) OR empty($sheel->GPC['form']['password2']) OR $sheel->GPC['form']['password'] != $sheel->GPC['form']['password2'])
				{
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
				if ($sheel->db->num_rows($sqlusercheck) > 0)
				{ // woops! change username for new user automatically.
					$sheel->GPC['form']['username'] = $sheel->GPC['form']['username'] . ' ' . rand(1000, 999999); // Peter 39918
				}
				$sql = $sheel->db->query("
					SELECT locationid
					FROM " . DB_PREFIX . "locations
					WHERE location_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $sheel->db->escape_string($sheel->GPC['country']) . "'
					LIMIT 1
				");
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				if ($sheel->GPC['form']['customerid']=='-1')
				{
					$sheel->GPC['form']['customerid'] = '0';
				}
				$salt = $sheel->construct_password_salt(5);
				$pass = md5(md5($sheel->GPC['form']['password']) . $salt);
				$sheel->GPC['form']['isadmin'] = $sheel->role->is_role_admin($sheel->GPC['form']['roleid']);
				$sheel->GPC['form']['languageid'] = isset($sheel->GPC['form']['languageid']) ? $sheel->GPC['form']['languageid'] : $sheel->language->fetch_default_languageid();
				$sheel->GPC['form']['useapi'] = ((isset($sheel->GPC['form']['useapi']) AND $sheel->GPC['form']['useapi']) ? 1 : 0);
				$sheel->GPC['form']['emailnotify'] = ((isset($sheel->GPC['form']['emailnotify']) AND $sheel->GPC['form']['emailnotify']) ? 1 : 0);
				$sheel->GPC['form']['dob'] = ((isset($sheel->GPC['form']['dob']) AND !empty($sheel->GPC['form']['dob'])) ? $sheel->GPC['form']['dob'] : '0000-00-00');
				$sheel->GPC['form']['gender'] = ((isset($sheel->GPC['form']['gender']) AND !empty($sheel->GPC['form']['gender'])) ? $sheel->GPC['form']['gender'] : '');

				

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
				if ($newuserid > 0)
				{
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

				if (isset($sheel->GPC['form']['notifyregister']) AND $sheel->GPC['form']['notifyregister'])
				{
					$sheel->email->mail = $sheel->GPC['form']['email'];
					$sheel->email->slng = $sheel->language->fetch_user_slng($newuserid);
					$sheel->email->get('register_welcome_email_admincp');
					$sheel->email->set(array(
						'{{username}}' => $sheel->GPC['form']['username'],
						'{{user_id}}' => $newuserid,
						'{{first_name}}' => $sheel->GPC['form']['firstname'],
						'{{last_name}}' => $sheel->GPC['form']['lastname'],
						'{{phone}}' => $sheel->GPC['form']['phone']
					));
					$sheel->email->send();
				}
				if (isset($sheel->GPC['form']['notifywelcome']) AND $sheel->GPC['form']['notifywelcome'])
				{
					$sheel->email->mail = SITE_CONTACT;
					$sheel->email->slng = $sheel->language->fetch_site_slng();
					$sheel->email->get('register_welcome_email_admin_admincp');
					$sheel->email->set(array(
						'{{username}}' => $sheel->GPC['form']['username'],
						'{{user_id}}' => $newuserid,
						'{{first_name}}' => $sheel->GPC['form']['firstname'],
						'{{last_name}}' => $sheel->GPC['form']['lastname'],
						'{{phone}}' => $sheel->GPC['form']['phone'],
						'{{emailaddress}}' => $sheel->GPC['form']['email'],
					));
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
			$form['companyname'] = '';
			$form['phone'] = '';
			$form['address'] = '';
			$form['address2'] = '';
			$form['zip_code'] = '';
			$form['ipaddress'] = '';
			$form['apikey'] = md5($sheel->construct_password(32));
			$userstatuses = array('active' => '{_active_can_signin}', 'suspended' => '{_suspended_cannot_signin}', 'unverified' => '{_unverified_email_cannot_signin}', 'banned' => '{_banned_cannot_signin}', 'moderated' => '{_moderated_cannot_signin}');
			$form['userstatus'] = $sheel->construct_pulldown('status', 'form[status]', $userstatuses, '', 'class="draw-select"');
			$form['role_pulldown'] = $sheel->role->print_role_pulldown('', 1, 0, '', '', 'draw-select', 'form_roleid', 'form[roleid]', false);
			$form['customer_pulldown'] = $sheel->admincp_customers->print_customer_pulldown('', 1,'', '', 'draw-select', 'form_customerid', 'form[customerid]', false);
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
            
		}
		else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'update')
		{
			$sheel->template->meta['jsinclude']['header'][] = 'admin_users';
			$form['userid'] = (isset($sheel->GPC['userid']) ? $sheel->GPC['userid'] : '0');
			if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'transactions')
			{
				$sheel->template->meta['areatitle'] = 'Admin CP | Users - Transactions';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Transactions';
				$sql = $sheel->db->query("
					SELECT first_name, last_name, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) == 0)
				{
					$sheel->admincp->print_action_failed('{_the_user_account_no_longer_exists}', HTTPS_SERVER_ADMIN . 'users/');
					exit();
				}
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$form = $res;
				$form['userid'] = (isset($sheel->GPC['userid']) ? $sheel->GPC['userid'] : '0');
				unset($res);

				$transactions = array();
				$sql = $sheel->db->query("
					SELECT id, datetime, userid, description, staffnotes, custom, technical, invoiceid, credit, debit, sumcredit, sumdebit, sumcredit - sumdebit AS balance
					FROM (SELECT id, datetime, userid, description, staffnotes, custom, technical, invoiceid, credit, debit, credit + COALESCE((
						SELECT SUM(credit)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumcredit, debit + COALESCE((
						SELECT SUM(debit)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumdebit
					FROM " . DB_PREFIX . "transactions AS a
					) AS b
					WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						AND (credit > 0 OR debit > 0)
					ORDER BY id DESC
				");
				if ($sheel->db->num_rows($sql) > 0)
				{
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$row['datepaid'] = $sheel->common->print_date($row['datetime'], 'd-M-Y', false, false);
						$row['description'] = o($row['description']);
						$row['custom'] = o($row['custom']);
						$row['staffnotes'] = ((!empty($row['staffnotes'])) ? '<div class="type--subdued st">' . o($row['staffnotes']) . '</div>' : '');
						$row['technical'] = $sheel->shorten(o($row['technical']), 150);
						$row['debit'] = (($row['debit'] > 0) ? $row['debit'] : '');
						$row['credit'] = (($row['credit'] > 0) ? $row['credit'] : '');
						$transactions[] = $row;
					}
				}
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'transactions/points')
			{
				$sheel->template->meta['areatitle'] = 'Admin CP | Users - Points Transactions';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Points Transactions';
				$sql = $sheel->db->query("
					SELECT first_name, last_name, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) == 0)
				{
					$sheel->admincp->print_action_failed('{_the_user_account_no_longer_exists}', HTTPS_SERVER_ADMIN . 'users/');
					exit();
				}
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$form = $res;
				$form['userid'] = (isset($sheel->GPC['userid']) ? $sheel->GPC['userid'] : '0');
				unset($res);

				$pointstransactions = array();
				$sql = $sheel->db->query("
					SELECT id, datetime, userid, description, staffnotes, custom, technical, invoiceid, creditpoints, debitpoints, sumcreditpoints, sumdebitpoints, sumcreditpoints - sumdebitpoints AS balance
					FROM (SELECT id, datetime, userid, description, staffnotes, custom, technical, invoiceid, creditpoints, debitpoints, creditpoints + COALESCE((
						SELECT SUM(creditpoints)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumcreditpoints, debitpoints + COALESCE((
						SELECT SUM(debitpoints)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumdebitpoints
					FROM " . DB_PREFIX . "transactions AS a
					) AS b
					WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						AND (creditpoints > 0 OR debitpoints > 0)
					ORDER BY id DESC
				");
				if ($sheel->db->num_rows($sql) > 0)
				{
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$row['datepaid'] = $sheel->common->print_date($row['datetime'], 'd-M-Y', false, false);
						$row['description'] = o($row['description']);
						$row['custom'] = o($row['custom']);
						$row['staffnotes'] = ((!empty($row['staffnotes'])) ? '<div class="type--subdued st">' . o($row['staffnotes']) . '</div>' : '');
						$row['technical'] = $sheel->shorten(o($row['technical']), 150);
						$row['debit'] = (($row['debitpoints'] > 0) ? $row['debitpoints'] : '');
						$row['credit'] = (($row['creditpoints'] > 0) ? $row['creditpoints'] : '');
						$pointstransactions[] = $row;
					}
				}
			}
			else
			{
				$sheel->template->meta['areatitle'] = 'Admin CP | Users - Update';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Users - Update';
				if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'save')
				{
					$country = $sheel->db->escape_string($sheel->GPC['country']);
					$sheel->GPC['form']['locationid'] = intval($sheel->common_location->fetch_country_id($country));


					$ipres = ((isset($sheel->GPC['form']['iprestrict']) AND $sheel->GPC['form']['iprestrict']) ? '1' : '0');
					$passwordsql = '';
					if (!empty($sheel->GPC['form']['password']))
					{
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
					$posthtml = ((isset($sheel->GPC['form']['posthtml'])) ? intval($sheel->GPC['form']['posthtml']) : '0');
					$useapi = ((isset($sheel->GPC['form']['useapi'])) ? intval($sheel->GPC['form']['useapi']) : '0');
					$emailnotify = ((isset($sheel->GPC['form']['emailnotify'])) ? intval($sheel->GPC['form']['emailnotify']) : '0');
					// detect if admin is changing status from 'moderated' to 'active'
					$oldstatus = $sheel->fetch_user('status', intval($sheel->GPC['userid']));
					$username_history = $sheel->fetch_user('username_history', intval($sheel->GPC['userid']));
					if ($oldstatus == 'moderated' AND $status == 'active')
					{
						$activatedusers = $sheel->admincp_users->activate_user(array($sheel->GPC['userid']));
						$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Users account status verified', 'The users account status was successfully verified.');
					}
					$sheel->show['error_username'] = $sheel->show['error_username_exists'] = $sheel->show['error_new_email_exists'] = false;
					if (isset($sheel->GPC['form']['email']) AND isset($sheel->GPC['form']['oldemail']) AND $sheel->GPC['form']['email'] != $sheel->GPC['form']['oldemail'])
					{ // changing to a new email..
						$sqlemailcheck = $sheel->db->query("
							SELECT user_id
							FROM " . DB_PREFIX . "users
							WHERE email IN ('" . $sheel->db->escape_string(htmlspecialchars_uni($sheel->GPC['form']['email'])) . "')
								AND user_id != '" . intval($sheel->GPC['userid']) . "'
						");
						if ($sheel->db->num_rows($sqlemailcheck) > 0)
						{
							$sheel->show['error_new_email_exists'] = true;
						}
					}
					if (isset($sheel->GPC['form']['username']) AND $sheel->GPC['form']['username'] != '')
					{ // quick username checkup
						if ($sheel->common->is_username_banned($sheel->GPC['form']['username']))
						{ // username ban checkup
							$sheel->show['error_username'] = true;
						}
						else
						{ // the username isn't banned
							$sqlusercheck = $sheel->db->query("
								SELECT user_id
								FROM " . DB_PREFIX . "users
								WHERE username IN ('" . $sheel->db->escape_string(htmlspecialchars_uni($sheel->GPC['form']['username'])) . "')
									AND user_id != '" . intval($sheel->GPC['userid']) . "'
							");
							if ($sheel->db->num_rows($sqlusercheck) > 0)
							{
								$sheel->show['error_username_exists'] = true;
							}
							else
							{ // does not exist- we're good to go
								if ($sheel->GPC['form']['oldusername'] != $sheel->GPC['form']['username'])
								{ // admin is changing users username
									if (!empty($username_history) AND $sheel->is_serialized($username_history))
									{
										$username_history = unserialize($username_history);
										$username_history[] = array(
											'username' => $sheel->GPC['form']['oldusername'],
											'datetime' => DATETIME24H
										);
										$username_history = serialize($username_history);
									}
									else
									{
										$username_history = array(array(
											'username' => $sheel->GPC['form']['oldusername'],
											'datetime' => DATETIME24H)
										);
										$username_history = serialize($username_history);
									}
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "abuse_reports
										SET username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "feedback
										SET for_username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE for_username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "feedback
										SET from_username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE from_username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "feedback_response
										SET for_username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE for_username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "feedback_response
										SET from_username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE from_username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");
									$sheel->db->query("
										UPDATE " . DB_PREFIX . "messages
										SET username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "'
										WHERE username = '" . $sheel->db->escape_string($sheel->GPC['form']['oldusername']) . "'
									");



								}
							}
						}
					}
					else
					{
						$sheel->show['error_username'] = true;
					}
					if (isset($sheel->GPC['form']['oldsubscriptionid']) AND isset($sheel->GPC['form']['subscriptionid']) AND $sheel->GPC['form']['oldsubscriptionid'] != $sheel->GPC['form']['subscriptionid'])
					{ // assigning new membership
						$sheel->subscription->subscription_upgrade_process_admincp(intval($sheel->GPC['userid']), intval($sheel->GPC['form']['subscriptionid']), $sheel->GPC['form']['txndescription'], $sheel->GPC['form']['planaction']);
						$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer membership updated', 'The users membership plan was successfully updated.');
					}
					if ($sheel->GPC['form']['oldroleid'] != $sheel->GPC['form']['roleid'])
					{ // assigning new membership role
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET roleid = '" . intval($sheel->GPC['form']['roleid']) . "'
							WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
							LIMIT 1
						");
						$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer membership role updated', 'The users membership role was successfully updated.');
					}
					if ($sheel->show['error_username'])
					{
						$sheel->admincp->print_action_failed('{_sorry_the_username_you_entered_appears_to_be_in_the_username_ban_list}', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
						exit();
					}
					if ($sheel->show['error_username_exists'])
					{
						$sheel->admincp->print_action_failed('This username appears to already exist.  Please enter a different username.', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
						exit();
					}
					if ($sheel->show['error_new_email_exists'])
					{
						$sheel->admincp->print_action_failed('This email address appears to already exist.  Please enter a different email address.', HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
						exit();
					}
					$gendersql = '';
					if (isset($sheel->GPC['form']['gender']) AND !empty($sheel->GPC['form']['gender']))
					{
						$gendersql = "gender = '" . $sheel->db->escape_string($sheel->GPC['form']['gender']) . "',";
					}
					$secretanswersql = $secretquestionsql = '';
					if (!empty($sheel->GPC['form']['secretanswer']) AND !empty($sheel->GPC['form']['secretquestion']))
					{
						$secretanswersql = "secretanswer = '" . $sheel->db->escape_string(md5($sheel->GPC['form']['secretanswer'])) . "',";
						$secretquestionsql = "secretquestion = '" . intval($sheel->GPC['form']['secretquestion']) . "',";
					}
					$sheel->GPC['form']['languageid'] = ((isset($sheel->GPC['form']['languageid'])) ? $sheel->GPC['form']['languageid'] : $sheel->language->fetch_default_languageid());
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET username = '" . $sheel->db->escape_string($sheel->GPC['form']['username']) . "',
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
						companyname = '" . $sheel->db->escape_string($sheel->GPC['form']['company']) . "',
						phone = '" . $sheel->db->escape_string($sheel->GPC['form']['phone']) . "',
						country = '" . intval($sheel->GPC['form']['locationid']) . "',
						ipaddress = '" . $sheel->db->escape_string($sheel->GPC['form']['ipaddress']) . "',
						iprestrict = '" . $sheel->db->escape_string($ipres) . "',
						status = '" . $sheel->db->escape_string($status) . "',
						dob = '" . $sheel->db->escape_string($dob) . "',
						$secretquestionsql
						$secretanswersql
						$gendersql
						isadmin = '" . intval($isadmin) . "',
						posthtml = '" . intval($posthtml) . "',
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



					if (!empty($sheel->GPC['custom1']) AND is_array($sheel->GPC['custom1']))
					{ // registration question answers
						$sheel->registration->process_custom_register_questions($sheel->GPC['custom1'], intval($sheel->GPC['userid']));
					}
					if (isset($sheel->GPC['form']['emailuser']) AND $sheel->GPC['form']['emailuser'])
					{ // staff emailing user with new random or entered password
						if (empty($sheel->GPC['form']['password']))
						{ // staff sending randomly generated new password to user
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
						$sheel->email->set(array(
							'{{username}}' => $sheel->GPC['form']['username'],
							'{{password}}' => $sheel->GPC['form']['password'],
						));
						$sheel->email->send();
					}
					refresh(HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
					exit();
				}
				else if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'transaction')
				{ // create new debit or credit transaction for user
					$sheel->GPC['form']['transaction_notes'] = isset($sheel->GPC['form']['transaction_notes']) ? $sheel->GPC['form']['transaction_notes'] : '';
					if (isset($sheel->GPC['form']['amount']) AND $sheel->GPC['form']['amount'] != '')
					{
						$sheel->GPC['form']['transaction_description'] = (!empty($sheel->GPC['form']['transaction_description']) ? $sheel->GPC['form']['transaction_description'] : '{_no_description}');
						if ($sheel->GPC['form']['action'] == 'debit')
						{
							$sheel->GPC['form']['amount'] = $sheel->currency->string_to_number($sheel->GPC['form']['amount']);
							$sql = $sheel->db->query("
								SELECT available_balance, total_balance
								FROM " . DB_PREFIX . "users
								WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
								LIMIT 1
							");
							if ($sheel->db->num_rows($sql) > 0)
							{
								$res = $sheel->db->fetch_array($sql, DB_ASSOC);
								$new_debit_amount = sprintf("%01.2f", $sheel->GPC['form']['amount']);
								$total_now = $res['total_balance'];
								$avail_now = $res['available_balance'];
								$new_total_now = ($total_now - $new_debit_amount);
								$new_avail_now = ($avail_now - $new_debit_amount);
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "users
									SET total_balance = '" . $new_total_now . "',
									available_balance = '" . $new_avail_now . "',
									income_reported = income_reported - $new_debit_amount
									WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
									LIMIT 1
								");
								$invoiceid = $sheel->accounting->insert_transaction(array(
									'user_id' => intval($sheel->GPC['userid']),
									'description' => $sheel->GPC['form']['transaction_description'],
									'amount' => sprintf("%01.2f", $new_debit_amount),
									'paid' => sprintf("%01.2f", $new_debit_amount),
									'totalamount' => sprintf("%01.2f", $new_debit_amount),
									'transactionfee' => 0,
									'status' => 'paid',
									'invoicetype' => 'debit',
									'paymethod' => 'account',
									'createdate' => DATETIME24H,
									'duedate' => DATEINVOICEDUE,
									'paiddate' => DATETIME24H,
									'custommessage' => $sheel->GPC['form']['transaction_notes'],
									'returnid' => 1
								));
								$array = array(
									'userid' => $sheel->GPC['userid'],
									'credit' => 0,
									'debit' => $new_debit_amount,
									'description' => $sheel->GPC['form']['transaction_description'],
									'invoiceid' => $invoiceid,
									'transactionid' => '',
									'custom' => '',
									'staffnotes' => $sheel->GPC['form']['transaction_notes'],
									'technical' => 'Account balance debit [username=' . $_SESSION['sheeldata']['user']['username'] . '] [do=transaction] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
								);
								$sheel->accounting->account_balance($array);
								unset($array);
								$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer account debited', 'The users account balance was successfully debited ' . $new_debit_amount . '.');
								$sqlemail = $sheel->db->query("
									SELECT email, username, first_name, last_name
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
									LIMIT 1
								");
								if ($sheel->db->num_rows($sqlemail) > 0)
								{
									$resemail = $sheel->db->fetch_array($sqlemail, DB_ASSOC);
									$sheel->email->mail = $resemail['email'];
									$sheel->email->slng = $sheel->language->fetch_user_slng(intval($sheel->GPC['userid']));
									$sheel->email->get('account_debit_notification');
									$sheel->email->set(array(
										'{{user}}' => $resemail['username'],
										'{{amount}}' => $sheel->currency->format($sheel->GPC['form']['amount']),
										'{{staff}}' => $_SESSION['sheeldata']['user']['username'],
										'{{accounttype}}' => '{_account_balance_debit}',
									));
									$sheel->email->send();
									refresh(HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
									exit();
								}
							}
						}
						else if ($sheel->GPC['form']['action'] == 'credit')
						{
							$sheel->GPC['form']['amount'] = $sheel->currency->string_to_number($sheel->GPC['form']['amount']);
							$sql = $sheel->db->query("
								SELECT available_balance, total_balance
								FROM " . DB_PREFIX . "users
								WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
							");
							if ($sheel->db->num_rows($sql) > 0)
							{
								$res = $sheel->db->fetch_array($sql, DB_ASSOC);
								$new_credit_amount = sprintf("%01.2f", $sheel->GPC['form']['amount']);
								$total_now = $res['total_balance'];
								$avail_now = $res['available_balance'];
								$new_total_now = ($total_now + $new_credit_amount);
								$new_avail_now = ($avail_now + $new_credit_amount);
								if (strchr($avail_now, '-'))
								{
									$new_total_now = ($new_credit_amount + -$total_now);
									$new_avail_now = ($new_credit_amount + -$avail_now);
								}
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "users
									SET total_balance = '" . $sheel->db->escape_string($new_total_now) . "',
									available_balance = '" . $sheel->db->escape_string($new_avail_now) . "',
									income_reported = income_reported + $new_credit_amount
									WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
									LIMIT 1
								");
								$invoiceid = $sheel->accounting->insert_transaction(array(
									'user_id' => intval($sheel->GPC['userid']),
									'description' => $sheel->GPC['form']['transaction_description'],
									'amount' => sprintf("%01.2f", $new_credit_amount),
									'paid' => sprintf("%01.2f", $new_credit_amount),
									'totalamount' => sprintf("%01.2f", $new_credit_amount),
									'transactionfee' => 0,
									'status' => 'paid',
									'invoicetype' => 'credit',
									'paymethod' => 'account',
									'createdate' => DATETIME24H,
									'duedate' => DATEINVOICEDUE,
									'paiddate' => DATETIME24H,
									'custommessage' => $sheel->GPC['form']['transaction_notes'],
									'returnid' => 1
								));
								$array = array(
									'userid' => $sheel->GPC['userid'],
									'credit' => $new_credit_amount,
									'debit' => 0,
									'description' => $sheel->GPC['form']['transaction_description'],
									'invoiceid' => $invoiceid,
									'transactionid' => '',
									'custom' => '',
									'staffnotes' => $sheel->GPC['form']['transaction_notes'],
									'technical' => 'Account balance credit [username=' . $_SESSION['sheeldata']['user']['username'] . '] [do=transaction] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
								);
								$sheel->accounting->account_balance($array);
								unset($array);
								$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer account credited', 'The users account balance was successfully credited ' . $new_credit_amount . '.');
								$sqlemail = $sheel->db->query("
									SELECT email, username, first_name, last_name
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
									LIMIT 1
								");
								if ($sheel->db->num_rows($sqlemail) > 0)
								{
									$resemail = $sheel->db->fetch_array($sqlemail, DB_ASSOC);
									$sheel->email->mail = $resemail['email'];
									$sheel->email->slng = $sheel->language->fetch_user_slng(intval($sheel->GPC['userid']));
									$sheel->email->get('account_credit_notification');
									$sheel->email->set(array(
										'{{user}}' => $resemail['username'],
										'{{amount}}' => $sheel->currency->format($sheel->GPC['form']['amount']),
										'{{staff}}' => $_SESSION['sheeldata']['user']['username'],
										'{{accounttype}}' => '{_account_balance_debit}',
									));
									$sheel->email->send();
									refresh(HTTPS_SERVER_ADMIN . 'users/update/' . intval($sheel->GPC['userid']) . '/');
									exit();
								}
							}
						}


					}
				}

				$sql = $sheel->db->query("
					SELECT
					*,
					l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS location, s.storename
					FROM " . DB_PREFIX . "users u
					LEFT JOIN " . DB_PREFIX . "locations l ON (u.country = l.locationid)
					LEFT JOIN " . DB_PREFIX . "stores s ON (u.user_id = s.user_id)
					WHERE u.user_id = '" . intval($sheel->GPC['userid']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) == 0)
				{
					$sheel->admincp->print_action_failed('{_the_user_account_no_longer_exists}', HTTPS_SERVER_ADMIN . 'users/');
					exit();
				}
				$sheel->show['usernamehistory'] = false;
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
				{
					$res['registersource'] = ((!empty($res['registersource'])) ? o($res['registersource']) : '-');
					$res['username'] = o($res['username']);
					$username = $res['username'];
					$usernamehistory = '';
					if (!empty($res['username_history']))
					{
						$res['username_history'] = unserialize($res['username_history']);
						foreach ($res['username_history'] AS $array)
						{
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
					$res['referredby'] = o($sheel->referral->print_referred_by_username(intval($sheel->GPC['userid']), true));
					$sheel->show['referredby'] = ((!empty($res['referredby'])) ? true : false);
					$username = "(" . $res['username'] . ")";
					$userstatuses = array('active' => '{_active_can_signin}', 'suspended' => '{_suspended_cannot_signin}', 'unverified' => '{_unverified_email_cannot_signin}', 'banned' => '{_banned_cannot_signin}', 'moderated' => '{_moderated_cannot_signin}');
					$res['userstatus'] = $sheel->construct_pulldown('status', 'form[status]', $userstatuses, $res['status'], 'class="draw-select"');
					$res['iprestrict'] = (($res['iprestrict']) ? 'checked="checked"' : '');
					$res['isadmin'] = (($res['isadmin']) ? 'checked="checked"' : '');
					$res['posthtml'] = (($res['posthtml']) ? 'checked="checked"' : '');
					$res['useapi'] = (($res['useapi']) ? 'checked="checked"' : '');
					$res['emailnotify'] = (($res['emailnotify']) ? 'checked="checked"' : '');
					$res['lastknownpurchasedate'] = '-';
					$res['lastknownbiddate'] = '-';
					$form = $res;
					$form['userid'] = intval($sheel->GPC['userid']);
					$form['purchases'] = number_format($res['purchases']);
					$form['cancels'] = number_format($res['cancels']);
					$form['returns'] = number_format($res['returns']);
					$spent = '';
					$sqlspend = $sheel->db->query("
						SELECT originalcurrencyid, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE buyer_id = '" . $res['user_id'] . "'
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY originalcurrencyid
					");
					if ($sheel->db->num_rows($sqlspend) > 0)
					{
						while ($respend = $sheel->db->fetch_array($sqlspend, DB_ASSOC))
						{
							$spent .= '<div>' . $sheel->currency->currencies[$respend['originalcurrencyid']]['symbol_left'] . number_format($respend['amount'], $sheel->currency->currencies[$respend['originalcurrencyid']]['decimal_places']) . $sheel->currency->currencies[$respend['originalcurrencyid']]['symbol_right'] . '</div>';
						}
					}
					else
					{
						$spent = '-';
					}
					$form['totalpurchased'] = $spent;
					$form['orderclaimsfiled'] = number_format($res['orderclaimsfiled']);
					$form['nonpaymentviolations'] = number_format($res['nonpaymentviolations']);
					$form['totalbids'] = number_format($res['totalbids']);
					$form['totalproxybids'] = number_format($res['totalproxybids']);
					$form['totalwinningbids'] = number_format($res['totalwinningbids']);
					$form['cart'] = number_format($res['cart']);
					$form['cartsaved'] = number_format($res['cartsaved']);
					$form['cartdelete'] = number_format($res['cartdelete']);
					$form['soldorders'] = number_format($res['orders']);
					$sales = '';
					$sqlsales = $sheel->db->query("
						SELECT originalcurrencyid, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE owner_id = '" . $res['user_id'] . "'
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY originalcurrencyid
					");
					if ($sheel->db->num_rows($sqlsales) > 0)
					{
						while ($resales = $sheel->db->fetch_array($sqlsales, DB_ASSOC))
						{
							$sales .= '<div>' . $sheel->currency->currencies[$resales['originalcurrencyid']]['symbol_left'] . number_format($resales['amount'], $sheel->currency->currencies[$resales['originalcurrencyid']]['decimal_places']) . $sheel->currency->currencies[$resales['originalcurrencyid']]['symbol_right'] . '</div>';
						}
					}
					else
					{
						$sales = '-';
					}
					$form['totalsales'] = $sales;
					$form['inventorycount'] = number_format($res['inventory']);
					$form['nonshipmentviolations'] = number_format($res['nonshipmentviolations']);
					$form['orderclaimsresponded'] = number_format($res['orderclaimsresponded']);
					$form['storename'] = ((!empty($res['storename'])) ? o($res['storename']) : '-');
					// account system
					$form['account_number'] = $res['account_number'];
					$form['available_balance'] = $sheel->currency->format($res['available_balance']);
					$form['total_balance'] = $sheel->currency->format($res['total_balance']);
					// points system
					$form['points_available'] = number_format($res['rewardpoints']);
					$form['points_pending'] = 0;
					$form['points_nextreleasedate'] = '-'; // 02-May-2019 (13), 03-May-2019 (5)
					$form['points_nextreleasedatepoints'] = 0;
		                        if (!empty($res['rewardpoints_delay']))
		                        {
						$pointtemp = array(); // unique dates with count totals
						$firstreleasedate = '';
		                                $delay = json_decode($res['rewardpoints_delay'], true);
						$c = 0;
		                                foreach ($delay AS $releasedate => $pointsarray)
		                                {
							foreach ($pointsarray AS $points)
							{
								if ($points['points'] > 0)
								{
									$form['points_pending'] += $points['points'];
									if (!isset($pointtemp[$releasedate]))
									{
										$pointtemp[$releasedate] = $points['points'];
									}
									else
									{
										$pointtemp[$releasedate] += $points['points'];
									}
								}
							}
							if ($c == 0)
							{
								$firstreleasedate = $releasedate;
								$form['points_nextreleasedate'] = $sheel->common->print_date($releasedate, 'd-M-Y', false, false);
							}
							$c++;
		                                }
						$form['points_nextreleasedatepoints'] = $pointtemp[$firstreleasedate];
						// presentation
						$form['points_pending'] = number_format($form['points_pending']);
						$form['points_nextreleasedatepoints'] = number_format($form['points_nextreleasedatepoints']);
						$form['points_nextreleasedate'] .= ' (' . $form['points_nextreleasedatepoints'] . ')';
		                        }
					$temp = $sheel->accounting->fetch_user_balance_owing(intval($sheel->GPC['userid']));
					$form['balance_owing'] = $sheel->currency->format($temp['balanceowing']);
					$countryid = $sheel->common_location->fetch_country_id($res['location'], $_SESSION['sheeldata']['user']['slng']);
					$form['country_pulldown'] = $sheel->common_location->construct_country_pulldown($countryid, $res['location'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid');
					$form['state_pulldown'] = '<div id="stateid">' . $sheel->common_location->construct_state_pulldown($countryid, $res['state'], 'state', false, false, 0, 'draw-select', 0, 'city', 'cityid') . '</div>';
					$form['city_pulldown'] = '<div id="cityid">' . $sheel->common_location->construct_city_pulldown($res['state'], 'city', $res['city'], false, false, 'draw-select') . '</div>';
					$form['language_pulldown'] = $sheel->language->construct_language_pulldown('languageid', $res['languageid'], 'draw-select', 'form[languageid]');
					$form['secretquestion'] = $sheel->construct_pulldown('secretquestion', 'form[secretquestion]', $sheel->template->meta['secretquestions'], $res['secretquestion'], 'class="draw-select"');
					$form['timezone_pulldown'] = $sheel->datetimes->timezone_pulldown('timezone', $res['timezone'], false, true, 'draw-select', 'form[timezone]');
					$form['currency_pulldown'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', '');
					$form['gender_pulldown'] = $sheel->construct_pulldown('gender', 'form[gender]', array('male' => '{_male}', 'female' => '{_female}', '' => '{_unknown}'), $res['gender'], 'class="draw-select"');
					$form['customquestions'] = $sheel->registration_questions->construct_register_questions(0, 'updateprofileadmin', intval($sheel->GPC['userid']));
					$sqll = $sheel->db->query("
						SELECT landingpage
						FROM " . DB_PREFIX . "visits
						WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						ORDER BY lasthit DESC
						LIMIT 1
					");
					if ($sheel->db->num_rows($sqll) > 0)
					{
						$resl = $sheel->db->fetch_array($sqll, DB_ASSOC);
						$form['lastlocation'] = '<a href="' . HTTPS_SERVER . $resl['landingpage'] . '" target="_blank" title="' . o($resl['landingpage']) . '">' . $sheel->shorten($resl['landingpage'], 55) . '</a>';
					}
					else
					{
						$form['lastlocation'] = '-';
					}
					$options = $sheel->shipping->fetch_billing_profile_select_options(intval($sheel->GPC['userid']));
					$form['billing_profiles_pulldown'] = $sheel->construct_pulldown('billingprofiles', 'form[billingprofiles]', $options['billing'], '', 'class="draw-select"');
					$options = $sheel->shipping->fetch_shipping_profile_select_options(intval($sheel->GPC['userid']));
					$form['shipping_profiles_pulldown'] = $sheel->construct_pulldown('shippingprofiles', 'form[shippingprofiles]', $options['shipping'], '', 'class="draw-select"');
					unset($options);
					$options = array();
					$sqlp = $sheel->db->query("
						SELECT cc_id, name_on_card, creditcard_expiry, creditcard_number, creditcard_type
						FROM " . DB_PREFIX . "creditcards
						WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
					");
					if ($sheel->db->num_rows($sqlp) > 0)
					{
						while ($resp = $sheel->db->fetch_array($sqlp, DB_ASSOC))
						{
							$options[$resp['cc_id']] = $resp['name_on_card'] . ' &ndash; {_expiry}: ' . $resp['creditcard_expiry'] . ' ({_' . $resp['creditcard_type'] . '})';
						}
					}
					else
					{
						$options = array('0' => '{_no_credit_card_profiles_added}');
					}
					$form['creditcard_profiles_pulldown'] = $sheel->construct_pulldown('creditcardprofiles', 'form[creditcardprofiles]', $options, '', 'class="draw-select"');
					$options = array();
					$sqlp = $sheel->db->query("
						SELECT bank_id, beneficiary_account_name, beneficiary_bank_name, bank_account_type, beneficiary_account_number, destination_currency_id
						FROM " . DB_PREFIX . "bankaccounts
						WHERE user_id = '" . intval($sheel->GPC['userid']) . "'
					");
					if ($sheel->db->num_rows($sqlp) > 0)
					{
						while ($resp = $sheel->db->fetch_array($sqlp, DB_ASSOC))
						{
							$options[$resp['bank_id']] = $resp['beneficiary_account_name'] . ' &ndash; ' . $resp['beneficiary_bank_name'] . ' (' . $resp['bank_account_type'] . ')';
						}
					}
					else
					{
						$options = array('0' => '{_no_bank_accounts_profile_added}');
					}
					$form['bank_profiles_pulldown'] = $sheel->construct_pulldown('bankprofiles', 'form[bankprofiles]', $options, '', 'class="draw-select"');
				}

				$form['transaction_type_pulldown'] = $sheel->construct_pulldown('transactiontype', 'form[action]', $txntypes, '', 'class="draw-select"');
				$form['cost'] = '{_free}';
				$form['paymethod'] = '-';
				$form['billcycle'] = '-';
				$form['oldsubscriptionid'] = '';
				$form['oldroleid'] = '';
				$sql = $sheel->db->query("
					SELECT s.subscriptionid, s.cost, u.paymethod, u.recurring_gateway, u.startdate, u.renewdate, u.active, u.roleid
					FROM " . DB_PREFIX . "subscription_user u
					LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
					LEFT JOIN " . DB_PREFIX . "invoices i ON (u.invoiceid = i.invoiceid)
					WHERE u.user_id = '" . intval($sheel->GPC['userid']) . "'
						AND s.type = 'product'
					ORDER BY u.renewdate DESC
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) > 0)
				{
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$form['cost'] = ($row['cost'] > 0) ? $sheel->currency->format($row['cost']) : '{_free}';
						$form['paymethod'] = $sheel->accounting_print->print_paymethod_icon($row['paymethod'], $row['recurring_gateway'], false);
						if ($row['active'] == 'yes')
						{
							$row['startdate'] = $sheel->common->print_date($row['startdate'], 'm/d/Y');
							$row['renewdate'] = $sheel->common->print_date($row['renewdate'], 'm/d/Y');
							$form['billcycle'] = $row['startdate'] . ' &ndash; ' . $row['renewdate'];
						}
						else
						{
							$form['billcycle'] = $row['paymethod'] = $row['cost'] = '-';
						}
						$form['plan_pulldown'] = $sheel->subscription->plans_pulldown('draw-select', $row['subscriptionid'], 'onchange="if (fetch_js_object(\'subscriptionid\').options[fetch_js_object(\'subscriptionid\').selectedIndex].value != \'' . $row['subscriptionid'] . '\'){jQuery(\'#newplanaction\').show();}else{jQuery(\'#newplanaction\').hide();}"');
						$form['role_pulldown'] = $sheel->subscription_role->print_subscription_roles_pulldown($row['subscriptionid'], $row['roleid'], 1, '', '', 'draw-select', 'form_roleid', 'form[roleid]', false);
						$planactions = array(
							'active' => '{_mark_active_new_waived_transaction_will_create}',
							'activepaid' => '{_mark_active_paid_payment_made_outside_of_marketplace}',
							'inactive' => '{_mark_inactive_new_unpaid_transaction_will_create}'
						);
						$form['plan_action_pulldown'] = $sheel->construct_pulldown('form_planaction', 'form[planaction]', $planactions, '', 'class="draw-select"');
						unset($planactions);
						$form['oldsubscriptionid'] = $row['subscriptionid'];
						$form['oldroleid'] = $row['roleid'];
					}
				}
				else
				{
					$form['plan_pulldown'] = $sheel->subscription->plans_pulldown('draw-select', '', '', 'form[subscriptionid]', 'subscriptionid', true);
					$form['role_pulldown'] = $sheel->subscription_role->print_role_pulldown('', 1, 0, 1, '', '', 'draw-select', 'form_roleid', 'form[roleid]', true);
				}
				$transactions = array();
				$sql = $sheel->db->query("
					SELECT id, datetime, userid, description, staffnotes, invoiceid, credit, debit, sumcredit, sumdebit, sumcredit - sumdebit AS balance
					FROM (SELECT id, datetime, userid, description, staffnotes, invoiceid, credit, debit, credit + COALESCE((
						SELECT SUM(credit)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumcredit, debit + COALESCE((
						SELECT SUM(debit)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumdebit
					FROM " . DB_PREFIX . "transactions AS a
					) AS b
					WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						AND (credit > 0 OR debit > 0)
					ORDER BY id DESC
					LIMIT 10
				");
				if ($sheel->db->num_rows($sql) > 0)
				{
					$altrows = 0;
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$row['datepaid'] = $sheel->common->print_date($row['datetime'], 'd-M-Y', false, false);
						$row['description'] = stripslashes($row['description']);
						$row['debit'] = (($row['debit'] > 0) ? $row['debit'] : '');
						$row['credit'] = (($row['credit'] > 0) ? $row['credit'] : '');
						$transactions[] = $row;
					}
				}
				$sql = $sheel->db->query("
					SELECT id, datetime, userid, description, staffnotes, invoiceid, creditpoints, debitpoints, sumcreditpoints, sumdebitpoints, sumcreditpoints - sumdebitpoints AS balance
					FROM (SELECT id, datetime, userid, description, staffnotes, invoiceid, creditpoints, debitpoints, creditpoints + COALESCE((
						SELECT SUM(creditpoints)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumcreditpoints, debitpoints + COALESCE((
						SELECT SUM(debitpoints)
						FROM " . DB_PREFIX . "transactions b
						WHERE b.id < a.id AND userid = '" . intval($sheel->GPC['userid']) . "'), 0
					) AS sumdebitpoints
					FROM " . DB_PREFIX . "transactions AS a
					) AS b
					WHERE userid = '" . intval($sheel->GPC['userid']) . "'
						AND (creditpoints > 0 OR debitpoints > 0)
					ORDER BY id DESC
					LIMIT 10
				");
				if ($sheel->db->num_rows($sql) > 0)
				{
					$altrows = 0;
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$row['datepaid'] = $sheel->common->print_date($row['datetime'], 'd-M-Y', false, false);
						$row['description'] = stripslashes($row['description']);
						$row['debit'] = (($row['debitpoints'] > 0) ? $row['debitpoints'] : '');
						$row['credit'] = (($row['creditpoints'] > 0) ? $row['creditpoints'] : '');
						$pointstransactions[] = $row;
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
				if ($sheel->db->num_rows($sql) > 0)
				{
					$altrows = 0;
					while ($row = $sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$row['datetime'] = $sheel->common->print_date($sheel->datetimes->fetch_datetime_from_timestamp($row['datetime']), 'd-M-Y', false, false);
						$row['description'] = stripslashes(o($row['message']));
						$consentlog[] = $row;
					}
				}
			}
		}
		else
		{
			if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'deleteusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->remove_user($ids, true, true, true, true, true, false);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'deleteusersnoemail')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->remove_user($ids, true, true, true, true, true, true);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'activateusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->activate_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'suspendusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->suspend_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'unsuspendusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->unsuspend_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'banusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->ban_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'unbanusers')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->unban_user($ids);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
					$sheel->template->templateregistry['message'] = '{_no_users_were_selected_for_removal_please_try_again}';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'deleteprofileanswer')
			{
				if (isset($sheel->GPC['xid']) AND !empty($sheel->GPC['xid']))
				{
					$sheel->db->query("
						DELETE FROM " . DB_PREFIX . "register_answers
						WHERE answerid = '" . intval($sheel->GPC['xid']) . "'
						LIMIT 1
					");
					$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer profile answer successfully deleted', 'A profile answer has been successfully deleted for this user.');
					die(json_encode(array('response' => '1', 'message' => 'A profile answer has been successfully deleted for this user.')));
				}
				else
				{
					$sheel->template->templateregistry['message'] = 'This profile answer was not deleted for the user.';
					die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
				}
			}
			else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'canceldeleterequest')
			{
				if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]))
                                {
					$ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
					$response = array();
					$response = $sheel->admincp_users->reject_user_delete_request($ids, true);
					$sheel->template->templateregistry['success'] = $response['success'];
					$sheel->template->templateregistry['errors'] = $response['errors'];
					set_cookie('inline' . $sheel->GPC['checkboxid'], '', false);
					die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
				}
				else
				{
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
			$sheel->GPC['page'] = (!isset($sheel->GPC['page']) OR isset($sheel->GPC['page']) AND $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
			$sheel->GPC['pp'] = (!isset($sheel->GPC['pp']) OR isset($sheel->GPC['pp']) AND $sheel->GPC['pp'] <= 0) ? $sheel->config['globalfilters_maxrowsdisplay'] : intval($sheel->GPC['pp']);
			$where = "u.user_id > 0";
			$extrasql = '';
			if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'staff')
			{
				$where = "u.isadmin = '1'";
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'suspended')
			{
				$where = "u.status = 'suspended'";
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'banned')
			{
				$where = "u.status = 'banned'";
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'moderated')
			{
				$where = "u.status = 'moderated'";
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'unverified')
			{
				$where = "u.status = 'unverified'";
			}
			else if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'deleterequests')
			{
				$where = "u.requestdeletion = '1'";
			}
			if (isset($sheel->GPC['filter']) AND isset($sheel->GPC['q']) AND $sheel->GPC['q'] != '')
			{
				switch ($sheel->GPC['filter'])
				{
					case 'name': // Full name / Username
					{
						$sheel->GPC['q1'] = $sheel->GPC['q2'] = '';
						if (strrchr($sheel->GPC['q'], ' '))
						{
							$tmp = explode(' ', trim($sheel->GPC['q']));
							$sheel->GPC['q1'] = trim($tmp[0]);
							$sheel->GPC['q2'] = trim($tmp[1]);
							$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q1']) . "%' AND u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q2']) . "%'))";
						}
						else
						{
							$extrasql = "AND (u.first_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.last_name LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.username LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')";
						}
						break;
					}
					case 'location': // city, state or country or zipcode
					{
						$extrasql = "AND (l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.city LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.state LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR u.zip_code LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')";
						break;
					}
					case 'email':
					{
						$extrasql = "AND u.email LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%'";
						break;
					}
                    case 'customer':
                    {
                        $extrasql = "AND u.customerid = '" . $sheel->db->escape_string($sheel->GPC['q']) . "'";
                        break;
                    }
				}
			}
			$sql = $sheel->db->query("
				SELECT
				u.user_id, u.username, u.first_name, u.last_name, u.customerid, u.email, u.phone, u.city, u.state, u.zip_code, u.status,  u.roleid, u.isadmin, u.permissions, u.registersource, u.lastseen, u.rewardpoints, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, l.cc
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
			$number = (int)$sheel->db->num_rows($sql2);
			$form['number'] = number_format($number);
			if ($sheel->db->num_rows($sql) > 0)
			{
				$row_count = 0;
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
				{
					$flags = '';
					if ($res['status'] == 'suspended')
					{
						$flags .= '<span title="{_suspended}"><i class="badge badge--warning" aria-hidden="true">S</i></span>';
					}
					else if ($res['status'] == 'banned')
					{
						$flags .= '<span title="{_banned}"><i class="badge badge--critical" aria-hidden="true">B</i></span>';
					}
					else if ($res['status'] == 'unverified' OR empty($res['email']) OR $res['email'] == '{_unknown}')
					{
						$flags .= '<span title="{_email_unverified}"><i class="badge badge--critical" aria-hidden="true">EU</i></span>';
					}
					else if ($res['status'] == 'moderated')
					{
						$flags .= '<span title="Moderation pending user"><i class="badge badge--info" aria-hidden="true">M</i></span>';
					}
					if (empty($res['registersource']))
					{
						$res['registersource'] = 'n/a';
					}
					$res['flags'] = $flags;
					$res['switch'] = '<span title="{_switch_to_another_user}"><a href="' . HTTPS_SERVER_ADMIN .'users/switch/' . $res['user_id'] . '/" data-no-turbolink>{_sign_in}</a></span>';
					$res['icon'] = '<img src="' . $sheel->config['imgcdn'] . 'flags/' . strtolower($res['cc']) . '.png" border="0" alt="" id="" />';
					$res['lastseen'] = $sheel->common->print_date($res['lastseen'], 'M j \@ g:ia', 0, 0);
					$customer = $sheel->admincp_customers->get_customer_details($res['customerid']);
                    $res['customer_ref'] = $customer['customer_ref'];
                    $res['customername'] = $customer['customername'];
					$res['plan'] = $res['isadmin']=='1'?'Admin':$sheel->subscription->getname($customer['subscriptionid']);
                    $res['role'] = $sheel->role->print_role($res['roleid']);
					$users[] = $res;
					$row_count++;
				}
			}
			else
			{
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
}
else
{
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>
