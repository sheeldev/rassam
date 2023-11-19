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
    'addition',
    'vendor' => array(
        'growl',
        'font-awesome',
        'glyphicons',
        'chartist',
        'balloon',
        'growl'
    )
);


if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_dashboard")) === false) {
        $sidenav = $sheel->admincp_nav->print('dashboard');
        $sheel->cache->store("sidenav_dashboard", $sidenav);
    }
    $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Dashboard</div>';
    $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Dashboard';

    $vars['sidenav'] = $sidenav;
    $vars['url'] = $_SERVER['REQUEST_URI'];
    $sheel->template->fetch('main', 'dashboard.html', 1);


    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>