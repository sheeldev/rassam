<?php
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'vendor/jquery_' . JQUERYVERSION,
		'vendor/jquery_bxslider',
		'vendor/lazyload',
		'vendor/growl',
		'inline'
	),
	'footer' => array(
		'v5',
		'vendor/jquery_slides',
		'autocomplete',
		'homepage'
	)
);
$sheel->template->meta['cssinclude'] = array(
	'header',
	'showcase',
	'thumbnail',
	'homepage',
	'breadcrumb',
	'slider',
	'slidein',
	'footer',
	'common',
	'auction',
	'vendor' => array(
		'balloon',
		'font-awesome',
		'bootstrap',
		'growl'
	)
);
$sheel->template->meta['navcrumb'] = array(HTTPS_SERVER => '{_homepage}');
define('LOCATION', 'home');

if (empty($_SESSION['sheeldata']['user']['userid']) or $_SESSION['sheeldata']['user']['userid'] == 0) {
	refresh(HTTPS_SERVER . 'signin/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
	exit();
}

$sheel->template->meta['area'] = 'main';
$sheel->show['widescreen'] = true;
$sheel->show['fluidscreen'] = false;
$sheel->show['nobreadcrumb'] = $sheel->show['categorynav'] = true;
$sheel->template->meta['areatitle'] = '{_main_menu}';
$sheel->template->meta['pagetitle'] = '{_template_metatitle} | ' . SITE_NAME;
$sheel->template->meta['description'] = '{_template_metadescription}';
$sheel->template->meta['keywords'] = '{_template_metakeywords}';
$sheel->template->meta['navcrumb'] = array();
$sheel->template->meta['navcrumb'][""] = '{_marketplace}';
$sheel->template->meta['cssinclude'][] = 'stores';
$homepageheros = array();
$hpaurl = $heroimagemaps = '';
$userid = ((!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? $_SESSION['sheeldata']['user']['userid'] : 0);

if (($homepageheros = $sheel->cache->fetch('homepageheros')) === false) {
	$homepageheros = $sheel->hero->fetch_heros('homepage');
	$sheel->cache->store('homepageheros', $homepageheros);
}
if (count($homepageheros) > 0) {
	foreach ($homepageheros as $key => $value) {
		if (!empty($value['imagemap'])) {
			$heroimagemaps .= str_replace('{id}', $value['id'], $value['imagemap']);
		}
	}
}
unset($userid, $stats);
$vars = array(
	'heroimagemaps' => $heroimagemaps
);
$loops = array(
	'homepageheros' => $homepageheros
);
$sheel->template->fetch('main', 'home.html');
$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
$sheel->template->parse_loop('main', $loops, false);
$sheel->template->pprint('main', $vars);
exit();
?>