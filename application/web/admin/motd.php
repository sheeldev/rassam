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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Message of the Day</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Message of the Day';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {

    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }

    $areanav = 'settings_motd';
    $currentarea = 'Message of the Day';
    if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'add')
    {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Message of the Day - Add</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Message of the Day - Add';
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
        {
            $sheel->db->query("
                INSERT INTO " . DB_PREFIX . "motd
                (motdid, content, date, visible)
                VALUES(
                NULL,
                '" . $sheel->db->escape_string($sheel->GPC['form']['content']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['date']) . "',
                '1'
                )
            ");
            refresh(HTTPS_SERVER_ADMIN . 'settings/motd/');
            exit();
        }
        $form['content'] = '';
        $form['date'] = '';
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'update')
    {
        $sheel->template->meta['areatitle'] = 'Admin CP | Settings<div class="type--subdued">Message of the Day - Edit</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings - Message of the Day - Edit';
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
        {
            foreach ($sheel->GPC['form'] AS $motid => $value)
            {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "motd
                    SET content = '" . $sheel->db->escape_string($value['content']) . "',
                    date = '" . $sheel->db->escape_string($value['date']) . "'
                    WHERE motdid = '" . intval($motid) . "'
                    LIMIT 1
                ");
            }
            refresh(HTTPS_SERVER_ADMIN . 'settings/motd/');
            exit();
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'delete')
    {
        if (isset($sheel->GPC['xid']) AND !empty($sheel->GPC['xid']))
        {
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "motd
                WHERE motdid = '" . intval($sheel->GPC['xid']) . "'
                LIMIT 1
            ");
            die(json_encode(array('response' => 1, 'message' => 'Successfully deleted message of the day ID #' . $sheel->GPC['xid'])));
        }
        else
        {
            $sheel->template->templateregistry['message'] = 'No message of the day was selected.  Please try again.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    $motd = array();
    $sqlmotd = $sheel->db->query("
        SELECT motdid, content, date
        FROM " . DB_PREFIX . "motd
        WHERE visible = '1'
        ORDER BY motdid DESC
    ");
    if ($sheel->db->num_rows($sqlmotd) > 0)
    {
        while ($res = $sheel->db->fetch_array($sqlmotd, DB_ASSOC))
        {
            $motd[] = $res;
        }
    }
    $sheel->template->parse_loop('main', array('motd' => $motd));
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
   
    $sheel->template->fetch('main', 'settings_motd.html', 1);
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array())
        )
    );
    $sheel->template->parse_loop('main', array('motd' => $motd));
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>