<?php
define('LOCATION', 'ajax');
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
	'updateruleline' => array('skipsession' => true),
	'updatetypeline' => array('skipsession' => true),
	'updatecategoryline' => array('skipsession' => true),
	'addtypeline' => array('skipsession' => true),
	'addcategoryline' => array('skipsession' => true),
	'showtypes' => array('skipsession' => true),
	'showimpactvalues' => array('skipsession' => true),
	'showrule' => array('skipsession' => true),
	'getorderdetails' => array('skipsession' => true),
	'getassemblydetails' => array('skipsession' => true),
	'getassemblyscans' => array('skipsession' => true),
	'updatestaffdetails' => array('skipsession' => true),
	'getdefaultuom' => array('skipsession' => true),
	'addmeasurement' => array('skipsession' => true),
	'addsize' => array('skipsession' => true),
	'build' => array('skipsession' => true),
	'version' => array('skipsession' => true),
	'bulkmailer' => array('skipsession' => true),
	'upgradelog' => array('skipsession' => true),
	'ldaplog' => array('skipsession' => true),
	'smtplog' => array('skipsession' => true),
	'forceautoupdate' => array('skipsession' => true),
	'consent' => array('skipsession' => false),
	'tzoffset' => array('skipsession' => true),
	'acpcheckusername' => array('skipsession' => true),
	'acpcheckemail' => array('skipsession' => true)
);

