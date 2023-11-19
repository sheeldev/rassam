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
        'vendor/growl'
    ),
    'footer' => array(
    )
);
$sheel->template->meta['cssinclude'] = array(
    'common',
    'vendor' => array(
        'growl',
        'font-awesome',
        'glyphicons',
        'chartist',
        'balloon',
        'growl'
    )
);
$sheel->template->meta['areatitle'] = 'Admin CP | {_email_template_manager}';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | {_email_template_manager}';
if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }

    $areanav = 'settings_emails';
    $currentarea = 'Email Templates Manager';
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
            refresh(HTTPS_SERVER_ADMIN . 'settings/emails/');
            exit();
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'save') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
            $field = '';
            $sql_lang = $sheel->db->query("
					SELECT languagecode, title
					FROM " . DB_PREFIX . "language
				");
            while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                if (!isset($sheel->GPC['form']['name'][$languagecode]) or empty($sheel->GPC['form']['name'][$languagecode])) {
                    $sheel->GPC['form']['name'][$languagecode] = '';
                }
                if (!isset($sheel->GPC['form']['subject'][$languagecode]) or empty($sheel->GPC['form']['subject'][$languagecode])) {
                    $sheel->GPC['form']['subject'][$languagecode] = '';
                }
                if (!isset($sheel->GPC['form']['body'][$languagecode]) or empty($sheel->GPC['form']['body'][$languagecode])) {
                    $sheel->GPC['form']['body'][$languagecode] = '';
                }
                if (!isset($sheel->GPC['form']['body_html'][$languagecode]) or empty($sheel->GPC['form']['body_html'][$languagecode])) {
                    $sheel->GPC['form']['body_html'][$languagecode] = '';
                }
                $field .= "
					name_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['name'][$languagecode]) . "',
					subject_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['subject'][$languagecode]) . "',
					message_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['body'][$languagecode]) . "',
					messagehtml_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['body_html'][$languagecode]) . "',
					";
            }
            $who = 'everyone';
            $buyer = $seller = $admin = 1;
            $sheel->GPC['form']['bcc'] = ((isset($sheel->GPC['form']['bcc']) and !empty($sheel->GPC['form']['bcc'])) ? $sheel->GPC['form']['bcc'] : '');
            if (isset($sheel->GPC['form']['who']) and !empty($sheel->GPC['form']['who'])) {
                $who = $sheel->GPC['form']['who'];
            }
            if ($who == 'everyone') {
                $admin = 0;
            } else if ($who == 'staff') {
                $admin = 1;
            }
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "email
					SET $field
					admin = '" . intval($admin) . "',
					bcc = '" . $sheel->db->escape_string($sheel->GPC['form']['bcc']) . "'
					WHERE varname = '" . $sheel->db->escape_string($sheel->GPC['varname']) . "'
					LIMIT 1
				");
            refresh(HTTPS_SERVER_ADMIN . 'settings/emails/update/' . $sheel->GPC['varname'] . '/');
            exit();
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update' and isset($sheel->GPC['varname']) and !empty($sheel->GPC['varname'])) {
        $varname = $sheel->GPC['varname'];
        $emailobjects = $objects1 = $objects2 = '';
        $sql = $sheel->db->query("
				SELECT *
				FROM " . DB_PREFIX . "email
				WHERE varname = '" . $sheel->db->escape_string($sheel->GPC['varname']) . "'
				LIMIT 1
			");
        if ($sheel->db->num_rows($sql) > 0) {
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $sqlx = $sheel->db->query("
						SELECT languagecode, title, textdirection
						FROM " . DB_PREFIX . "language
					");
                while ($resx = $sheel->db->fetch_array($sqlx, DB_ASSOC)) {
                    $languagecode = strtolower(substr($resx['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($resx['languagecode'], 0, 2));
                    $language['language'] = $resx['title'];
                    $language['slng'] = $languagecode;
                    $language['rslng'] = $rlanguagecode;
                    $form['name_' . $language['slng']] = ((!empty($res['name_' . $language['slng']])) ? $res['name_' . $language['slng']] : '');
                    $form['subject_' . $language['slng']] = ((!empty($res['subject_' . $language['slng']])) ? $res['subject_' . $language['slng']] : '');
                    $form['body_' . $language['slng']] = ((!empty($res['message_' . $language['slng']])) ? $res['message_' . $language['slng']] : '');
                    $form['bodyhtml_' . $language['slng']] = ((!empty($res['messagehtml_' . $language['slng']])) ? $res['messagehtml_' . $language['slng']] : '');
                    $form['flag_' . $language['slng']] = $language['rslng'];
                    $form['textdirection_' . $language['slng']] = $resx['textdirection'];
                    if (preg_match_all("!\{\{[a-z0-9_]+\}\}!", $form['body_' . $language['slng']], $matches)) {
                        foreach ($matches[0] as $key => $value) {
                            $matchesx[0][$key] = '<li><code>' . $value . '</code></li>';
                        }
                    }
                    if (preg_match_all("!\{\{[a-z0-9_]+\}\}!", $form['bodyhtml_' . $language['slng']], $matches)) {
                        foreach ($matches[0] as $key => $value) {
                            $matchesy[0][$key] = '<li><code>' . $value . '</code></li>';
                        }
                    }
                    $languages[] = $language;
                }
                $currentarea = $form['name_' . $_SESSION['sheeldata']['user']['slng']];
                $form['rbeveryone'] = $res['admin'] == '0' ? ' checked="checked"' : '';
                $form['rbstaff'] = $res['admin'] == '1' ? ' checked="checked"' : '';
                $form['bcc'] = o($res['bcc']);
            }
            if (!empty($matchesx)) {
                $objects1 = implode(LINEBREAK, array_unique($matchesx[0]));
                $emailobjects = '<div style="padding-top:4px;padding-bottom:4px;font-weight:bold">Plain-text version:</div>' . $objects1;
            }
            if (!empty($matchesy)) {
                $objects2 .= implode(LINEBREAK, array_unique($matchesy[0]));
                $emailobjects .= '<div style="padding-top:4px;padding-bottom:4px;font-weight:bold">HTML version:</div>' . $objects2;
            }
        }
        $sheel->template->fetch('main', 'settings_emails_edit.html', 1);
        $sheel->template->parse_loop('main', array('languages' => $languages));
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $vars['sidenav'] = $sidenav;
        $vars['emailobjects'] = (isset($emailobjects) ? $emailobjects : '');
        $vars['varname'] = (isset($varname) ? $varname : '');
        $sheel->template->parse_hash('main', array(
            'form' => (isset($form) ? $form : array())
        )
        );
        $sheel->template->pprint('main', $vars);

    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'export') {
        $sheel->admincp_importexport->export('email', 'admincp', $sheel->GPC['languageid'], '', false, 0);
        exit();
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'import') {
        if (empty($_FILES['xml_file']['tmp_name'])) {
            refresh(HTTPS_SERVER_ADMIN . 'settings/emails/');
            exit();
        }
        $xml = file_get_contents($_FILES['xml_file']['tmp_name']);
        $noversioncheck = isset($sheel->GPC['noversioncheck']) ? intval($sheel->GPC['noversioncheck']) : 0;
        $overwritephrases = isset($sheel->GPC['overwrite']) ? intval($sheel->GPC['overwrite']) : 0;
        $compress = isset($sheel->GPC['compress']) ? intval($sheel->GPC['compress']) : 0;
        $sheel->admincp_importexport->import('email', 'admincp', $xml, false, $noversioncheck, $overwritephrases, 0, $compress);
        exit();
    } else {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'findorphans') {
            $sheel->show['results'] = true;
            $orphan_phrases_text_area = $sheel->admincp_find_orphan->find_emailtemplate(substr(SITE_ROOT, 0, -1));
            if ($sheel->admincp_find_orphan->orphanphrases > 0) {
                $sheel->show['can_delete'] = true;
            } else {
                $sheel->show['no_results'] = true;
            }
        }

        $language_pulldown = $sheel->language->print_language_pulldown('', '', '', '', 'draw-select');
        $groups = $r = array();
        $form['totalgroups'] = 0;
        $form['totalemails'] = 0;
        $groupx = $sheel->db->query("
				SELECT `group`
				FROM " . DB_PREFIX . "email
				GROUP BY `group`
				ORDER BY `group` ASC
			");

        if ($sheel->db->num_rows($groupx) > 0) {
            while ($row = $sheel->db->fetch_array($groupx, DB_ASSOC)) {
                $form['totalgroups']++;
                $sql = $sheel->db->query("
						SELECT id, varname, name_" . $_SESSION['sheeldata']['user']['slng'] . " AS name, message_" . $_SESSION['sheeldata']['user']['slng'] . " AS body, messagehtml_" . $_SESSION['sheeldata']['user']['slng'] . " AS bodyhtml, subject_" . $_SESSION['sheeldata']['user']['slng'] . " AS subject, product, `group`, admin, type, bcc
						FROM " . DB_PREFIX . "email
						WHERE `group` = '" . $row['group'] . "'
						ORDER BY id ASC
					");
                $sql2 = $sheel->db->query("
						SELECT id
						FROM " . DB_PREFIX . "email
						WHERE `group` = '" . $row['group'] . "'
					");
                if ($sheel->db->num_rows($sql) > 0) {
                    $number = $sheel->db->num_rows($sql2);
                    while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                        $form['totalemails']++;
                        if (($res['buyer'] == 0 and $res['seller'] == 0 and $res['admin'] == 0) or ($res['buyer'] and $res['seller'] and $res['admin']) or ($res['buyer'] and $res['seller'])) {
                            $res['type'] = '<span class="badge badge--subdued">{_everyone}</span>';
                        } else if ($res['buyer'] and $res['seller'] == 0 and $res['admin'] == 0) {
                            $res['type'] = '<span class="badge badge--info">{_buyers}</span>';
                        } else if ($res['seller'] and $res['buyer'] == 0 and $res['admin'] == 0) {
                            $res['type'] = '<span class="badge badge--success">{_sellers}</span>';
                        } else if ($res['admin'] and $res['buyer'] == 0 and $res['seller'] == 0) {
                            $res['type'] = '<span class="badge badge--attention">{_staff}</span>';
                        }
                        $res['name'] = o($res['name']);
                        $res['plaintext'] = ((!empty($res['body'])) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />');
                        $res['html'] = ((!empty($res['bodyhtml'])) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />');
                        $res['bccc'] = ((!empty($res['bcc'])) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark_grey.png" border="0" alt="" />');
                        $r['emails' . $res['group']][] = $res;
                    }
                }
                $row['title'] = '{_' . $row['group'] . '_emailgroup_desc}';
                $row['help'] = '{_' . $row['group'] . '_emailgroup_help}';
                $groups[] = $row;
            }
        }

        $sheel->template->fetch('main', 'settings_emails.html', 1);
        $sheel->template->parse_loop('main', array('groups' => $groups));
        if (isset($groups)) {
            foreach ($groups as $key => $value) {
                $sheel->template->parse_loop('main', array(
                    'emails' . $value['group'] => (isset($r['emails' . $value['group']]) ? $r['emails' . $value['group']] : array()),
                ), true);
            }
        }
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $vars['sidenav'] = $sidenav;
        $vars['language_pulldown'] = (isset($language_pulldown) ? $language_pulldown : '');
        $sheel->template->parse_hash('main', array(
            'form' => (isset($form) ? $form : array())
        )
        );
        $sheel->template->pprint('main', $vars);
    }

} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>