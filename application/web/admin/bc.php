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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Business Central API Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Business Central API Manager';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $areanav = 'settings_bc';
    $currentarea = 'Business Central API Manager';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'enable') {
        if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
            $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
            $response = array();
            $response = $sheel->admincp_common->enablebcapis($ids);
            unset($ids);
            $sheel->template->templateregistry['success'] = $response['success'];
            $sheel->template->templateregistry['errors'] = $response['errors'];
            die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
        } else {
            $sheel->template->templateregistry['message'] = 'Selected APIs could not be enabled.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'disable') {
        if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
            $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
            $response = array();
            $response = $sheel->admincp_common->disablebcapis($ids);
            unset($ids);
            $sheel->template->templateregistry['success'] = $response['success'];
            $sheel->template->templateregistry['errors'] = $response['errors'];
            die(json_encode(array('response' => '2', 'success' => $sheel->template->parse_template_phrases('success'), 'errors' => $sheel->template->parse_template_phrases('errors'), 'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']], 'successids' => $response['successids'], 'failedids' => $response['failedids'])));
        } else {
            $sheel->template->templateregistry['message'] = 'Selected APIs could not be disabled.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    $sqlview = "";
    if (isset($sheel->GPC['view'])) {
        switch ($sheel->GPC['view']) {
            case 'active': {
                    $sqlview = "AND active = '1'";
                    break;
                }
            case 'inactive': {
                    $sqlview = "AND active = '0'";
                    break;
                }
        }
    }
    $api = array();
    $sql = $sheel->db->query("
        SELECT id, apigroup, name, value, tokenendpoint, authendpoint, clientid, clientsecret, params, provides, hits, success, failed, active
        FROM " . DB_PREFIX . "dynamics_api
        WHERE adminonly = '1'
            $sqlview
        ORDER BY apigroup ASC
    ");
    if ($sheel->db->num_rows($sql) > 0) {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            $res['active'] = (($res['active'] == 1) ? '<center><span class="badge w-90pct badge--success" title="{_active}">{_active}</span></center>' : '<center><span class="badge w-90pct badge--critical" title="{_inactive}">{_inactive}</span></center>');
            $api[] = $res;
        }
    }
    $form['number'] = $sheel->db->num_rows($sql);
    $form['q'] = ((isset($sheel->GPC['q'])) ? o($sheel->GPC['q']) : '');

    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $sheel->template->fetch('main', 'settings_bc.html', 1);
    $sheel->template->parse_loop('main', array('api' => $api));
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array())
        )
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>