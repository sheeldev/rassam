<?php
class admincp_importexport extends admincp
{
	var $notice = '';

	function import($what = '', $where = 'admincp', $xml = '', $slientmode = false, $noversioncheck = 1, $overwritephrases = 0, $setasdefault = 0, $compress = 0, $styleid = 0, $themeinfo = array())
	{
		$this->notice = '';
		if (empty($what)) {
			die('No import action [email, phrase] was specified.  Cannot continue.');
		}
		if (empty($where)) {
			die('No import location [admincp or xmlrpc] was specified.  Cannot continue.');
		}
		if (empty($xml)) {
			die('No xml file to import was specified.  Cannot continue.');
		}
		switch ($what) {
			case 'skin': {
					$this->import_skin($styleid);
					break;
				}
			case 'theme': {

					$this->import_theme($themeinfo);
					break;
				}
			case 'email': {
					$xml_encoding = 'UTF-8';
					$xml_encoding = mb_detect_encoding($xml);
					if ($xml_encoding == 'ASCII') {
						$xml_encoding = '';
					}
					$parser = xml_parser_create($xml_encoding);
					$data = array();
					xml_parse_into_struct($parser, $xml, $data);
					$error_code = xml_get_error_code($parser);
					xml_parser_free($parser);
					if ($error_code == 0) {
						$result = $this->sheel->xml->process_email_xml($data, $xml_encoding);
						if ($result['version'] != $this->sheel->config['version'] and $noversioncheck == 0) {
							if ($slientmode == false) {
								$this->print_action_failed('{_the_version_of_the_this_email_package_is_different_than} <strong>' . $this->sheel->config['version'] . '</strong>.  {_the_operation_has_aborted_due_to_a_version_conflict}', HTTPS_SERVER_ADMIN . 'settings/emails/');
								exit();
							} else {
								return false;
							}
						}
						
						$query = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $this->sheel->db->escape_string($result['langcode']) . "'
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($query) == 0) {
							if ($slientmode == false) {
								$this->print_action_failed('{_were_sorry_email_pack_uploading_requires_the_actual_language_to_already_exist}', HTTPS_SERVER_ADMIN . 'settings/emails/');
								exit();
							} else {
								return false;
							}
						}
						$query2 = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $this->sheel->db->escape_string($result['langcode']) . "'
					", 0, null, __FILE__, __LINE__);
					
						if ($this->sheel->db->num_rows($query2) > 0) {
							$phrasearray = $result['emailarray'];
							$lfn1 = 'subject_' . mb_substr($result['langcode'], 0, 3);
							$lfn2 = 'message_' . mb_substr($result['langcode'], 0, 3);
							$lfn3 = 'type';
							$lfn4 = 'varname';
							$lfn5 = 'name_' . mb_substr($result['langcode'], 0, 3);
							$lfn6 = 'messagehtml_' . mb_substr($result['langcode'], 0, 3);
							for ($i = 0; $i < count($phrasearray); $i++) {
								$product = ((isset($phrasearray[$i][5])) ? $phrasearray[$i][5] : 'sheel');
								$bodyhtml = (($compress) ? trim($this->minify($phrasearray[$i][10])) : trim($phrasearray[$i][10]));
								if ($this->sheel->db->num_rows($this->sheel->db->query("SELECT * FROM " . DB_PREFIX . "email WHERE varname = '" . $this->sheel->db->escape_string($phrasearray[$i][4]) . "' LIMIT 1", 0, null, __FILE__, __LINE__)) == 0) {
									if ($phrasearray[$i][4] != '') {
										$this->sheel->db->query("
										INSERT INTO " . DB_PREFIX . "email
										(`varname`)
										VALUES ('" . $this->sheel->db->escape_string($phrasearray[$i][4]) . "')
									", 0, null, __FILE__, __LINE__);
									} else {
										$this->notice .= "Error: Email template name '<strong>" . $phrasearray[$i][0] . "</strong>' could not be added due to a blank phrase existing within the xml file (near CDATA[])";
									}
									// cansend was 6
									if ($phrasearray[$i][1] != '') { // subject not blank .. update proper field content
										$this->sheel->db->query("
										UPDATE " . DB_PREFIX . "email
										SET `subject_original` = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "',
										`message_original` = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',
										`" . $lfn1 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "',
										`" . $lfn2 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',
										`" . $lfn3 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][3]) . "',
										`" . $lfn4 . "` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][4])) . "',
										`" . $lfn5 . "` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][0])) . "',
										`" . $lfn6 . "` = '" . trim($this->sheel->db->escape_string($bodyhtml)) . "',
										`product` = '" . trim($this->sheel->db->escape_string($product)) . "',
										`group` = '" . $this->sheel->db->escape_string($phrasearray[$i][6]) . "',
										`admin` = '" . intval($phrasearray[$i][9]) . "'
										WHERE `varname` = '" . $this->sheel->db->escape_string($phrasearray[$i][4]) . "'
										LIMIT 1
									", 0, null, __FILE__, __LINE__);
									} else {
										$this->notice .= "Error: email: <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank email template existing within the xml file (near CDATA)";
									}
								} else { // update email template
									if ($phrasearray[$i][1] != '') { // subject not blank
										$extraquery = '';
										if ($overwritephrases) { // overwrite email templates in db from xml file
											$extraquery .= "`" . $lfn1 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "',"; // subject_eng
											$extraquery .= "`" . $lfn2 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',"; // message_eng
											$extraquery .= "`" . $lfn5 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][0]) . "',"; // name_eng
											$extraquery .= "`" . $lfn6 . "` = '" . $this->sheel->db->escape_string($bodyhtml) . "',"; // messagehtml_eng
											$extraquery .= "`" . $lfn3 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][3]) . "',"; // type
											$extraquery .= "`" . $lfn4 . "` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][4])) . "',"; // varname
											$this->sheel->db->query("
											UPDATE " . DB_PREFIX . "email
											SET `subject_original` = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "',
											`message_original` = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',
											$extraquery
											`product` = '" . trim($this->sheel->db->escape_string($product)) . "',
											`group` = '" . $this->sheel->db->escape_string($phrasearray[$i][6]) . "',
											`admin` = '" . intval($phrasearray[$i][9]) . "'
											WHERE `varname` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][4])) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										} else { // do not overwrite existing email templates, only update required fields for this template
											$extraquery .= "`" . $lfn3 . "` = '" . $this->sheel->db->escape_string($phrasearray[$i][3]) . "',"; // type
											$extraquery .= "`" . $lfn4 . "` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][4])) . "',"; // varname
											$this->sheel->db->query("
											UPDATE " . DB_PREFIX . "email
											SET
											$extraquery
											`product` = '" . trim($this->sheel->db->escape_string($product)) . "',
											`group` = '" . $this->sheel->db->escape_string($phrasearray[$i][6]) . "',
											`admin` = '" . intval($phrasearray[$i][9]) . "'
											WHERE `varname` = '" . trim($this->sheel->db->escape_string($phrasearray[$i][4])) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										}
									} else {
										$this->notice .= "Error: template: <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank template existing within the xml file (near CDATA[])";
									}
								}
							}
							
							if ($slientmode == false) {
								$this->print_action_success('{_email_language_pack_importation_success}', HTTPS_SERVER_ADMIN . 'settings/emails/');
								exit();
							} else {
								return true;
							}
						} else {
							if ($slientmode == false) {
								$this->print_action_failed('{_were_sorry_this_language_does_not_exist}', HTTPS_SERVER_ADMIN . 'settings/emails/');
								exit();
							} else {
								return false;
							}
						}
					} else {
						$error_string = xml_error_string($error_code);
						if ($slientmode == false) {
							$this->print_action_failed('{_were_sorry_there_was_an_error_with_the_formatting}' . ' <strong>' . $error_string . '</strong>.', HTTPS_SERVER_ADMIN . 'settings/emails/');
							exit();
						} else {
							return false;
						}
					}
					break;
				}
			case 'phrase': {
					$data = array();
					$xml_encoding = 'UTF-8';
					$xml_encoding = mb_detect_encoding($xml);
					if ($xml_encoding == 'ASCII') {
						$xml_encoding = '';
					}

					$parser = xml_parser_create($xml_encoding);
					xml_parse_into_struct($parser, $xml, $data);
					$error_code = xml_get_error_code($parser);
					xml_parser_free($parser);
					if ($error_code == 0) {
						$result = $this->sheel->xml->process_lang_xml($data, $xml_encoding);
						if ($result['lang_version'] != $this->sheel->config['version'] and $noversioncheck == 0) {
							if ($slientmode == false) {
								$this->print_action_failed('{_the_version_of_the_this_language_xml_package_is_different_than_the_currently_installed_version} <strong>' . $this->sheel->config['version'] . '</strong>.  {_the_operation_has_aborted_due_to_a_language_version_conflict}<br /><br />{_tip_you_can_click_the_checkbox_on_the_previous_page_to_ignore_language_version_conflicts_which_will_ultimately_bypass_this_version_checker}', HTTPS_SERVER_ADMIN . 'settings/languages/');
								exit();
							} else {
								return false;
							}
						}

						$query = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $result['lang_code'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($query) == 0) {
							if ($slientmode == false) {
								$this->print_action_failed('{_were_sorry_the_language_package_being_uploaded_requires}', HTTPS_SERVER_ADMIN . 'settings/languages/');
								exit();
							} else {
								return false;
							}
						}
						$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "language
						SET title = '" . $this->sheel->db->escape_string($result['title']) . "',
						charset = '" . $this->sheel->db->escape_string($result['charset']) . "',
						locale = '" . $this->sheel->db->escape_string($result['locale']) . "',
						author = '" . $this->sheel->db->escape_string($result['author']) . "',
						languageiso = '" . $this->sheel->db->escape_string($result['languageiso']) . "',
						textdirection = '" . $this->sheel->db->escape_string($result['textdirection']) . "',
						canselect = '" . intval($result['canselect']) . "',
						replacements = '" . $this->sheel->db->escape_string($result['replacements']) . "',
						lastimport = '" . DATETIME24H . "'
						WHERE languagecode = '" . $this->sheel->db->escape_string($result['lang_code']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);

						$AllLanguages = array();

						$query = $this->sheel->db->query("
						SELECT languagecode
						FROM " . DB_PREFIX . "language
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($query) > 0) {
							while ($row = $this->sheel->db->fetch_array($query, DB_ASSOC)) {
								$AllLanguages[] = 'text_' . mb_substr($row['languagecode'], 0, 3);
							}

							$lfn = 'text_' . mb_substr($result['lang_code'], 0, 3);
							$phrasearray = $result['phrasearray'];
							$phrasecount = count($phrasearray);
							for ($i = 0; $i < $phrasecount; $i++) {
								$varexist = $this->sheel->db->query("
								SELECT phrasegroup
								FROM " . DB_PREFIX . "language_phrases
								WHERE varname = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
								if ($this->sheel->db->num_rows($varexist) == 0) {
									if (!empty($phrasearray[$i][0]) and !empty($phrasearray[$i][1])) {
										$this->sheel->db->query("
										INSERT INTO " . DB_PREFIX . "language_phrases
										(phrasegroup, varname, text_original)
										VALUES(
										'" . $this->sheel->db->escape_string($phrasearray[$i][0]) . "',
										'" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "',
										'" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "')
									", 0, null, __FILE__, __LINE__);
									} else {
										$this->notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrasegroup <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
									}
									foreach ($AllLanguages as $value) {
										if (!empty($phrasearray[$i][1]) and !empty($phrasearray[$i][2])) {
											$ismastersql = '';
											if (strtolower($result['author']) == 'sheel') {
												$ismastersql = "ismaster = '1',";
											}

											$this->sheel->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET " . $this->sheel->db->escape_string($value) . " = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',
											$ismastersql
											isupdated = '0'
											WHERE varname = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										} else {
											$this->notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrase group <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
										}
									}
								} else {
									if (!empty($phrasearray[$i][0]) and !empty($phrasearray[$i][1]) and !empty($phrasearray[$i][2])) {

										$updateoriginaltext = $ismastersql = '';

										if ($result['lang_code'] == 'english' and strtolower($result['author']) == 'sheel') {
											$updateoriginaltext = "text_original = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',";
											$ismastersql = "ismaster = '1',";
										}

										if ($overwritephrases == 0) {
											$this->sheel->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET phrasegroup = '" . $this->sheel->db->escape_string($phrasearray[$i][0]) . "',
											$updateoriginaltext
											$ismastersql
											isupdated = '0'
											WHERE varname = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										} else {
											$this->sheel->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET " . $this->sheel->db->escape_string($lfn) . " = '" . $this->sheel->db->escape_string($phrasearray[$i][2]) . "',
											$updateoriginaltext
											$ismastersql
											phrasegroup = '" . $this->sheel->db->escape_string($phrasearray[$i][0]) . "',
											isupdated = '0'
											WHERE varname = '" . $this->sheel->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										}
									} else {
										//$this->notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrase group <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
									}
								}
							}

							if ($slientmode == false) {
								$this->print_action_success('{_language_import_successful}', HTTPS_SERVER_ADMIN . 'settings/languages/');
								exit();
							} else {
								return true;
							}
						}
					} else {
						if ($slientmode == false) {
							$this->print_action_failed('{_were_sorry_there_was_an_error_with_the_formatting_of_the_language_file}', HTTPS_SERVER_ADMIN . 'settings/languages/');
							exit();
						} else {
							return false;
						}
					}
					break;
				}

			case 'configuration': {
					$data = array();
					$html = '<tr class="alt2"><td width="20%" wrap="wrap">{_varname}</td><td width="40%" wrap="wrap">{_old} {_value}</td><td width="40%" wrap="wrap">{_new} {_value}</td></tr>';
					$xml_encoding = 'UTF-8';
					$xml_encoding = mb_detect_encoding($xml);
					if ($xml_encoding == 'ASCII') {
						$xml_encoding = '';
					}
					$parser = xml_parser_create($xml_encoding);
					xml_parse_into_struct($parser, $xml, $data);
					$error_code = xml_get_error_code($parser);
					xml_parser_free($parser);
					if ($error_code == 0) {
						$result = $this->sheel->xml->process_configuration_xml($data, $xml_encoding);
						$sql = $this->sheel->db->query("SELECT * FROM " . DB_PREFIX . "configuration");
						while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
							$config[$res['name']] = $res['value'];
						}
						$i = 0;
						foreach ($result['main_configuration'] as $key => $value) {
							foreach ($value as $key2 => $value2) {
								if (isset($config[$key2]) and $config[$key2] != $value2) {
									$class = ' class="red"';
									$html .= '<input type="hidden" name="xml[' . $key2 . ']" value="' . htmlspecialchars($value2) . '" />';
									$i++;
								} else {
									$class = '';
								}
								if (!empty($class)) {
									$len1 = strlen($config[$key2]);
									$rows1 = ($len1 > 30) ? intval($len1 / 30) : 1;
									$len2 = strlen($value2);
									$rows2 = ($len2 > 30) ? intval($len2 / 30) : 1;
									$html .= '<tr class="alt1"><td width="20%" wrap="wrap"><div' . $class . '>' . $key2 . '</div></td><td width="40%" wrap="wrap"><textarea cols="30" rows="' . $rows1 . '">' . htmlspecialchars($config[$key2]) . '</textarea></td><td width="40%" wrap="wrap"><textarea cols="30" rows="' . $rows2 . '">' . $value2 . '</textarea></td></tr>';
								}
							}
						}
						return array($html, $i);
					} else {
						$error_string = xml_error_string($error_code);
						if ($slientmode == false) {
							$this->notice .= '{_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file}' . ' [' . $error_string . '].';
							$this->print_action_failed($this->notice);
							exit();
						} else {
							return false;
						}
					}
					break;
				}
		}
		return false;
	}

	private function import_skin($styleid = 0)
	{
		if ($styleid > 0) {

			return true;
		}
	}
	private function revert_skin_import($styleid = 0)
	{
		if ($styleid > 0) {
			return true;
		}
	}
	private function import_theme($themeinfo = array())
	{

		return true;
	}
	private function export_skin($styleid = 0)
	{
		$zipfile = '';
		if ($styleid > 0) {
			return $zipfile;
		}
	}
	private function export_theme($styleid = 0)
	{
		$zipfile = '';
		if ($styleid > 0) {
			return $zipfile;
		}
	}

	function export($what = '', $where = 'admincp', $languageid = 0, $pathtofile = '', $slientmode = false, $untranslated = 0, $styleid = 0, $product = '')
	{
		switch ($what) {
			case 'skin': {
					$zipfile = $this->export_skin($styleid);
					$this->sheel->common->download_file($zipfile, 'skin-' . $styleid . '.zip', 'application/zip');
					return true;
					break;
				}
			case 'theme': {
					$zipfile = $this->export_theme($styleid);
					$this->sheel->common->download_file($zipfile, 'theme-' . $styleid . '.zip', 'application/zip');
					return true;
					break;
				}
			case 'email': {
					if ($languageid <= 0) {
						die('No language id was specified.  Cannot continue.');
					}
					$query = $this->sheel->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
					WHERE languageid = '" . intval($languageid) . "'
					LIMIT 1
				");
					if ($this->sheel->db->num_rows($query) > 0) {
						$langconfig = $this->sheel->db->fetch_array($query, DB_ASSOC);
						header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
						$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>" . LINEBREAK;
						$xml_output .= "<!--
Simple rules for translators
1. For <settings> tag, edit all tags if applicable
2. For <email> tag, do not modify <varname>, <type>, <product> or <group> tags
3. If email is for buyers, <buyer>1</buyer>
4. If email is for sellers, <seller>1</seller>
5. If email is for staff, <admin>1</admin>
6. Editor should support UTF-8 like Atom for Mac or Komodo IDE for Windows
Team sheel
-->" . LINEBREAK;
						$xml_output .= "<language version=\"" . $this->sheel->config['version'] . "\">" . LINEBREAK;
						$xml_output .= "\t<settings>" . LINEBREAK;
						$xml_output .= "\t\t<author><![CDATA[" . stripslashes(SITE_NAME) . "]]></author>" . LINEBREAK;
						$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>" . LINEBREAK;
						$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>" . LINEBREAK;
						$xml_output .= "\t</settings>" . LINEBREAK;

						$query2 = $this->sheel->db->query("
						SELECT name_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS name, varname, type, subject_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS subject, message_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS message, messagehtml_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS messagehtml, product, `group`, admin
						FROM " . DB_PREFIX . "email
						ORDER BY `group` ASC, id ASC
					", 0, null, __FILE__, __LINE__);

						if ($this->sheel->db->num_rows($query2) > 0) {
							while ($phraseres = $this->sheel->db->fetch_array($query2, DB_ASSOC)) {
								$themessage = stripslashes($phraseres['message']);
								$themessage = str_replace("<br />", LINEBREAK, $themessage);
								$thehtmlmessage = trim(stripslashes($phraseres['messagehtml']));
								$thesubject = stripslashes($phraseres['subject']);
								$thename = stripslashes($phraseres['name']);
								$xml_output .= "
                                \t<email>
                                \t\t<varname>" . trim($phraseres['varname']) . "</varname>
                                \t\t<name><![CDATA[" . $thename . "]]></name>
                                \t\t<subject><![CDATA[" . $thesubject . "]]></subject>
                                \t\t<type><![CDATA[" . trim($phraseres['type']) . "]]></type>
                                \t\t<product><![CDATA[" . trim($phraseres['product']) . "]]></product>
                                \t\t<group><![CDATA[" . $this->sheel->db->escape_string($phraseres['group']) . "]]></group>
                                \t\t<buyer>" . intval($phraseres['buyer']) . "</buyer>
                                \t\t<seller>" . intval($phraseres['seller']) . "</seller>
                                \t\t<admin>" . intval($phraseres['admin']) . "</admin>
                                \t\t<messagehtml><![CDATA[" . ((!empty($thehtmlmessage)) ? $thehtmlmessage : '') . "]]></messagehtml>
                                \t\t<message><![CDATA[" . $themessage . "]]></message>
                                \t</email>" . LINEBREAK;
							}
						}
						$xml_output .= "</language>";

						$this->sheel->common->download_file($xml_output, 'emails-' . mb_strtolower($langconfig['languagecode']) . '.xml', 'text/plain');
						return true;
					}

					break;
				}
			case 'phrase': {
					if ($languageid <= 0) {
						die('No language id was specified.  Cannot continue.');
					}
					$query = $this->sheel->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
					WHERE languageid = '" . intval($languageid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
					if ($this->sheel->db->num_rows($query) > 0) {
						$langconfig = $this->sheel->db->fetch_array($query, DB_ASSOC);
						header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
						$replacements = $langconfig['replacements'];
						// language header configuration settings for this particular language
						$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>" . LINEBREAK;
						$xml_output .= "<!--
Simple rules for translators
1. For <settings> tags, you can edit all applicable
2. Do not modify anything for <phrasegroup> tags
3. For <phrase> tags, edit only content within <![CDATA[xxx]]>
4. Do not modify any varname=\"_xxx_xxx\"
5. Editor should support UTF-8 like Atom for Mac or Komodo IDE for Windows
Team sheel
-->" . LINEBREAK;
						$xml_output .= "<language version=\"" . $this->sheel->config['version'] . "\">" . LINEBREAK;
						$xml_output .= "\t<settings>" . LINEBREAK;
						$xml_output .= "\t\t<title>" . stripslashes($langconfig['title']) . "</title>" . LINEBREAK;
						$xml_output .= "\t\t<author>" . stripslashes(SITE_NAME) . "</author>" . LINEBREAK;
						$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>" . LINEBREAK;
						$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>" . LINEBREAK;
						$xml_output .= "\t\t<locale><![CDATA[" . stripslashes($langconfig['locale']) . "]]></locale>" . LINEBREAK;
						$xml_output .= "\t\t<languageiso><![CDATA[" . stripslashes($langconfig['languageiso']) . "]]></languageiso>" . LINEBREAK;
						$xml_output .= "\t\t<textdirection><![CDATA[" . stripslashes($langconfig['textdirection']) . "]]></textdirection>" . LINEBREAK;
						$xml_output .= "\t\t<canselect><![CDATA[" . intval($langconfig['canselect']) . "]]></canselect>" . LINEBREAK;
						$xml_output .= "\t\t<replacements><![CDATA[" . stripslashes($replacements) . "]]></replacements>" . LINEBREAK;
						$xml_output .= "\t</settings>" . LINEBREAK;
						$query2 = $this->sheel->db->query("
						SELECT groupname, description, product
						FROM " . DB_PREFIX . "language_phrasegroups
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($query2) > 0) {
							while ($groupres = $this->sheel->db->fetch_array($query2, DB_ASSOC)) {
								$xml_output .= "\t<phrasegroup name=\"" . stripslashes($groupres['groupname']) . "\" description=\"" . stripslashes($groupres['description']) . "\" product=\"" . stripslashes($groupres['product']) . "\">" . LINEBREAK;
								if ($untranslated) {
									// export only untranslated phrases
									$query3 = $this->sheel->db->query("
									SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
									FROM " . DB_PREFIX . "language_phrases
									WHERE phrasegroup = '" . $groupres['groupname'] . "'
										AND text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " = text_eng
									ORDER BY phraseid ASC
								", 0, null, __FILE__, __LINE__);
								} else {
									// export entire language phrases
									$query3 = $this->sheel->db->query("
									SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
									FROM " . DB_PREFIX . "language_phrases
									WHERE phrasegroup = '" . $groupres['groupname'] . "'
									ORDER BY phraseid ASC
								", 0, null, __FILE__, __LINE__);
								}
								if ($this->sheel->db->num_rows($query3) > 0) {
									$shortlang = mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3));

									while ($phraseres = $this->sheel->db->fetch_array($query3, DB_ASSOC)) {
										$xml_output .= "\t\t<phrase varname=\"" . stripslashes(trim($phraseres['varname'])) . "\">" . LINEBREAK . "\t\t\t<![CDATA[" . stripslashes($phraseres['text']) . "]]>" . LINEBREAK . "\t\t</phrase>" . LINEBREAK;
									}
								}
								$xml_output .= "\t</phrasegroup>" . LINEBREAK;
							}
						}
						$xml_output .= "</language>";
						$this->sheel->common->download_file($xml_output, 'phrases-' . $langconfig['languagecode'] . '.xml', 'text/plain');
						return true;
					}
					break;
				}

			case 'configuration': {
					header("Content-type: text/xml; charset=" . $this->sheel->config['template_charset']);
					$xml_output = "<?xml version=\"1.0\" encoding=\"" . $this->sheel->config['template_charset'] . "\"?>" . LINEBREAK;
					$xml_output .= "<configuration version=\"" . $this->sheel->config['version'] . "\" build=\"" . $this->sheel->buildversion . "\">" . LINEBREAK;
					$xml_output .= "\t<settings>" . LINEBREAK;
					$xml_output .= "\t\t<sitename>" . stripslashes($this->sheel->config['globalserversettings_sitename']) . "</sitename>" . LINEBREAK;
					$xml_output .= "\t\t<date>" . DATETIME24H . "</date>" . LINEBREAK;
					$xml_output .= "\t</settings>" . LINEBREAK . LINEBREAK;
					$xml_output .= "\t<main_configuration>" . LINEBREAK;
					$sql_main_conf_group = $this->sheel->db->query("
					SELECT parentgroupname, groupname, sort, type
					FROM " . DB_PREFIX . "configuration_groups
				", 0, null, __FILE__, __LINE__);
					if ($this->sheel->db->num_rows($sql_main_conf_group) > 0) {
						while ($res_main_conf_group = $this->sheel->db->fetch_array($sql_main_conf_group, DB_ASSOC)) {
							$xml_output .= "\t\t<configuration_group parentgroupname=\"" . stripslashes(trim($res_main_conf_group['parentgroupname'])) . "\" groupname=\"" . stripslashes(trim($res_main_conf_group['groupname'])) . "\" sort=\"" . intval(trim($res_main_conf_group['sort'])) . "\" type=\"" . stripslashes(trim($res_main_conf_group['type'])) . "\">" . LINEBREAK;
							$sql_main_conf = $this->sheel->db->query("
							SELECT name, value, configgroup, inputtype, inputcode, inputname, sort, visible, type
							FROM " . DB_PREFIX . "configuration
							WHERE configgroup = '" . $res_main_conf_group['groupname'] . "'
						", 0, null, __FILE__, __LINE__);
							if ($this->sheel->db->num_rows($sql_main_conf) > 0) {
								while ($res_main_conf = $this->sheel->db->fetch_array($sql_main_conf, DB_ASSOC)) {
									$xml_output .= "\t\t\t<option name=\"" . stripslashes(o(trim($res_main_conf['name']))) . "\" configgroup=\"" . stripslashes(o(trim($res_main_conf['configgroup']))) . "\" inputtype=\"" . stripslashes(o(trim($res_main_conf['inputtype']))) . "\" inputcode=\"" . stripslashes(o(trim($res_main_conf['inputcode']))) . "\" inputname=\"" . stripslashes(o(trim($res_main_conf['inputname']))) . "\" sort=\"" . intval(trim($res_main_conf['sort'])) . "\" visible=\"" . intval(trim($res_main_conf['visible'])) . "\" inputtype=\"" . stripslashes(o(trim($res_main_conf['type']))) . "\">" . LINEBREAK . "\t\t\t\t<![CDATA[" . stripslashes($res_main_conf['value']) . "]]>" . LINEBREAK . "\t\t\t</option>" . LINEBREAK;
								}
							}
							$xml_output .= "\t\t</configuration_group>" . LINEBREAK . LINEBREAK;
						}
						unset($res_main_conf_group);
					}
					unset($sql_main_conf_group);
					$xml_output .= "\t</main_configuration>" . LINEBREAK . LINEBREAK;
					if (false) {
						$sql_deposit_methods = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "deposit_offline_methods
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($sql_deposit_methods) > 0) {
							$xml_output .= "\t<deposit_methods>" . LINEBREAK;
							while ($res_deposit_methods = $this->sheel->db->fetch_array($sql_deposit_methods, DB_ASSOC)) {
								$xml_output .= "\t\t<method id=\"" . stripslashes(trim($res_deposit_methods['id'])) . "\" name=\"" . stripslashes(trim($res_deposit_methods['name'])) . "\" number=\"" . stripslashes(trim($res_deposit_methods['number'])) . "\" swift=\"" . stripslashes(trim($res_deposit_methods['swift'])) . "\" company_name=\"" . stripslashes(trim($res_deposit_methods['company_name'])) . "\" id=\"" . stripslashes(trim($res_deposit_methods['id'])) . "\" company_address=\"" . stripslashes(trim($res_deposit_methods['company_address'])) . "\" custom_notes=\"" . stripslashes(trim($res_deposit_methods['custom_notes'])) . "\" visible=\"" . stripslashes(trim($res_deposit_methods['visible'])) . "\"sort=\"" . stripslashes(trim($res_deposit_methods['sort'])) . "\"></method>" . LINEBREAK;
							}
							$xml_output .= "\t</deposit_methods>" . LINEBREAK . LINEBREAK;
						}
					}
					if (false) {
						$sql_payment_groups = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "payment_groups
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($sql_payment_groups) > 0) {
							$xml_output .= "\t<payment_configuration>" . LINEBREAK;
							while ($res_payment_groups = $this->sheel->db->fetch_array($sql_payment_groups, DB_ASSOC)) {
								$xml_output .= "\t\t<payment_group groupname=\"" . stripslashes(trim($res_payment_groups['groupname'])) . "\">" . LINEBREAK;
								$sql_payment_configuration = $this->sheel->db->query("
								SELECT *
								FROM " . DB_PREFIX . "payment_configuration
								WHERE configgroup = '" . $res_payment_groups['groupname'] . "'
							", 0, null, __FILE__, __LINE__);
								if ($this->sheel->db->num_rows($sql_payment_configuration) > 0) {
									while ($res_payment_configuration = $this->sheel->db->fetch_array($sql_payment_configuration, DB_ASSOC)) {
										$xml_output .= "\t\t\t<option id=\"" . stripslashes(trim($res_payment_configuration['id'])) . "\" name=\"" . stripslashes(trim($res_payment_configuration['name'])) . "\" value=\"" . stripslashes(trim($res_payment_configuration['value'])) . "\" configgroup=\"" . stripslashes(trim($res_payment_configuration['configgroup'])) . "\" inputname=\"" . stripslashes(trim($res_payment_configuration['inputname'])) . "\" ></option>" . LINEBREAK;
									}
								}
								$xml_output .= "\t\t</payment_group>" . LINEBREAK;
							}
							$xml_output .= "\t</payment_configuration>" . LINEBREAK . LINEBREAK;
						}
					}
					if (false) {
						$sql_payment_methods = $this->sheel->db->query("
						SELECT *
						FROM " . DB_PREFIX . "payment_methods
					", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($sql_payment_methods) > 0) {
							$xml_output .= "\t<payment_methods>" . LINEBREAK;
							while ($res_payment_methods = $this->sheel->db->fetch_array($sql_payment_methods, DB_ASSOC)) {
								$xml_output .= "\t\t<method id=\"" . stripslashes(trim($res_payment_methods['id'])) . "\" title=\"" . stripslashes(trim($res_payment_methods['title'])) . "\" sort=\"" . stripslashes(trim($res_payment_methods['sort'])) . "\"></option>" . LINEBREAK;
							}
							$xml_output .= "\t</payment_methods>" . LINEBREAK . LINEBREAK;
						}
					}
					$xml_output .= "</configuration>";
					$this->sheel->common->download_file($xml_output, 'configuration-' . $this->sheel->config['globalserversettings_sitename'] . '.xml', 'text/plain');
					break;
				}
		}
		return false;
	}
	function minify($string = '')
	{
		if (!empty($string)) {
			$string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
			$string = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $string);
		}
		return $string;
	}
}
?>