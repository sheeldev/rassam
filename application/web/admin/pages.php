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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Pages</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Pages';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $slng = (isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
    $sheel->template->meta['jsinclude']['header'][] = 'vendor/friendurl';
    $sheel->template->meta['jsinclude']['footer'][] = 'admin_pages';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    $parentcats = array();
    $locations = array('header' => 'Header Navigation', 'footer' => 'Footer Navigation', 'both' => 'Header &amp; Footer Navigation', 'none' => '{_none}');
    $areanav = 'settings_pages';
    $currentarea = 'Pages Manager';

    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        $sheel->template->meta['areatitle'] = 'Admin CP | Pages - Add';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Pages - Add';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
            if (isset($sheel->GPC['description_html'])) {
                $sheel->GPC['form']['description_html'] = $sheel->GPC['description_html'];
                unset($sheel->GPC['description_html']);
            }
            $sheel->GPC['form']['subtitle'] = ((isset($sheel->GPC['form']['subtitle'])) ? $sheel->GPC['form']['subtitle'] : '');
            $sheel->GPC['form']['membersonly'] = (isset($sheel->GPC['form']['membersonly']) ? $sheel->GPC['form']['membersonly'] : 0);
            $sheel->GPC['form']['ispublished'] = (isset($sheel->GPC['form']['ispublished']) ? $sheel->GPC['form']['ispublished'] : 0);
            $sheel->GPC['form']['isterms'] = (isset($sheel->GPC['form']['isterms']) ? $sheel->GPC['form']['isterms'] : 0);
            $sheel->GPC['form']['isprivacy'] = (isset($sheel->GPC['form']['isprivacy']) ? $sheel->GPC['form']['isprivacy'] : 0);
            $sheel->GPC['form']['iscookies'] = (isset($sheel->GPC['form']['iscookies']) ? $sheel->GPC['form']['iscookies'] : 0);
            $sheel->GPC['form']['showdate'] = (isset($sheel->GPC['form']['showdate']) ? $sheel->GPC['form']['showdate'] : 0);
            $sheel->GPC['form']['sitemap'] = (isset($sheel->GPC['form']['sitemap']) ? $sheel->GPC['form']['sitemap'] : 0);
            $sheel->GPC['form']['sidebar'] = (isset($sheel->GPC['form']['sidebar']) ? $sheel->GPC['form']['sidebar'] : 0);
            $sheel->GPC['form']['groupname'] = ((isset($sheel->GPC['form']['groupname'])) ? $sheel->GPC['form']['groupname'] : '');
            $sheel->GPC['form']['location'] = ((isset($sheel->GPC['form']['location'])) ? $sheel->GPC['form']['location'] : '');
            $sheel->GPC['form']['parentid'] = (isset($sheel->GPC['form']['parentid']) ? intval($sheel->GPC['form']['parentid']) : 0);
            $sheel->db->query("
					INSERT INTO " . DB_PREFIX . "content
					(id, parentid, title, subtitle, description, description_html, groupname, location, seourl, ispublished, isterms, isprivacy, iscookies, showdate, sitemap, sidebar, membersonly, keywords, sort, date, publishdate, userid, lastupdateuserid)
					VALUES(
					NULL,
					'" . intval($sheel->GPC['form']['parentid']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['title']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['subtitle']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['description_html']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['groupname']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['location']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['seourl']) . "',
					'" . intval($sheel->GPC['form']['ispublished']) . "',
					'" . intval($sheel->GPC['form']['isterms']) . "',
					'" . intval($sheel->GPC['form']['isprivacy']) . "',
					'" . intval($sheel->GPC['form']['iscookies']) . "',
					'" . intval($sheel->GPC['form']['showdate']) . "',
					'" . intval($sheel->GPC['form']['sitemap']) . "',
					'" . intval($sheel->GPC['form']['sidebar']) . "',
					'" . intval($sheel->GPC['form']['membersonly']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['form']['keywords']) . "',
					'" . intval($sheel->GPC['form']['sort']) . "',
					'" . DATETIME24H . "',
					'" . DATETIME24H . "',
					'" . $_SESSION['sheeldata']['user']['userid'] . "',
					'" . $_SESSION['sheeldata']['user']['userid'] . "'
					)
				");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Content page added', 'A content page has been successfully added.');
            refresh(HTTPS_SERVER_ADMIN . 'settings/pages/');
            exit();
        }
        $form['title'] = '';
        $form['subtitle'] = '';
        $form['seourl'] = '';
        $form['keywords'] = '';
        $form['sort'] = 10;
        $form['membersonly'] = '';
        $form['ispublished'] = '';
        $form['isterms'] = '';
        $form['isprivacy'] = '';
        $form['iscookies'] = '';
        $form['showdate'] = 'checked="checked"';
        $form['sitemap'] = '';
        $form['sidebar'] = 'checked="checked"';
        $form['description'] = '';
        $form['description_html'] = $sheel->common->print_wysiwyg_editor('description_html', '', 'textarea', $sheel->config['globalfilters_enablewysiwyg'], $sheel->config['globalfilters_enablewysiwyg'], false, '100%', '350', '', $sheel->config['default_wysiwyg'], $sheel->config['wysiwyg_toolbar']);
        $form['groupname'] = '';
        $form['location'] = $sheel->construct_pulldown('form_location', 'form[location]', $locations, '', 'class="draw-select"');
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process' and isset($sheel->GPC['id'])) {
            if (isset($sheel->GPC['description_html'])) {
                $sheel->GPC['form']['description_html'] = $sheel->GPC['description_html'];
                unset($sheel->GPC['description_html']);
            }
            $sheel->GPC['form']['subtitle'] = ((isset($sheel->GPC['form']['subtitle'])) ? $sheel->GPC['form']['subtitle'] : '');
            $sheel->GPC['form']['membersonly'] = (isset($sheel->GPC['form']['membersonly']) ? $sheel->GPC['form']['membersonly'] : 0);
            $sheel->GPC['form']['ispublished'] = (isset($sheel->GPC['form']['ispublished']) ? $sheel->GPC['form']['ispublished'] : 0);
            $sheel->GPC['form']['isterms'] = (isset($sheel->GPC['form']['isterms']) ? $sheel->GPC['form']['isterms'] : 0);
            $sheel->GPC['form']['isprivacy'] = (isset($sheel->GPC['form']['isprivacy']) ? $sheel->GPC['form']['isprivacy'] : 0);
            $sheel->GPC['form']['iscookies'] = (isset($sheel->GPC['form']['iscookies']) ? $sheel->GPC['form']['iscookies'] : 0);
            $sheel->GPC['form']['showdate'] = (isset($sheel->GPC['form']['showdate']) ? $sheel->GPC['form']['showdate'] : 0);
            $sheel->GPC['form']['sitemap'] = (isset($sheel->GPC['form']['sitemap']) ? $sheel->GPC['form']['sitemap'] : 0);
            $sheel->GPC['form']['sidebar'] = (isset($sheel->GPC['form']['sidebar']) ? $sheel->GPC['form']['sidebar'] : 0);
            $sheel->GPC['form']['groupname'] = ((isset($sheel->GPC['form']['groupname'])) ? $sheel->GPC['form']['groupname'] : '');
            $sheel->GPC['form']['location'] = ((isset($sheel->GPC['form']['location'])) ? $sheel->GPC['form']['location'] : '');
            $sheel->GPC['form']['parentid'] = (isset($sheel->GPC['form']['parentid']) ? intval($sheel->GPC['form']['parentid']) : 0);
            if ($sheel->GPC['id'] == $sheel->GPC['form']['parentid']) { // parentid cannot be the same as this id!
                $sheel->GPC['form']['parentid'] = 0;
            }
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "content
					SET title = '" . $sheel->db->escape_string($sheel->GPC['form']['title']) . "',
					subtitle = '" . $sheel->db->escape_string($sheel->GPC['form']['subtitle']) . "',
					description = '" . $sheel->db->escape_string($sheel->GPC['form']['description']) . "',
					description_html = '" . $sheel->db->escape_string($sheel->GPC['form']['description_html']) . "',
					seourl = '" . $sheel->db->escape_string($sheel->GPC['form']['seourl']) . "',
					keywords = '" . $sheel->db->escape_string($sheel->GPC['form']['keywords']) . "',
					sort = '" . $sheel->db->escape_string($sheel->GPC['form']['sort']) . "',
					membersonly = '" . $sheel->db->escape_string($sheel->GPC['form']['membersonly']) . "',
					ispublished = '" . $sheel->db->escape_string($sheel->GPC['form']['ispublished']) . "',
					isterms = '" . intval($sheel->GPC['form']['isterms']) . "',
					isprivacy = '" . intval($sheel->GPC['form']['isprivacy']) . "',
					iscookies = '" . intval($sheel->GPC['form']['iscookies']) . "',
					showdate = '" . intval($sheel->GPC['form']['showdate']) . "',
					sitemap = '" . intval($sheel->GPC['form']['sitemap']) . "',
					sidebar = '" . intval($sheel->GPC['form']['sidebar']) . "',
					lastupdate = '" . DATETIME24H . "',
					lastupdateuserid = '" . $_SESSION['sheeldata']['user']['userid'] . "',
					location = '" . $sheel->db->escape_string($sheel->GPC['form']['location']) . "',
					parentid = '" . intval($sheel->GPC['form']['parentid']) . "',
					groupname = '" . $sheel->db->escape_string($sheel->GPC['form']['groupname']) . "'
					WHERE id = '" . intval($sheel->GPC['id']) . "'
				");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Content page updated', 'A content page has been successfully updated.');
            refresh(HTTPS_SERVER_ADMIN . 'settings/pages/update/' . $sheel->GPC['form']['seourl'] . '/');
            exit();
        }
        $pagesql = $sheel->db->query("
				SELECT id, parentid, title, subtitle, description, description_html, seourl, ispublished, isterms, isprivacy, iscookies, membersonly, keywords, sort, groupname, location, showdate, sitemap, sidebar
				FROM " . DB_PREFIX . "content
				WHERE visible = '1'
					AND seourl = '" . $sheel->db->escape_string($sheel->GPC['seourl']) . "'
				LIMIT 1
			");
        if ($sheel->db->num_rows($pagesql) > 0) {
            while ($res = $sheel->db->fetch_array($pagesql, DB_ASSOC)) {
                $form['id'] = $res['id'];
                $form['title'] = o($res['title']);
                $form['subtitle'] = o($res['subtitle']);
                $form['seourl'] = o($res['seourl']);
                $form['keywords'] = o($res['keywords']);
                $form['sort'] = intval($res['sort']);
                $form['membersonly'] = (($res['membersonly']) ? 'checked="checked"' : '');
                $form['ispublished'] = (($res['ispublished']) ? 'checked="checked"' : '');
                $form['isterms'] = (($res['isterms']) ? 'checked="checked"' : '');
                $form['isprivacy'] = (($res['isprivacy']) ? 'checked="checked"' : '');
                $form['iscookies'] = (($res['iscookies']) ? 'checked="checked"' : '');
                $form['showdate'] = (($res['showdate']) ? 'checked="checked"' : '');
                $form['sitemap'] = (($res['sitemap']) ? 'checked="checked"' : '');
                $form['sidebar'] = (($res['sidebar']) ? 'checked="checked"' : '');
                $form['description'] = o($res['description']);
                $form['groupname'] = o($res['groupname']);
                $form['location'] = $sheel->construct_pulldown('form_location', 'form[location]', $locations, $res['location'], 'class="draw-select"');
                $form['description_html'] = $sheel->common->print_wysiwyg_editor('description_html', $res['description_html'], 'textarea', $sheel->config['globalfilters_enablewysiwyg'], $sheel->config['globalfilters_enablewysiwyg'], false, '100%', '350', '', $sheel->config['default_wysiwyg'], $sheel->config['wysiwyg_toolbar']);
            }
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
        if (isset($sheel->GPC['xid']) and !empty($sheel->GPC['xid'])) {
            $id = $sheel->db->fetch_field(DB_PREFIX . "content", "seourl = '" . $sheel->db->escape_string($sheel->GPC['xid']) . "'", "id");
            $sheel->db->query("
					DELETE FROM " . DB_PREFIX . "content
					WHERE seourl = '" . $sheel->db->escape_string($sheel->GPC['xid']) . "'
					LIMIT 1
				");
            // if a parent is removed, make all children orphaned
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "content
					SET parentid = '0'
					WHERE parentid = '" . intval($id) . "'
				");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Content page deleted', 'A content page has been successfully deleted.');
            die(json_encode(array('response' => '1', 'message' => 'A content page has been successfully deleted.')));
        } else {
            $sheel->template->templateregistry['message'] = 'This page could not be deleted.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    $pages = array();
    $pagesql = $sheel->db->query("
			SELECT id, parentid, title, subtitle, description, description_html, seourl, candelete, ispublished, isterms, membersonly, groupname
			FROM " . DB_PREFIX . "content
			WHERE visible = '1'
			ORDER BY sort ASC
		");
    if ($sheel->db->num_rows($pagesql) > 0) {
        while ($res = $sheel->db->fetch_array($pagesql, DB_ASSOC)) {
            $sheel->show['candelete_' . $res['id']] = $res['candelete'];
            $sheel->show['membersonly_' . $res['id']] = $res['membersonly'];
            $sheel->show['ispublished_' . $res['id']] = $res['ispublished'];
            $sheel->show['isterms_' . $res['id']] = $res['isterms'];
            if ($res['parentid'] > 0) {
                $sheel->show['isparent_' . $res['id']] = false;
            } else {
                $sheel->show['isparent_' . $res['id']] = true;
            }
            $pages[] = $res;
        }
    }
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    
    $sheel->template->fetch('main', 'settings_pages.html', 1);
    $sheel->template->parse_hash('main', array(
        'form' => (isset($form) ? $form : array())
    )
    );
    $sheel->template->parse_loop('main', array('pages' => $pages));
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>