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

$sheel->template->meta['areatitle'] = 'Admin CP | Announcements';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Announcements';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {

    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }

    $areanav = 'settings_announcements';
    $currentarea = 'Announcements';
    $sheel->template->meta['jsinclude']['header'][] = 'vendor/friendurl';
    $sheel->template->meta['jsinclude']['footer'][] = 'admin_announcements';
    $settings = $sheel->admincp->construct_admin_input('announcements', HTTPS_SERVER_ADMIN . 'settings/announcements/');
    if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'add')
    {
        $sheel->template->meta['areatitle'] = 'Admin CP | Announcements - Add';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Announcements - Add';
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
        {
            $ext = mb_substr($_FILES['imagename']['name'], strpos($_FILES['imagename']['name'], '.'), strlen($_FILES['imagename']['name']) - 1);
            if (in_array($ext, array('.png', '.jpg', '.jpeg', '.gif')) AND filesize($_FILES['imagename']['tmp_name']) <= 250000)
            {
                if (file_exists(DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                {
                    if (!unlink(DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                    {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old announcement image file', 'Old announcement image file could not be removed (check permission)');
                    }
                }
                if (!move_uploaded_file($_FILES['imagename']['tmp_name'], DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error saving announcement image file', 'Announcement image file could not be uploaded (check folder permission)');
                }
                else
                {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'Added announcement image file', 'A new announcement image file was saved ');
                }
            }
            else
            {
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading announcement image file', 'The announcement image file must be .png, .jpg, .jpeg or .gif and under 250kb in file size.');
            }
            $sheel->GPC['form']['content'] = ((isset($sheel->GPC['form']['content'])) ? $sheel->GPC['form']['content'] : '');
            $sheel->GPC['form']['imagename'] = ((isset($_FILES['imagename']['name'])) ? $_FILES['imagename']['name'] : '');
            $sheel->GPC['form']['date'] = ((isset($sheel->GPC['form']['date'])) ? $sheel->GPC['form']['date'] : '');
            $sheel->GPC['form']['visible'] = ((isset($sheel->GPC['form']['visible'])) ? $sheel->GPC['form']['visible'] : '1');
            $sheel->db->query("
                INSERT INTO " . DB_PREFIX . "announcements
                (announcementid, content, date, imagename, visible)
                VALUES
                (NULL,
                '" . $sheel->db->escape_string($sheel->GPC['form']['content']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['date']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['imagename']) . "',
                '" . intval($sheel->GPC['form']['visible']) . "')
            ");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'New announcement added', 'A new announcement was added to the portal');
            refresh(HTTPS_SERVER_ADMIN . 'settings/announcements/');
            exit();
        }
        $form['content'] = '';
        $form['date'] = '';
        $form['visible_1'] = 'checked="checked"';
        $form['visible_0'] = '';
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'update')
    {
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process' AND isset($sheel->GPC['announcementid']))
        {
            $sheel->GPC['form']['content'] = ((isset($sheel->GPC['form']['content'])) ? $sheel->GPC['form']['content'] : '');
            $sheel->GPC['form']['date'] = ((isset($sheel->GPC['form']['date'])) ? $sheel->GPC['form']['date'] : '');
            $sheel->GPC['form']['visible'] = ((isset($sheel->GPC['form']['visible'])) ? $sheel->GPC['form']['visible'] : '1');
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "announcements
                SET content = '" . $sheel->db->escape_string($sheel->GPC['form']['content']) . "',
                date = '" . $sheel->db->escape_string($sheel->GPC['form']['date']) . "',
                visible = '" . intval($sheel->GPC['form']['visible']) . "'
                WHERE announcementid = '" . intval($sheel->GPC['announcementid']) . "'
                LIMIT 1
            ");
            if (isset($_FILES['imagename']['name']) AND !empty($_FILES['imagename']['name']))
            {
                $ext = mb_substr($_FILES['imagename']['name'], strpos($_FILES['imagename']['name'], '.'), strlen($_FILES['imagename']['name']) - 1);
                if (in_array($ext, array('.png', '.jpg', '.jpeg', '.gif')) AND filesize($_FILES['imagename']['tmp_name']) <= 250000)
                {
                    if (isset($sheel->GPC['form']['imagename_old']) AND !empty($sheel->GPC['form']['imagename_old']) AND file_exists(DIR_ATTACHMENTS . 'announcements/' . $sheel->GPC['form']['imagename_old']))
                    { // delete old file
                        if (!unlink(DIR_ATTACHMENTS . 'announcements/' . $sheel->GPC['form']['imagename_old']))
                        {
                            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old announcement image file', 'Old announcement image file [' . $sheel->GPC['form']['imagename_old'] . '] could not be removed (check permission)');
                        }
                    }
                    if (file_exists(DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                    { // check if new file already exists
                        if (!unlink(DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                        {
                            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old announcement image file', 'Old announcement image file could not be removed (check permissions)');
                        }
                    }
                    if (!move_uploaded_file($_FILES['imagename']['tmp_name'], DIR_ATTACHMENTS . 'announcements/' . $_FILES['imagename']['name']))
                    {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error saving announcement image file', 'Announcement image file could not be uploaded (check folder permissions)');
                    }
                    else
                    {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "announcements
                            SET imagename = '" . $sheel->db->escape_string($_FILES['imagename']['name']) . "'
                            WHERE announcementid = '" . intval($sheel->GPC['announcementid']) . "'
                            LIMIT 1
                        ");
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'Updated announcement image file', 'A new announcement image file was saved #' . $sheel->GPC['announcementid']);
                    }
                }
                else
                {
                    if (!in_array($ext, array('.png', '.jpg', '.jpeg', '.gif')))
                    {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading announcement image file', 'The announcement image file must be .png, .jpg, .jpeg, or .gif.');
                        refresh(HTTPS_SERVER_ADMIN . 'settings/announcements/update/' . intval($sheel->GPC['announcementid']) . '/?note=extension');
                        exit();
                    }
                    if (filesize($_FILES['imagename']['tmp_name']) >= 250000)
                    {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading announcement image file', 'The announcement image file must be under 250kb in file size.');
                        refresh(HTTPS_SERVER_ADMIN . 'settings/announcements/update/' . intval($sheel->GPC['announcementid']) . '/?note=size');
                        exit();
                    }
                }
            }
            refresh(HTTPS_SERVER_ADMIN . 'settings/announcements/');
            exit();
        }
        $sql = $sheel->db->query("
            SELECT announcementid, content, date, visible, imagename
            FROM " . DB_PREFIX . "announcements
            WHERE announcementid = '" . intval($sheel->GPC['announcementid']) . "'
            LIMIT 1
        ");
        if ($sheel->db->num_rows($sql) > 0)
        {
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
            {
                $res['content'] = o($res['content']);
                $res['date'] = o($res['date']);
                $res['visible_1'] = (($res['visible']) ? 'checked="checked"' : '');
                $res['visible_0'] = (($res['visible']) ? '' : 'checked="checked"');
                $res['imagename_old'] = $res['imagename'];
                $form = $res;
            }
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'delete')
    {
        if ($sheel->show['ADMINCP_TEST_MODE'])
        {
            $sheel->template->templateregistry['message'] = '{_demo_mode_only}';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
        if (isset($sheel->GPC['xid']) AND !empty($sheel->GPC['xid']))
        {
            $sql = $sheel->db->query("
                SELECT imagename
                FROM " . DB_PREFIX . "announcements
                WHERE announcementid = '" . intval($sheel->GPC['xid']) . "'
                LIMIT 1
            ");
            $res = $sheel->db->fetch_array($sql, DB_ASSOC);
            if (!unlink(DIR_ATTACHMENTS . 'announcements/' . $res['imagename']))
            { // delete old file
              $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old announcement image file', 'Old announcement image file [' . $res['imagename'] . '] could not be removed (check permission)');
            }
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "announcements
                WHERE announcementid = '" . intval($sheel->GPC['xid']) . "'
                LIMIT 1
            ");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Announcement deleted', 'An announcement #' . intval($sheel->GPC['xid']) . ' was successfully deleted from the portal');
            die(json_encode(array('response' => '1', 'message' => 'A portal announcement has been successfully deleted.')));
        }
        else
        {
            $sheel->template->templateregistry['message'] = 'This announcement could not be deleted.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    $announcements = array();
    $sql = $sheel->db->query("
        SELECT announcementid, content, date, imagename, visible
        FROM " . DB_PREFIX . "announcements
        ORDER BY announcementid DESC
    ");
    if ($sheel->db->num_rows($sql) > 0)
    {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
        {
            $res['imagename'] = '<img src="' . $sheel->config['imguploadscdn'] . 'announcements/' . $res['imagename'] . '" alt="" width="50">';
            $res['content'] = $sheel->shorten($res['content'], 75);
            $res['visible'] = ($res['visible']) ? 'True' : 'False';
            $res['action'] = '<ul class="segmented">
				<li>
				      <a href="' . HTTPS_SERVER_ADMIN . 'settings/announcements/update/' . $res['announcementid'] . '/" class="btn btn-slim btn--icon" title="{_update}">
					<span class="ico-16-svg halflings halflings-edit draw-icon" aria-hidden="true"></span>
				      </a>
				</li>
				<li>
				      <a href="javascript:;" data-bind-event-click="acp_confirm(\'delete\', \'Delete selected announcement?\', \'Are you sure you want to delete the selected Announcement? This action cannot be reversed and therefore cannot be undone.\', \'' . $res['announcementid'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="{_delete}">
					<span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span>
				      </a>
				</li>
				</ul>';
            $announcements[] = $res;
        }
    }
    $sheel->template->parse_loop('main', array('announcements' => $announcements));
    $sheel->template->parse_loop('main', array('motd' => $motd));
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
   
    $sheel->template->fetch('main', 'settings_announcements.html', 1);
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array())
        )
    );
    $sheel->template->parse_loop('main', array('announcements' => $announcements));
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>