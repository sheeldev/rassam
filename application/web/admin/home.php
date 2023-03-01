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
	'vendor' => array(
		'font-awesome',
		'glyphicons',
		'chartist',
		'growl'
	),
	'common'
);
// #### setup default breadcrumb ###############################################
$sheel->template->meta['navcrumb'] = array($sheel->ilpage['dashboard'] => $sheel->ilcrumbs[$sheel->ilpage['dashboard']]);
$sheel->template->meta['areatitle'] = '{_admin_cp_dashboard}';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_cp_dashboard}';


//$sheel->show['postitems']='1';
//$sheel->show['autoupdates']='1';
//$sheel->show['usersmoderated']='1';
//$sheel->show['diskspacewarning']='1';

if (($sidenav = $sheel->cache->fetch("sidenav_dashboard")) === false) {
	$sidenav = $sheel->admincp_nav->print($sheel->ilpage['dashboard']);
	$sheel->cache->store("sidenav_dashboard", $sidenav);
}

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
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
	
	$stats = $sheel->admincp->stats('home', $sheel->GPC['period']);
	


	$visitors['visitors'] = $stats['visitors']['visitors'];
	$visitors['uniquevisitors'] = $stats['visitors']['uniquevisitors'];
	$visitors['label'] = $stats['visitors']['label'];
	$visitors['series'] = $stats['visitors']['series'];
	$visitors['pageviews'] = $stats['visitors']['pageviews'];

	$loops = array(
		'topcountries' => $stats['stats']['topcountries'],
		'topdevices' => $stats['stats']['topdevices'],
		'topbrowsers' => $stats['stats']['topbrowsers'],
		'trafficsources' => $stats['stats']['trafficsources'],
		'toplandingpages' => $stats['stats']['toplandingpages'],
		'mostactive' => $stats['visitors']['mostactive']
	);
	$vars = array(
		'sidenav' => $sidenav,
		'period' => $period,
		'periodpulldown' => $periodpulldown
	);

	// notices

	$sheel->template->fetch('main', 'home.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'currency' => $currency, 'visitors' => $visitors, 'revenue' => $revenue, 'sales' => $sales, 'percent' => $percent, 'space' => $space, 'statistics' => $statistics));
	$sheel->template->parse_loop('main', $loops, false);
	$sheel->template->pprint('main', $vars);
	exit();
} else {
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode('/admin/'));
	exit();
}
?>