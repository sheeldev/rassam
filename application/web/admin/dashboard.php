<?php
define('LOCATION', 'admin');
if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
    'header' => array(
        'functions',
        'admin',
        'admin_dashboard',
        'inline',
        'vendor/chartist',
        'vendor/chartistlegend',
        'vendor/chartisttooltip',
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
        'chartisttooltip',
        'balloon',
        'growl'
    ),
    'dashboard'
);
$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Dashboard</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Dashboard';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_dashboard")) === false) {
        $sidenav = $sheel->admincp_nav->print('dashboard');
        $sheel->cache->store("sidenav_dashboard", $sidenav);
    }
    $sheel->GPC['period'] = ((isset($sheel->GPC['period']) and !empty($sheel->GPC['period'])) ? $sheel->GPC['period'] : 'last7days');
    $period = '{_last_7_days}';
    $periods = array(
        'today' => array(
            'title' => '{_today}',
        ),
        'yesterday' => array(
            'title' => '{_yesterday}',
        ),
        'last7days' => array(
            'title' => '{_last_7_days}',
        ),
        'last30days' => array(
            'title' => '{_last_30_days}',
        ),
        'last60days' => array(
            'title' => '{_last_60_days}',
        ),
        'last90days' => array(
            'title' => '{_last_90_days}',
        ),
        'last365days' => array(
            'title' => '{_last_365_days}',
        )
    );
    foreach ($periods as $key => $value) {
        $parr[$key] = $value['title'];
        $parrs[] = $key;
    }
    if (!in_array($sheel->GPC['period'], $parrs)) {
        $sheel->GPC['period'] = 'last7days';
    }

    $periodpulldown = $sheel->construct_pulldown('period', 'period', $parr, $sheel->GPC['period'], 'class="draw-select" onchange="this.form.submit()"');
    unset($parr);
    if (isset($sheel->GPC['period']) and isset($periods[$sheel->GPC['period']]['title'])) {
        $period = $periods[$sheel->GPC['period']]['title'];
    }

    $stats = $sheel->admincp_dashboard->stats('dashboard', $sheel->GPC['period']);

	$orders['totalorders'] = $stats['orders']['totalorders'];
    $orders['totalquantity'] = $stats['orders']['totalquantity'];
    $orders['invoiced'] = $stats['orders']['invoiced'];
    $orders['archived'] = $stats['orders']['archived'];
    $orders['smallorders'] = $stats['orders']['smallorders'];
    $orders['mediumorders'] = $stats['orders']['mediumorders'];
    $orders['largeorders'] = $stats['orders']['largeorders'];

	$orders['label'] = $stats['orders']['label'];
	$orders['series'] = $stats['orders']['series'];

	$loops = array(
        'topdestinations' => $stats['stats']['topdestinations'],
        'assembliescategories' => $stats['stats']['assembliescategories'],
		'topcustomers' => $stats['stats']['topcustomers'],
		'topentities' => $stats['stats']['topentities'],
		'ordersizes' => $stats['stats']['ordersizes'],
        'analysis' => $stats['stats']['analysis'],
	);


    $vars = array(
        'sidenav' => $sidenav,
        'period' => $period,
        'periodpulldown' => $periodpulldown,
        'orders1' => $orders['label'],
        'orders2' => $orders['series']
    );
    $vars['url'] = $_SERVER['REQUEST_URI'];
    $sheel->template->fetch('main', 'dashboard.html', 1);
    $sheel->template->parse_hash('main', array('slpage' => $sheel->slpage, 'orders' => $orders,  'statistics' => $statistics));

    $sheel->template->parse_loop('main', $loops, false);
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>