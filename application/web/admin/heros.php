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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Hero Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP |  Hero Manager';
$sheel->template->meta['jsinclude']['header'][] = 'vendor/upclick';
$sheel->template->meta['jsinclude']['footer'][] = 'admin_heros';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {

    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    $areanav = 'settings_heros';
    $currentarea = 'Hero Manager';

    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'inactivate') { // inactivate hero
        if ($sheel->show['ADMINCP_TEST_MODE']) {
            $sheel->template->templateregistry['message'] = '{_demo_mode_only}';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
        if (isset($sheel->GPC['xid']) and !empty($sheel->GPC['xid'])) {
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "hero
                WHERE id = '" . $sheel->db->escape_string($sheel->GPC['xid']) . "'
                LIMIT 1
            ", 0, null, __FILE__, __LINE__);
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Hero graphic inactivated', 'A system hero graphic has been successfully inactivated.');
            die(json_encode(array('response' => '1', 'message' => 'Hero graphic has been successfully inactivated.')));
        } else {
            $sheel->template->templateregistry['message'] = 'This hero graphic could not be inactivated.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'upload') {
        if (is_array($_FILES) and isset($_FILES['upload']['tmp_name'])) {
            if (is_uploaded_file($_FILES['upload']['tmp_name'])) {
                if ($fileinfo = getimagesize($_FILES['upload']['tmp_name'])) { // only accept image
                    if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) {
                        // check filesize to keep sites running fast
                        if (filesize($_FILES['upload']['tmp_name']) > 500000) {
                            $array = array(
                                'error' => 1,
                                'note' => 'File size is to big for hero graphic. Try less than 500KB.'
                            );
                            $response = json_encode($array);
                            echo $response;
                            exit();
                        }
                        // check width / height limits
                        if ($fileinfo[0] != $sheel->config['herowidth'] or $fileinfo[1] != $sheel->config['heroheight']) {
                            $array = array(
                                'error' => 1,
                                'note' => 'The width and height must be exactly ' . $sheel->config['herowidth'] . 'x' . $sheel->config['heroheight'] . ' pixels (yours was ' . $fileinfo[0] . 'x' . $fileinfo[1] . ') for the currently viewing theme.'
                            );
                            $response = json_encode($array);
                            echo $response;
                            exit();
                        }
                        if ($fileinfo[2] != 1 and $fileinfo[2] != 2 and $fileinfo[2] != 3) { // error not .jpg, .gif or .png
                            $array = array(
                                'error' => 1,
                                'note' => 'The image type ust be jpg, gif or png (yours was ' . $fileinfo[2] . ')'
                            );
                            $response = json_encode($array);
                            echo $response;
                            exit();
                        }
                        $sourcepath = $_FILES['upload']['tmp_name'];
                        $targetpath = DIR_ATTACHMENTS . 'heros/' . $_FILES['upload']['name'];
                        $alreadyexists = false;
                        if (file_exists($targetpath)) {
                            unlink($targetpath);
                            $alreadyexists = true;
                        }
                        if (move_uploaded_file($sourcepath, $targetpath)) {
                            if ($alreadyexists) {
                                list($width, $height) = getimagesize($targetpath);
                                $sheel->db->query("
									UPDATE " . DB_PREFIX . "hero
									SET width = '" . intval($width) . "',
									height = '" . intval($height) . "'
									WHERE filename = '" . $sheel->db->escape_string($_FILES['upload']['name']) . "'
								", 0, null, __FILE__, __LINE__);
                            }
                            $array = array(
                                'error' => 0,
                                'text' => $_FILES['upload']['name'] . ' (' . $sheel->attachment->print_filesize(filesize($_FILES['upload']['tmp_name'])) . ')',
                                'value' => $_FILES['upload']['name'],
                                'note' => 'Your file was uploaded successfully' . (($alreadyexists) ? ' and was replaced with new width/height: ' . $width . 'x' . $height . '' : '') . '.'
                            );
                            $response = json_encode($array);
                            echo $response;
                            exit();
                        }
                    }
                } else {
                    $array = array(
                        'error' => 1,
                        'note' => 'Only image types accepted (jpg, gif or png)'
                    );
                    $response = json_encode($array);
                    echo $response;
                    exit();
                }
            }
        }
        $array = array(
            'error' => 1,
            'note' => 'Check folder permissions for /images/heros/'
        );
        $response = json_encode($array);
        echo $response;
        exit();
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') { // delete hero graphic from libary
        if (isset($sheel->GPC['xid']) and !empty($sheel->GPC['xid'])) {
            $sql = $sheel->db->query("
                SELECT * FROM " . DB_PREFIX . "hero
                WHERE filename = '" . $sheel->db->escape_string($sheel->GPC['xid']) . "'
                LIMIT 1
            ", 0, null, __FILE__, __LINE__);
            if ($sheel->db->num_rows($sql) <= 0) {
                if (file_exists(DIR_ATTACHMENTS . 'heros/' . $sheel->GPC['xid'])) { // is this file in use by other active heros?
                    if (!unlink(DIR_ATTACHMENTS . 'heros/' . $sheel->GPC['xid'])) {
                        $sheel->template->templateregistry['message'] = 'This hero graphic could not be deleted.  Check folder permissions.';
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing hero graphic file', 'Hero graphic file could not be removed (check permissions)');
                        die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                    }
                }
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Hero graphic deleted', 'A system hero graphic has been successfully deleted.');
                die(json_encode(array('response' => '1', 'message' => 'Hero graphic has been successfully deleted.')));
            } else {
                $sheel->template->templateregistry['message'] = 'This hero graphic is assigned to an active hero slider and cannot be deleted.  Please inactivate it first.';
                die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
            }
        } else {
            $sheel->template->templateregistry['message'] = 'This hero graphic could not be deleted.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'heromanage') {
        if (isset($sheel->GPC['activatehero']) and isset($sheel->GPC['source_url4']) and !empty($sheel->GPC['source_url4']) and isset($sheel->GPC['src_styleid']) and $sheel->GPC['src_styleid'] > 0) { // activate hero
            $sheel->db->query("
                INSERT INTO " . DB_PREFIX . "hero
                (id, mode,  filename, width, height, imagemap, date_added, sort, styleid)
                VALUES (
                NULL,
                '" . $sheel->db->escape_string($sheel->GPC['src_mode']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['source_url4']) . "',
                '" . intval($sheel->GPC['img_width']) . "',
                '" . intval($sheel->GPC['img_height']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['html_container']) . "',
                '" . DATETIME24H . "',
                '" . intval($sheel->GPC['sort']) . "',
                '" . intval($sheel->GPC['src_styleid']) . "')
            ", 0, null, __FILE__, __LINE__);
            refresh(HTTPS_SERVER_ADMIN . 'settings/heros/?note=activated');
            exit();
        } else if (isset($sheel->GPC['updatehero']) and isset($sheel->GPC['source_url3']) and !empty($sheel->GPC['source_url3']) and isset($sheel->GPC['source_url3_id']) and $sheel->GPC['source_url3_id'] > 0 and isset($sheel->GPC['src_styleid']) and $sheel->GPC['src_styleid'] > 0) { // update hero
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "hero
                SET sort = '" . intval($sheel->GPC['sort']) . "',
                imagemap = '" . $sheel->db->escape_string($sheel->GPC['html_container']) . "',
                mode = '" . $sheel->db->escape_string($sheel->GPC['src_mode']) . "',
                styleid = '" . intval($sheel->GPC['src_styleid']) . "'
                WHERE id = '" . $sheel->db->escape_string($sheel->GPC['source_url3_id']) . "'
                LIMIT 1
            ", 0, null, __FILE__, __LINE__);
        }
    }
    $heropictureoptions = $activeheropictureoptions = '';
    $sheel->show['inactiveheros'] = false;
    $active = array();
    $sql = $sheel->db->query("
        SELECT id, mode, filename, imagemap, date_added, sort, width, height, styleid
        FROM " . DB_PREFIX . "hero
        ORDER BY sort
    ", 0, null, __FILE__, __LINE__);
    if ($sheel->db->num_rows($sql) > 0) {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            $active[] = $res['filename'];
            $activeheropictureoptions .= '<option value="' . $res['filename'] . '" id="' . $res['id'] . '" styleid="' . $res['styleid'] . '" cid="' . $res['cid'] . '" folder="' . (($res['mode'] == 'categoryflyout') ? 'categoryheros' : 'heros') . '">(' . (($res['mode'] == 'categorymap' or $res['mode'] == 'storescategorymap' or $res['mode'] == 'categoryflyout') ? $res['mode'] . ' : ' . $sheel->categories->title($_SESSION['sheeldata']['user']['slng'], $res['cid']) . '' : $res['mode']) . ') ' . $res['filename'] . (($res['width'] > 0) ? ' (' . $res['width'] . 'x' . $res['height'] . ')' : '') . ' | {_display_order}: ' . $res['sort'] . ' | {_theme}: #' . $res['styleid'] . '</option>';
            // 				var_dump($activeheropictureoptions); die();
        }
    }
    $dirname = DIR_ATTACHMENTS . 'heros';
    $dir = opendir($dirname);
    while (false != ($file = readdir($dir))) {
        if (($file != '.') and ($file != '..')) {
            list($width, $height, $type, $attr) = getimagesize($dirname . '/' . $file);
            $heropictureoptions .= '<option value="' . $file . '">' . $file . ' (' . $width . 'x' . $height . ') (' . $sheel->attachment->print_filesize(filesize($dirname . '/' . $file)) . ')</option>';
        }
    }
    if (empty($heropictureoptions)) {
        $sheel->show['inactiveheros'] = true;
    }
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $vars['heropictureoptions'] = (isset($heropictureoptions) ? $heropictureoptions : '');
    $vars['activeheropictureoptions'] = (isset($activeheropictureoptions) ? $activeheropictureoptions : '');
    $sheel->template->fetch('main', 'settings_heros.html', 1);
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