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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Attachment Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Attachment Manager';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }


    $areanav = 'settings_attachments';
    $currentarea = 'Attachment Manager';
    if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'verify')
    {
        if (isset($sheel->GPC['attachid']) AND !empty($sheel->GPC['attachid']))
        {
            foreach ($sheel->GPC['attachid'] AS $value)
            {
                if (!empty($value))
                {
                    $sheel->db->query("
                        UPDATE " . DB_PREFIX . "attachment
                        SET visible = '1'
                        WHERE attachid = '" . intval($value) . "'
                    ", 0, null, __FILE__, __LINE__);
                }
            }
            refresh(HTTPS_SERVER_ADMIN . 'settings/attachments/');
            exit();
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'delete')
    {
        foreach ($sheel->GPC['attachid'] AS $value)
        {
            if ($value > 0)
            {
                $sheel->attachment->remove_attachment(intval($value));
            }
        }
        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Attachments deleted', 'Selected attachments have been deleted from the portal.');
        if (isset($sheel->GPC['returnurl']) AND !empty($sheel->GPC['returnurl']))
        {
            refresh(urldecode($sheel->GPC['returnurl']));
            exit();
        }
        refresh(HTTPS_SERVER_ADMIN . 'settings/attachments/');
        exit();
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'rebuild')
    {
        foreach ($sheel->GPC['attachid'] AS $value)
        {
            if ($value > 0)
            {
                $sheel->auction_pictures_rebuilder->process_picture_rebuilder(intval($value));
            }
        }
        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Attachments rebuilt', 'Selected attachments have been rebuilt in the portal.');
        refresh(HTTPS_SERVER_ADMIN . 'settings/attachments/');
        exit();
    }
    // attachments
    if (!isset($sheel->GPC['page']) OR isset($sheel->GPC['page']) AND $sheel->GPC['page'] <= 0)
    {
        $sheel->GPC['page'] = 1;
    }
    else
    {
        $sheel->GPC['page'] = intval($sheel->GPC['page']);
    }
    $maxrowsdisplay = (isset($sheel->GPC['pp']) AND is_numeric($sheel->GPC['pp'])) ? intval($sheel->GPC['pp']) : $sheel->config['maxrowsadmin'];
    $limit = ' ORDER BY a.attachid DESC LIMIT ' . (($sheel->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
    $attachtypes = array('itemphoto', 'profile', 'pmb', 'digital', 'stores', 'storesbackground');
    $filtersql = $leftjoinsql = $attachtype = '';
    $viewsql = "a.visible = '1'";
    $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
    $form['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
    if (isset($sheel->GPC['view']) AND $sheel->GPC['view'] == 'moderated')
    {
        $viewsql = "a.visible = '0'";
    }

    if (isset($sheel->GPC['filterby']) AND !empty($sheel->GPC['filterby']))
    {
        if ($sheel->GPC['filterby'] == 'user_id')
        {
            $nameuserid = $sheel->db->fetch_field(DB_PREFIX . "users", "username = '" . $sheel->db->escape_string($sheel->GPC['q']) . "'", "user_id");
            if ($nameuserid > 0)
            {
                $filtersql = " AND a.user_id = '" . $nameuserid . "'";
            }
            else
            {
                $filtersql = " AND a.user_id = '" . $sheel->db->escape_string($sheel->GPC['q']) . "'";
            }
        }
        else
        {
            $filtersql = " AND a." . $sheel->db->escape_string($sheel->GPC['filterby']) . " = '" . $sheel->db->escape_string($sheel->GPC['q']) . "'";
        }
    }
    if (isset($sheel->GPC['attachtype']) AND in_array($sheel->GPC['attachtype'], $attachtypes))
    {
        $attachtype = "AND a.attachtype = '" . $sheel->db->escape_string($sheel->GPC['attachtype']) . "'";
    }
    $attachments = array();
    $sql = $sheel->db->query("
        SELECT a.attachid, a.attachtype, a.user_id, a.project_id,  a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filehash, a.ipaddress, a.tblfolder_ref, a.watermarked, u.username, u.first_name, u.last_name
        FROM " . DB_PREFIX . "attachment a
        $leftjoinsql
        LEFT JOIN " . DB_PREFIX . "users u ON (a.user_id = u.user_id)
        WHERE $viewsql
        $filtersql
        $attachtype
        $limit
    ", 0, null, __FILE__, __LINE__);
    $sqltmp = $sheel->db->query("
        SELECT a.attachid, a.attachtype, a.user_id, a.project_id,  a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filehash, a.ipaddress, a.tblfolder_ref, a.watermarked,  u.username, u.first_name, u.last_name
        FROM " . DB_PREFIX . "attachment a
        $leftjoinsql
        LEFT JOIN " . DB_PREFIX . "users u ON (a.user_id = u.user_id)
        WHERE $viewsql
        $filtersql
        $attachtype
    ", 0, null, __FILE__, __LINE__);
    $totalcount = $sheel->db->num_rows($sqltmp);
    $form['number'] = number_format($totalcount);
    if ($sheel->db->num_rows($sql) > 0)
    {
        $sheel->show['no_attachments'] = false;
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
        {
            $res['url'] = HTTP_ATTACHMENTS . $sheel->attachment_tools->fetch_physical_folder_name($res['attachtype']) . '/' . $res['filehash'] . '/' . $res['filename'];
            $res['urlfolder'] = HTTP_ATTACHMENTS . $sheel->attachment_tools->fetch_physical_folder_name($res['attachtype']) . '/' . $res['filehash'] . '/';
            $res['sizes'] = ''; //'<div class="pt-6 litegray">' . $sheel->attachment_tools->fetch_attachment_dimensions($res) . '</div>';
            $res['subscriber'] = o($res['username']);
            $res['fullname'] = o($res['first_name'] . ' ' . $res['last_name']);
            $res['filesize'] = $sheel->attachment->print_filesize($res['filesize']);
            $url = $sheel->attachment->print_file_extension_icon($res['filename']);
            $ext = $sheel->attachment->fetch_extension($res['filename']);
            $ext = strtolower($ext);
            $res['attachextension'] = '<img src="' . $url . '" border="0" alt="" />';
            $res['date'] = $sheel->common->print_date($res['date']);
            $filename = $res['filename'];
            $res['filename'] = $sheel->shorten($filename, 23);
            $res['filenamefull'] = $filename;
            $res['attachtype'] = $sheel->attachment_tools->fetch_attachment_type($res['attachtype'], $res['project_id'], $res['attachid']);
            $res['counter'] = number_format($res['counter']);
            if (in_array(strtolower($ext), array('gif','jpg','jpeg','png')) AND @list($width, $height) = @getimagesize($res['urlfolder'] . rawurlencode($filename)))
            {
                $res['dimension'] = $width . 'x' . $height;
                $res['icon'] = '<img src="' . $res['urlfolder'] . '60x60.jpg" border="0" alt="" />';
            }
            else
            {
                $res['dimension'] = '-';
                $res['icon'] = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.gif" width="60" border="0" alt="" />';
            }
            $res['watermarked'] = (($res['watermarked']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '-');
            $res['invoiceid'] = (($res['invoiceidpublic'] != '') ? '<a href="' . HTTPS_SERVER_ADMIN . 'accounting/invoices/?filter=invoiceidpublic&amp;q=' . $res['invoiceidpublic'] . '">' . $res['invoiceid'] . '</a>' : '-');
            $attachments[] = $res;
        }
    }
    else
    {
        $sheel->show['no_attachments'] = true;
    }
    $pageurl = PAGEURL;
    $prevnext = $sheel->admincp->pagination($totalcount, $sheel->config['maxrowsadmin'], $sheel->GPC['page'], $pageurl);
    

    $sheel->template->fetch('main', 'settings_attachments.html', 1);
    $vars = array(
		'sidenav' => $sidenav,
		'areanav' => (isset($areanav) ? $areanav : ''),
		'currentarea' => (isset($currentarea) ? $currentarea : ''),
		'prevnext' => (isset($prevnext) ? $prevnext : ''),
		'q' => (isset($q) ? $q : '')
	);
    $sheel->template->parse_hash('main', array(
		'form' => (isset($form) ? $form : array()),
		'ilpage' => $sheel->ilpage
	));
    $sheel->template->parse_loop('main', array('attachments' => $attachments));

    
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>