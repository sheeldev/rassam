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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Access</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Access';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $sheel->template->templateregistry['message'] = 'You do not have permission to access this page. contact your administrator and provide the following page reference: ' . $match['name'];
    $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
    exit();

} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>