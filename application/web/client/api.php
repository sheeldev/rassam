<?php
define('LOCATION', 'api');
if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['areatitle'] = 'REST API - /api/';
// Fire up the REST API server
require_once DIR_CLASSES  . 'class.jsonapiserver.inc.php'; // Include the REST API server class
$apiServer = new SheelRestApiServer($sheel); // Initialize the REST API server
$apiServer->handle_request(); // Handle the incoming request
?>