if (isset($sheel->GPC['do'])) {
	if (isset($methods[$sheel->GPC['do']]['skipsession'])) {
		define('SKIP_SESSION', $methods[$sheel->GPC['do']]['skipsession']);
	} else {
		define('SKIP_SESSION', false);
	}
	if ($sheel->GPC['do'] == 'updatebulkstaff') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
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
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
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
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
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
	} else if ($sheel->GPC['do'] == 'heropicture') { // admin hero picture info
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
			$filename = ((isset($sheel->GPC['filename'])) ? $sheel->GPC['filename'] : '');
			$mode = ((isset($sheel->GPC['mode'])) ? o($sheel->GPC['mode']) : 'homepage');
			$folder = ((isset($sheel->GPC['folder'])) ? o($sheel->GPC['folder']) : 'heros');
			$cid = ((isset($sheel->GPC['cid'])) ? intval($sheel->GPC['cid']) : 0);
			$id = ((isset($sheel->GPC['id'])) ? intval($sheel->GPC['id']) : 0);
			if (!empty($filename)) {
				if ($mode == 'load' and $id > 0) {
					$sql = $sheel->db->query("
						SELECT imagemap, mode, filename, sort, width, height, styleid
						FROM " . DB_PREFIX . "hero
						WHERE filename = '" . $sheel->db->escape_string($filename) . "'
							AND id = '" . intval($id) . "'
						LIMIT 1
					");
					if ($sheel->db->num_rows($sql) > 0) {
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$themeselect = '<div class="draw-select__wrapper draw-input--has-content">' . $sheel->styles->print_styles_pulldown($res['styleid'], '', 'src_styleid', 'draw-select') . '</div>';
						$pulldown = '<div class="draw-select__wrapper draw-input--has-content"><select name="src_location" id="src_location" class="draw-select" onchange="((jQuery(\'#src_location option:selected\').attr(\'type\').length > 0) ? jQuery(\'#src_mode\').val(jQuery(\'#src_location option:selected\').attr(\'type\')) : jQuery(\'#src_mode\').val(\'\'))"><optgroup label="{_location}"><option value="homepage" id="0" cid="0"' . (($res['mode'] == 'homepage') ? ' selected="selected"' : '') . ' type="homepage">{_homepage}</option><option value="landingpage" id="0" cid="0"' . (($res['mode'] == 'landingpage') ? ' selected="selected"' : '') . ' type="landingpage">Landing Page</option></optgroup>';

						$pulldown .= '</select><input type="hidden" name="src_mode" id="src_mode" value="' . $res['mode'] . '" /></div>';
						$sheel->template->templateregistry['results'] = "$res[sort]|$res[imagemap]|''|$pulldown|$res[width]|$res[height]|$themeselect";
						die($sheel->template->parse_template_phrases('results'));
					}
				} else if ($mode == 'insert') {
					$sql = $sheel->db->query("
						SELECT imagemap, mode, filename, sort
						FROM " . DB_PREFIX . "hero
						ORDER BY sort DESC
						LIMIT 1
					");
					if ($sheel->db->num_rows($sql) > 0) {
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$themeselect = '<div class="draw-select__wrapper draw-input--has-content">' . $sheel->styles->print_styles_pulldown($sheel->config['defaultstyle'], '', 'src_styleid', 'draw-select') . '</div>';
						$pulldown = '<div class="draw-select__wrapper draw-input--has-content"><select name="src_location" id="src_location" class="draw-select" onchange="((jQuery(\'#src_location option:selected\').attr(\'type\').length > 0) ? jQuery(\'#src_mode\').val(jQuery(\'#src_location option:selected\').attr(\'type\')) : jQuery(\'#src_mode\').val(\'\'))"><option value="">{_select_a_location}</option><optgroup label="{_location}"><option value="homepage" id="0" type="homepage">{_homepage}</option><option value="landingpage" id="0" type="landingpage">Landing Page</option></optgroup>';
						$pulldown .= '</select><input type="hidden" name="src_mode" id="src_mode" /></div>';
						$width = $height = '';
						$targetpath = DIR_ATTACHMENTS . $folder . '/' . $filename;
						if (file_exists($targetpath)) {
							list($width, $height, $type, $attr) = getimagesize($targetpath);
						}
						$sheel->template->templateregistry['results'] = "$res[sort]|$res[imagemap]|''|$pulldown|$width|$height|$themeselect";
						echo $sheel->template->parse_template_phrases('results');
						exit();
					} else {
						echo "10|";
						exit();
					}
				}
			}
		}
		echo '';
		exit();
	} else if ($sheel->GPC['do'] == 'updateruleline') {
		if (isset($sheel->GPC['recordid']) and !empty($sheel->GPC['recordid']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname']) and isset($sheel->GPC['newvalue']) and !empty($sheel->GPC['newvalue'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			$sheel->db->query("
					UPDATE " . DB_PREFIX . "size_rules
					SET `" . $sheel->GPC['fieldname'] . "` = '" . $sheel->GPC['newvalue'] . "'
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					", 0, null, __FILE__, __LINE__);
			$sql = $sheel->db->query("
					SELECT `" . $sheel->GPC['fieldname'] . "`
					FROM " . DB_PREFIX . "size_rules
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					");
			if ($sheel->db->num_rows($sql) > 0) {
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$value = $res[$sheel->GPC['fieldname']];
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'updatetypeline') {
		if (isset($sheel->GPC['recordid']) and !empty($sheel->GPC['recordid']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname']) and isset($sheel->GPC['newvalue'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			if ($sheel->GPC['fieldname'] == 'isdefault') {
				$sql = $sheel->db->query("
					SELECT categoryid
					FROM " . DB_PREFIX . "size_types
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					");
				if ($sheel->db->num_rows($sql) > 0) {
					$res = $sheel->db->fetch_array($sql, DB_ASSOC);
					$sheel->db->query("
					UPDATE " . DB_PREFIX . "size_types
					SET isdefault = '0'
					WHERE categoryid = '" . $res['categoryid'] . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			$sheel->db->query("
					UPDATE " . DB_PREFIX . "size_types
					SET `" . $sheel->GPC['fieldname'] . "` = '" . $sheel->GPC['newvalue'] . "'
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					", 0, null, __FILE__, __LINE__);
			$sql = $sheel->db->query("
					SELECT `" . $sheel->GPC['fieldname'] . "`
					FROM " . DB_PREFIX . "size_types
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					");
			if ($sheel->db->num_rows($sql) > 0) {
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$value = $res[$sheel->GPC['fieldname']];
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'addtypeline') {
		if (isset($sheel->GPC['code']) and !empty($sheel->GPC['code']) and isset($sheel->GPC['gender']) and !empty($sheel->GPC['gender']) and isset($sheel->GPC['category']) and !empty($sheel->GPC['category']) and isset($sheel->GPC['needsize']) and isset($sheel->GPC['isdefault'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			if ($sheel->GPC['isdefault'] == '1') {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "size_types
					SET isdefault = '0'
					WHERE categoryid = '" . $sheel->GPC['category'] . "'
					", 0, null, __FILE__, __LINE__);
			}
			$sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "size_types
                            (id, code, needsize, gender, categoryid, isdefault)
                            VALUES
                            (NULL,
                            '" . $sheel->db->escape_string($sheel->GPC['code']) . "',
                            '" . $sheel->GPC['needsize'] . "',
							'" . $sheel->GPC['gender'] . "',
							'" . $sheel->GPC['category'] . "',
							'" . $sheel->GPC['isdefault'] . "')
                        ");

			$value = $sheel->db->insert_id();
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'updatecategoryline') {
		if (isset($sheel->GPC['recordid']) and !empty($sheel->GPC['recordid']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname']) and isset($sheel->GPC['newvalue'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			$sheel->db->query("
					UPDATE " . DB_PREFIX . "size_type_categories
					SET `" . $sheel->GPC['fieldname'] . "` = '" . $sheel->GPC['newvalue'] . "'
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					", 0, null, __FILE__, __LINE__);
			$sql = $sheel->db->query("
					SELECT `" . $sheel->GPC['fieldname'] . "`
					FROM " . DB_PREFIX . "size_type_categories
					WHERE id = '" . $sheel->GPC['recordid'] . "'
					LIMIT 1
					");
			if ($sheel->db->num_rows($sql) > 0) {
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$value = $res[$sheel->GPC['fieldname']];
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'addcategoryline') {
		if (isset($sheel->GPC['code']) and !empty($sheel->GPC['code']) and isset($sheel->GPC['name']) and !empty($sheel->GPC['name'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			$sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "size_type_categories
                            (id, code, name)
                            VALUES
                            (NULL,
                            '" . $sheel->db->escape_string($sheel->GPC['code']) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['name']) . "')
                        ");

			$value = $sheel->db->insert_id();
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'showtypes') {
		if (isset($sheel->GPC['gendername']) and !empty($sheel->GPC['gendername']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$gender = $sheel->GPC['gendername'];
		} else {
			$gender = 'Male';
		}
		$html = $sheel->common_sizingrule->construct_type_checkbox($gender, true);
		$sheel->template->templateregistry['showtypes'] = $html;
		echo $sheel->template->parse_template_phrases('showtypes');
		exit();
	} else if ($sheel->GPC['do'] == 'showimpactvalues') {
		if (isset($sheel->GPC['impactname']) and !empty($sheel->GPC['impactname']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$impact = $sheel->GPC['impactname'];
		} else {
			$impact = 'Fit';
		}
		$html = $sheel->common_sizingrule->construct_impactvalue_pulldown($impact, 'form[impactvalue_1]', '', false, false, 'draw-select');
		$sheel->template->templateregistry['showimpactvalues'] = $html;
		echo $sheel->template->parse_template_phrases('showimpactvalues');
		exit();
	} else if ($sheel->GPC['do'] == 'showrule') {
		if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'reset') {
			$html = '<fieldset class="mb-20"><legend><span class="smaller litegray left prl-6 pt-12 uc"><img src="' . $sheel->config['imgcdn'] . 'v5/ico_working.gif" width="13" height="13" alt="{_loading}" /></span></legend></fieldset>';
			$sheel->template->templateregistry['showimpactvalues'] = $html;
			echo $sheel->template->parse_template_phrases('showimpactvalues');
			exit();
		}

		if (isset($sheel->GPC['impactname']) and !empty($sheel->GPC['impactname'])) {
			$impact = $sheel->GPC['impactname'];
		} else {
			$impact = 'Fit';
		}
		$html .= '<fieldset class="mb-20">';
		$html .= '<legend><span class="smaller litegray left prl-6 pt-12 uc">Rule #' . $sheel->GPC['rulenumber'] . ': </span></legend>';
		$html .= '<div class="draw-grid draw-grid--no-outside-padding draw-grid--inner-grid">';
		$html .= '<div class="draw-grid__cell">';
		$html .= ' <div class="draw-input-wrapper">';
		$html .= '<label>{_value_high}</label>';
		$html .= '<input class="draw-input with-add-on" size="2" type="text" value="0" name="rank_' . $sheel->GPC['rulenumber'] . '" id="rank_' . $sheel->GPC['rulenumber'] . '"  placeholder="">';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="draw-grid__cell">';
		$html .= '<label>{_impact} {_value}</label>';
		$html .= '<div class="draw-select__wrapper draw-input--has-content" id="value-wraper">';
		$html .= '<div  id="value-wrapper">';
		$html .= $sheel->common_sizingrule->construct_impactvalue_pulldown($impact, 'form[impactvalue_' . $sheel->GPC['rulenumber'] . ']', '', false, false, 'draw-select');
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="draw-grid__cell">';
		$html .= '<div class="draw-input-wrapper">';
		$html .= '<label>{_value_low}</label>';
		$html .= '<input class="draw-input with-add-on" size="50" type="text" value="0" name="valuelow_' . $sheel->GPC['rulenumber'] . '" id="valuelow_' . $sheel->GPC['rulenumber'] . '"  placeholder="">';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="draw-grid__cell">';
		$html .= ' <div class="draw-input-wrapper">';
		$html .= '<label>{_value_high}</label>';
		$html .= '<input class="draw-input with-add-on" size="50" type="text" value="0" name="valuehigh_' . $sheel->GPC['rulenumber'] . '" id="valuehigh_' . $sheel->GPC['rulenumber'] . '"  placeholder="">';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="draw-grid__cell">';
		$html .= ' <label>{_uom}</label>';
		$html .= '<div class="draw-select__wrapper draw-input--has-content" id="value-wraper">';
		$html .= '<div>';
		$html .= $sheel->common_sizingrule->construct_uom_pulldown('form[uom_' . $sheel->GPC['rulenumber'] . ']', 'CM', false, false, 'draw-select');
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</fieldset>';
		$sheel->template->templateregistry['showimpactvalues'] = $html;
		echo $sheel->template->parse_template_phrases('showimpactvalues');
		exit();
	} else if ($sheel->GPC['do'] == 'getorderdetails') {
		if (isset($sheel->GPC['orderno']) and !empty($sheel->GPC['orderno']) and isset($sheel->GPC['customerno']) and !empty($sheel->GPC['customerno'])) {
			$html = $sheel->common_order->get_order_details($sheel->GPC['orderno'], $sheel->GPC['customerno']);
			$sheel->template->templateregistry['orderdetails'] = $html;
			echo $sheel->template->parse_template_phrases('orderdetails');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'getassemblydetails') {
		if (isset($sheel->GPC['orderno']) and !empty($sheel->GPC['orderno']) and isset($sheel->GPC['customerno']) and !empty($sheel->GPC['customerno'])) {
			$html = $sheel->common_order->get_assembly_details($sheel->GPC['orderno'], $sheel->GPC['customerno']);
			$sheel->template->templateregistry['assemblydetails'] = $html;
			echo $sheel->template->parse_template_phrases('assemblydetails');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'getassemblyscans') {
		if (isset($sheel->GPC['assemblyno']) and !empty($sheel->GPC['assemblyno']) and isset($sheel->GPC['orderno']) and !empty($sheel->GPC['orderno']) and isset($sheel->GPC['customerno']) and !empty($sheel->GPC['customerno'])) {
			$html = $sheel->common_order->get_assembly_scans($sheel->GPC['assemblyno'], $sheel->GPC['orderno'], $sheel->GPC['customerno']);
			$sheel->template->templateregistry['assemblyscans'] = $html;
			echo $sheel->template->parse_template_phrases('assemblyscans');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'updatestaffdetails') {
		if (isset($sheel->GPC['recordid']) and !empty($sheel->GPC['recordid']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname']) and isset($sheel->GPC['newvalue'])) {
			$sheel->template->templateregistry['error'] = '';
			$response = '0';
			switch ($sheel->GPC['fieldname']) {
				case 'mValue': {
					if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $sheel->GPC['company'])) {
						$response = '1';
						$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
					}

					$updateResponse = $sheel->dynamics->update(
						$sheel->GPC['recordid'],
						array(
							"@odata.etag" => $sheel->GPC['etag'],
							"value" => floatval($sheel->GPC['newvalue'])
						)
					);
					if ($updateResponse->isSuccess()) {
						$value = $sheel->GPC['newvalue'];
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					break;
				}

				case 'uom': {
					if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $sheel->GPC['company'])) {
						$response = '1';
						$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
					}

					$updateResponse = $sheel->dynamics->update(
						$sheel->GPC['recordid'],
						array(
							"@odata.etag" => $sheel->GPC['etag'],
							"uomCode" => $sheel->GPC['newvalue']
						)
					);
					if ($updateResponse->isSuccess()) {
						$value = $sheel->GPC['newvalue'];
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$searchcondition = '$filter=systemId eq ' . $sheel->GPC['recordid'];
					$apiResponse = $sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$measurement = $apiResponse->getData();
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$etag = $measurement['0']['@odata.etag'];
					break;
				}
				case 'sizeCode': {
					if (!$sheel->dynamics->init_dynamics('erStaffSizes', $sheel->GPC['company'])) {
						$response = '1';
						$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
					}

					$updateResponse = $sheel->dynamics->update(
						$sheel->GPC['recordid'],
						array(
							"@odata.etag" => $sheel->GPC['etag'],
							"sizeCode" => $sheel->GPC['newvalue']
						)
					);
					if ($updateResponse->isSuccess()) {
						$value = $sheel->GPC['newvalue'];
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$searchcondition = '$filter=systemId eq ' . $sheel->GPC['recordid'];
					$apiResponse = $sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$size = $apiResponse->getData();
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$etag = $size['0']['@odata.etag'];
					break;
				}
				case 'fitCode': {
					if (!$sheel->dynamics->init_dynamics('erStaffSizes', $sheel->GPC['company'])) {
						$response = '1';
						$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
					}

					$updateResponse = $sheel->dynamics->update(
						$sheel->GPC['recordid'],
						array(
							"@odata.etag" => $sheel->GPC['etag'],
							"fitCode" => $sheel->GPC['newvalue']
						)
					);
					if ($updateResponse->isSuccess()) {
						$value = $sheel->GPC['newvalue'];
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$searchcondition = '$filter=systemId eq ' . $sheel->GPC['recordid'];
					$apiResponse = $sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$size = $apiResponse->getData();
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$etag = $size['0']['@odata.etag'];
					break;
				}
				case 'cutCode': {
					if (!$sheel->dynamics->init_dynamics('erStaffSizes', $sheel->GPC['company'])) {
						$response = '1';
						$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
					}

					$updateResponse = $sheel->dynamics->update(
						$sheel->GPC['recordid'],
						array(
							"@odata.etag" => $sheel->GPC['etag'],
							"cutCode" => $sheel->GPC['newvalue']
						)
					);
					if ($updateResponse->isSuccess()) {
						$value = $sheel->GPC['newvalue'];
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$searchcondition = '$filter=systemId eq ' . $sheel->GPC['recordid'];
					$apiResponse = $sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$size = $apiResponse->getData();
					} else {
						$response = '1';
						$sheel->template->templateregistry['error'] = $updateResponse->getErrorMessage();
					}
					$etag = $size['0']['@odata.etag'];
					break;
				}
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error, 'etag' => $etag)));







	} else if ($sheel->GPC['do'] == 'addmeasurement') {
		if (isset($sheel->GPC['staffcode']) and !empty($sheel->GPC['staffcode']) and isset($sheel->GPC['mcategory']) and !empty($sheel->GPC['mcategory']) and isset($sheel->GPC['mvalue']) and !empty($sheel->GPC['mvalue']) and isset($sheel->GPC['uom']) and !empty($sheel->GPC['uom'])) {
			$sheel->template->templateregistry['error'] = '';
			if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $sheel->GPC['company'])) {
				$response = '1';
				$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
			}
			$addResponse = $sheel->dynamics->insert(
				array(
					"customerNo" => $sheel->GPC['customer'],
					"staffCode" => $sheel->GPC['staffcode'],
					"measurementCode" => $sheel->GPC['mcategory'],
					"positionCode" => $sheel->GPC['position'],
					"departmentCode" => $sheel->GPC['department'],
					"value" => floatval($sheel->GPC['mvalue']),
					"uomCode" => $sheel->GPC['uom']
				)
			);
			if ($addResponse->isSuccess()) {
				$response = '0';
			} else {
				$response = '1';
				$sheel->template->templateregistry['error'] = $addResponse->getErrorMessage();
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'addsize') {
		if (isset($sheel->GPC['staffcode']) and !empty($sheel->GPC['staffcode']) and isset($sheel->GPC['itemtype']) and !empty($sheel->GPC['itemtype']) and isset($sheel->GPC['size']) and !empty($sheel->GPC['size']) and isset($sheel->GPC['fit']) and !empty($sheel->GPC['fit'] and isset($sheel->GPC['cut']) and !empty($sheel->GPC['cut']))) {
			$sheel->template->templateregistry['error'] = '';
			if (!$sheel->dynamics->init_dynamics('erStaffSizes', $sheel->GPC['company'])) {
				$response = '1';
				$sheel->template->templateregistry['error'] = '{_inactive_dynamics_api}';
			}
			$addResponse = $sheel->dynamics->insert(
				array(
					"customerNo" => $sheel->GPC['customer'],
					"staffCode" => $sheel->GPC['staffcode'],
					"sizeType" => $sheel->GPC['itemtype'],
					"positionCode" => $sheel->GPC['position'],
					"departmentCode" => $sheel->GPC['department'],
					"sizeCode" => $sheel->GPC['size'],
					"fitCode" => $sheel->GPC['fit'],
					"cutCode" => $sheel->GPC['cut']
				)
			);
			if ($addResponse->isSuccess()) {
				$response = '0';
			} else {
				$response = '1';
				$sheel->template->templateregistry['error'] = $addResponse->getErrorMessage();
			}
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'getdefaultuom') {
		if (isset($sheel->GPC['mcategory']) and !empty($sheel->GPC['mcategory'])) {
			$sheel->template->templateregistry['error'] = '';
			$value = $sheel->common_sizingrule->get_default_uom($sheel->GPC['mcategory']) == '' ? 'CM' : $sheel->common_sizingrule->get_default_uom($sheel->GPC['mcategory']);
			$response = '0';
		} else {
			$sheel->template->templateregistry['error'] = '{_missing_parameters}';
			$value = 'CM';
			$response = '1';
		}
		$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'value' => $value, 'error' => $error)));
	} else if ($sheel->GPC['do'] == 'build') {
		die('001');
	} else if ($sheel->GPC['do'] == 'version') {
		die(VERSION . '.' . ((SVNVERSION == '001') ? '0' : SVNVERSION));
	} else if ($sheel->GPC['do'] == 'bulkmailer') { // admin panel bulk mailer
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
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
	} else if ($sheel->GPC['do'] == 'acpcheckusername') {
		$sheel->template->templateregistry['error'] = '';
		$response = '0';
		if ($sheel->common->is_username_banned($sheel->GPC['username'])) {
			$sheel->template->templateregistry['error'] = ((!empty($sheel->common->username_errors[0])) ? $sheel->common->username_errors[0] : '{_sorry_that_username_has_been_blocked}');
			$response = '1';
		} else if (empty($sheel->GPC['username']) or !isset($sheel->GPC['username'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_username}';
			$response = '1';
		}
		// make sure username doesn't conflict with another user
		$sqlusercheck = $sheel->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE username IN ('" . $sheel->db->escape_string($sheel->GPC['username']) . "')
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlusercheck) > 0) { // woops! change username for new user automatically.
			$sheel->template->templateregistry['error'] = '{_that_username_already_exists_in_our_system}';
			$response = '1';
		}
		$html = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'error' => $html)));
	} else if ($sheel->GPC['do'] == 'acpcheckemail') {
		$sheel->template->templateregistry['error'] = '';
		$response = '0';
		if (!isset($sheel->GPC['email']) or empty($sheel->GPC['email'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_email}';
			$response = '1';
		}
		if (!$sheel->common->is_email_valid($sheel->GPC['email'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_email}';
			$response = '1';
		}
		// make sure email doesn't conflict with another user
		$sqlusercheck = $sheel->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE email IN ('" . $sheel->db->escape_string($sheel->GPC['email']) . "')
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlusercheck) > 0) { // woops! can't use same email!
			$sheel->template->templateregistry['error'] = '{_that_email_address_already_exists_in_our_system}';
			$response = '1';
		}
		$html = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'error' => $html)));
	}
}
?>