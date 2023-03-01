<?php
define('LOCATION', 'logout');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params']))
{
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['areatitle'] = '{_sign_out}<div class="smaller">{_logging_out_of_marketplace}</div>';
$sheel->template->meta['pagetitle'] = '{_logging_out_of_marketplace}';

$sheel->common->logout();

if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'all-devices' AND isset($sheel->GPC['uid']) AND $sheel->GPC['uid'] > 0)
{
	$sheel->db->query("
		DELETE FROM " . DB_PREFIX . "sessions
		WHERE userid = '" . intval($sheel->GPC['uid']) . "'
	");
	refresh(HTTPS_SERVER . 'signin/');
	exit();
}
if (isset($sheel->GPC['nc']) AND $sheel->GPC['nc'] > 0)
{
	refresh(HTTPS_SERVER . '?' . intval($sheel->GPC['nc']));
	exit();
}
refresh(HTTPS_SERVER);
exit();
?>
