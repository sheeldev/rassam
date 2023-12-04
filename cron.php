#!/usr/bin/env php
<?php
@ignore_user_abort(1);
@set_time_limit(0);

define('LOCATION', 'cron');
define('SKIP_SESSION', true);
define('SITE_ROOT', __DIR__ . '/');
require_once(SITE_ROOT . 'application/config.php');


$cronid = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;
if ($cronid < 1) {
	$cronid = null;
}
$sheel->automation->execute_task($cronid);
?>