<?php
define('LOCATION', 'rpc');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params']))
{
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}

$sheel->template->meta['areatitle'] = 'XML-RPC - /rpc/';

// fire up our xml-rpc server
$xmlrpcserver = iL('xmlrpcserver');
$sheel_xmlrpcserver = new sheel_xmlrpcserver($sheel, $xmlrpcserver);
$answer = $xmlrpcserver->send_reponse();
header('Content-Type: text/xml');
die($answer);
?>
