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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Memberships</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Memberships';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $slng = (isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
    $areanav = 'settings_memberships';
    $currentarea = 'Memberships';
    $sheel->template->meta['jsinclude']['header'][] = 'vendor/upclick';
    $sheel->template->meta['jsinclude']['footer'][] = 'admin_memberships';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    $languages_role = $languages_plan = $languages_permission_group = $subscription_rows = $access_permission_groups = $r = array();
    $sql_lang = $sheel->db->query("
        SELECT languagecode, title, textdirection
        FROM " . DB_PREFIX . "language
    ");
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {

        if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'plan') {
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "subscription_permissions
                WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
            ");
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "subscription
                WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                    AND canremove = '1'
                LIMIT 1
            ");
            $sheel->template->templateregistry['message'] = '{_successfully_deleted_membership}';
            die(
                json_encode(
                    array(
                        'response' => '1',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
            exit();
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'plan') {
            $sheel->template->meta['areatitle'] = 'Admin CP | Membership - Update';
            $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Membership - Update';
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
                $field = '';
                $sql_lang = $sheel->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    if (!isset($sheel->GPC['form']['description_' . $languagecode]) or empty($sheel->GPC['form']['description_' . $languagecode]) or !isset($sheel->GPC['form']['title_' . $languagecode]) or empty($sheel->GPC['form']['title_' . $languagecode])) {
                        $sheel->admincp->print_action_failed('{_please_fill_all_fields}', HTTPS_SERVER_ADMIN . 'marketplance/plans/');
                        exit();
                    }
                    $field .= "title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "', description_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['description_' . $languagecode]) . "', ";
                }
                $active = ((isset($sheel->GPC['form']['active']) and $sheel->GPC['form']['active'] == '1') ? 'yes' : 'no');
                $canremove = ((isset($sheel->GPC['form']['canremove']) and $sheel->GPC['form']['canremove'] == '1') ? '1' : '0');
                if ($sheel->GPC['form']['visible'] == '1') {
                    $visible_registration = '1';
                    $visible_upgrade = '0';
                } else if ($sheel->GPC['form']['visible'] == '2') {
                    $visible_registration = '0';
                    $visible_upgrade = '1';
                } else if ($sheel->GPC['form']['visible'] == '3') {
                    $visible_registration = '1';
                    $visible_upgrade = '1';
                } else {
                    $visible_registration = '0';
                    $visible_upgrade = '0';
                }
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "subscription
                    SET $field
                    cost = '" . $sheel->db->escape_string($sheel->GPC['form']['cost']) . "',
                    length = '" . intval($sheel->GPC['form']['duration']) . "',
                    units = '" . $sheel->db->escape_string(mb_strtoupper($sheel->GPC['form']['units'])) . "',
                    active = '" . $active . "',
                    canremove = '" . $canremove . "',
                    visible_registration = '" . $visible_registration . "',
                    visible_upgrade = '" . $visible_upgrade . "',
                    icon = '" . $sheel->db->escape_string($sheel->GPC['form']['icon']) . "',
                    sort ='" . intval($sheel->GPC['form']['sort']) . "'
                    WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                    LIMIT 1
                ");
                refresh(HTTPS_SERVER_ADMIN . 'settings/memberships/');
                exit();
            } else {
                $sql = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "subscription
                    WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                    LIMIT 1
                ");
                if ($sheel->db->num_rows($sql) > 0) {
                    $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                    $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/memberships/">{_memberships}</a> / </span> {_update} ' . $res['title_' . $_SESSION['sheeldata']['user']['slng']];
                    $languages_plan = array();
                    $sql_lang = $sheel->db->query("SELECT languagecode, title, textdirection FROM " . DB_PREFIX . "language");
                    while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                        $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                        $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                        $language['rslng'] = $rlanguagecode;
                        $language['title'] = stripslashes($res['title_' . $languagecode]);
                        $language['description'] = stripslashes($res['description_' . $languagecode]);
                        $language['language'] = $res_lang['title'];
                        $language['languagecode'] = $languagecode;
                        $form['title_' . $languagecode] = stripslashes($res['title_' . $languagecode]);
                        $form['description_' . $languagecode] = stripslashes($res['description_' . $languagecode]);
                        $form['flag_' . $languagecode] = $language['rslng'];
                        $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                        $languages_plan[] = $language;
                    }
                    $sheel->GPC['form']['subscriptionid'] = (isset($sheel->GPC['form']['subscriptionid']) ? intval($sheel->GPC['form']['subscriptionid']) : '');
                    $form['subscriptionid'] = $sheel->GPC['subscriptionid'];
                    $form['cost'] = o($res['cost']);
                    $form['active'] = (($res['active'] == 'yes') ? 'checked="checked"' : '');
                    $form['canremove'] = (($res['canremove'] == '1') ? 'checked="checked"' : '');
                    for ($i = 1; $i < 31; $i++) {
                        $arr[$i] = $i;
                    }
                    $form['duration_pulldown'] = $sheel->construct_pulldown('form_duration', 'form[duration]', $arr, $res['length'], 'class="draw-select"');
                    $form['unit_pulldown'] = $sheel->construct_pulldown('form_units', 'form[units]', array('D' => '{_day}', 'M' => '{_month}', 'Y' => '{_year}'), $res['units'], 'class="draw-select"');
                    if ($res['visible_registration'] and $res['visible_upgrade']) {
                        $res['visible'] = '3';
                    } else if ($res['visible_registration'] and !$res['visible_upgrade']) {
                        $res['visible'] = '1';
                    } else if (!$res['visible_registration'] and $res['visible_upgrade']) {
                        $res['visible'] = '2';
                    } else if (!$res['visible_registration'] and !$res['visible_upgrade']) {
                        $res['visible'] = '4';
                    }
                    $form['visible_pulldown'] = $sheel->construct_pulldown('form_visible', 'form[visible]', array('1' => '{_registration}', '2' => '{_upgrade}', '3' => '{_all}', '4' => '{_not_visible}'), $res['visible'], 'class="draw-select"');
                    $badges = array();
                    $temp_files = glob(DIR_ATTACHMENTS . 'plan/*.*');
                    foreach ($temp_files as $file) {
                        $file = str_replace(DIR_ATTACHMENTS . 'plan/', '', $file);
                        $badges[$file] = $file;
                    }
                    $form['icon_pulldown'] = $sheel->construct_pulldown('form_icon', 'form[icon]', $badges, '', 'class="draw-select"');
                    //$form['icon'] = $res['icon'];
                    $form['sort'] = $res['sort'];

                }
            }
        } else if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'permissions') {
            $sheel->template->meta['areatitle'] = 'Admin CP | Settings - Membership - Update Permissions';
            $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Settings - Membership - Update Permissions';
            $groupname = $sheel->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'", "title_" . $_SESSION['sheeldata']['user']['slng']);
            $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/memberships/">{_memberships}</a> / <a href="' . HTTPS_SERVER_ADMIN . 'settings/memberships/update/permissions/' . intval($sheel->GPC['subscriptionid']) . '/">' . $groupname . '</a> / </span> {_permissions}';
            $form['subscriptionid'] = $sheel->GPC['subscriptionid'];
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
                $sql = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "subscription_permissions
                    WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                    LIMIT 1
                ");
                if ($sheel->db->num_rows($sql) > 0) { // update permissions
                    foreach ($sheel->GPC['form'] as $k => $v) {
                        $vis = 0;
                        if (isset($sheel->GPC['accessvisible'][$k]) and $sheel->GPC['accessvisible'][$k] == 'on') {
                            $vis = 1;
                        }
                        if (isset($k) and !is_array($v)) {
                            $sheel->db->query("
                                UPDATE " . DB_PREFIX . "subscription_permissions
                                SET value = '" . $sheel->db->escape_string($v) . "',
                                visible = '" . intval($vis) . "'
                                WHERE accessname = '" . $sheel->db->escape_string($k) . "'
                                    AND subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                                LIMIT 1
                            ");
                        }
                    }
                } else { // create new permissions
                    $sql = $sheel->db->query("
                        SELECT id, accessname, accessgroup, accesstype, accessmode, value, original, iscustom, visible
                        FROM " . DB_PREFIX . "subscription_permissions
                        WHERE original = '1' OR iscustom = '0'
                        GROUP BY accessname
                        ORDER BY accessgroup ASC
                    ");
                    if ($sheel->db->num_rows($sql) > 0) {
                        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                            $sheel->db->query("
                                INSERT INTO " . DB_PREFIX . "subscription_permissions
                                (id, subscriptionid, roleid, accessgroup, accessname, accesstype, accessmode, value, canremove, original, iscustom, visible)
                                VALUES(
                                NULL,
                                '" . intval($sheel->GPC['subscriptionid']) . "',
                                '0',
                                '" . $sheel->db->escape_string($res['accessgroup']) . "',
                                '" . $sheel->db->escape_string($res['accessname']) . "',
                                '" . $sheel->db->escape_string($res['accesstype']) . "',
                                '" . $sheel->db->escape_string($res['accessmode']) . "',
                                '" . $sheel->db->escape_string($res['value']) . "',
                                '1',
                                '" . $res['original'] . "',
                                '" . $res['iscustom'] . "',
                                '" . $res['visible'] . "'
                                )
                            ");
                        }
                        foreach ($sheel->GPC['form'] as $k => $v) { // update permissions with pre-configured settings the admin may have enabled/disabled
                            if (isset($sheel->GPC['form'][$v]) and !is_array($sheel->GPC['form'][$v])) {
                                $sheel->db->query("
                                    UPDATE " . DB_PREFIX . "subscription_permissions
                                    SET value = '" . $sheel->db->escape_string($sheel->GPC['form'][$v]) . "'
                                    WHERE accessname = '" . $sheel->db->escape_string($sheel->GPC['form'][$k]) . "'
                                        AND subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                                    LIMIT 1
                                ");
                            }
                        }
                    }
                }
                refresh(HTTPS_SERVER_ADMIN . 'settings/memberships/?note=success');
                exit();
            } else {
                $sqlgroups = $sheel->db->query("
                    SELECT groupid, accessgroup
                    FROM " . DB_PREFIX . "subscription_permissions_groups
                    WHERE visible = '1'
                    ORDER BY accessgroup ASC
                ");

                if ($sheel->db->num_rows($sqlgroups) > 0) {
                    while ($resgroups = $sheel->db->fetch_array($sqlgroups, DB_ASSOC)) {
                        $sqlitems = $sheel->db->query("
                            SELECT id, subscriptionid, accessname, accesstype, value, original, visible
                            FROM " . DB_PREFIX . "subscription_permissions
                            WHERE subscriptionid = '" . intval($sheel->GPC['subscriptionid']) . "'
                                AND accessgroup = '" . $sheel->db->escape_string($resgroups['accessgroup']) . "'
                                AND (accessmode = 'global' OR accessmode = 'product')
                            GROUP BY accessname
                            ORDER BY id ASC
                        ");
                        if ($sheel->db->num_rows($sqlitems) > 0) {
                            while ($resitems = $sheel->db->fetch_array($sqlitems, DB_ASSOC)) {
                                if ($resitems['accesstype'] == 'yesno') {
                                    if ($resitems['value'] == 'yes') {
                                        $resitems['userinput'] = '<label for="yes_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="yes" id="yes_' . $resitems['id'] . '" checked="checked" /> {_yes}</label> <label for="no_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="no" id="no_' . $resitems['id'] . '" /> {_no}</label>';
                                    } else {
                                        $resitems['userinput'] = '<label for="yes_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" id="yes_' . $resitems['id'] . '" value="yes" /> {_yes}</label> <label for="no_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="no" id="no_' . $resitems['id'] . '" checked="checked" /> {_no}</label>';
                                    }
                                } else if ($resitems['accesstype'] == 'text' or $resitems['accesstype'] == 'int') {
                                    $resitems['userinput'] = '<div><input type="text" name="form[' . $resitems['accessname'] . ']" value="' . o($resitems['value']) . '" class="draw-input" /></div>';
                                } else if ($resitems['accesstype'] == 'textarea') {
                                    $resitems['userinput'] = '<div><textarea class="draw-textarea" rows="3" name="form[' . $resitems['accessname'] . ']">' . o($resitems['value']) . '</textarea></div>';
                                }
                                $resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">{' . stripslashes('_' . $resitems['accessname'] . '_text') . '}</span>';
                                $resitems['accessdescription'] = '{' . stripslashes('_' . $resitems['accessname'] . '_desc') . '}';
                                if ($resitems['visible'] == 1) {
                                    $resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" checked="checked" />';
                                } else {
                                    $resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" />';
                                }
                                $r['access_permission_items' . $resgroups['accessgroup']][] = $resitems;
                            }
                            $sheel->show['deletebutton'] = 1;
                        } else {
                            // this is a entirely new permissions instance.  We must collect all original permissions
                            // and any "new" custom permissions the admin/staff may have created in his/her venture
                            // for only 1 given permissions group :-).  collect all original and/or custom created ones
                            // and group by the unique "accessname" while ordering the sort as ascending
                            $sqlitems = $sheel->db->query("
                                SELECT id, subscriptionid, accessname, accesstype, value, original
                                FROM " . DB_PREFIX . "subscription_permissions
                                WHERE accessgroup = '" . $sheel->db->escape_string($resgroups['accessgroup']) . "'
                                    AND (accessmode = 'global' OR accessmode = 'product')
                                    AND (original = '1' OR iscustom = '1')
                                GROUP BY accessname
                                ORDER BY id ASC
                            ");
                            if ($sheel->db->num_rows($sqlitems) > 0) {
                                while ($resitems = $sheel->db->fetch_array($sqlitems)) {
                                    if ($resitems['accesstype'] == 'yesno') {
                                        if ($resitems['value'] == 'yes') {
                                            $resitems['userinput'] = '<label for="yes_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="yes" id="yes_' . $resitems['id'] . '" checked="checked" /> {_yes}</label> <label for="no_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="no" id="no_' . $resitems['id'] . '" /> {_no}</label>';
                                        } else {
                                            $resitems['userinput'] = '<label for="yes_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" id="yes_' . $resitems['id'] . '" value="yes" /> {_yes}</label> <label for="no_' . $resitems['id'] . '"><input type="radio" name="form[' . $resitems['accessname'] . ']" value="no" id="no_' . $resitems['id'] . '" checked="checked" /> {_no}</label>';
                                        }
                                    } else if ($resitems['accesstype'] == 'text' or $resitems['accesstype'] == 'int') {
                                        $resitems['userinput'] = '<div><input type="text" name="form[' . $resitems['accessname'] . ']" value="' . o($resitems['value']) . '" class="draw-input" /></div>';
                                    } else if ($resitems['accesstype'] == 'textarea') {
                                        $resitems['userinput'] = '<div><textarea class="draw-textarea" rows="3" name="form[' . $resitems['accessname'] . ']">' . o($resitems['value']) . '</textarea></div>';
                                    }
                                    $resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">' . stripslashes('{_' . $resitems['accessname'] . '_text}') . '</span>';
                                    $resitems['accessdescription'] = stripslashes('{_' . $resitems['accessname'] . '_desc}');
                                    $resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" value="1" checked="checked" />';

                                    $r['access_permission_items' . $resgroups['accessgroup']][] = $resitems;
                                }
                            }
                        }
                        $resgroups['title'] = '{_' . $resgroups['accessgroup'] . '_permission_help}';
                        $resgroups['help'] = '{_' . $resgroups['accessgroup'] . '_permission_desc}';
                        $access_permission_groups[] = $resgroups;
                    }
                }
            }
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'plan') {
            $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Membership - Add</div>';
            $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Membership - Add';
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {

                $sheel->GPC['form']['icon'] = isset($sheel->GPC['form']['icon']) ? $sheel->GPC['form']['icon'] : 'default.png';
                $migratetoid = ((isset($sheel->GPC['form']['migratetoid']) and $sheel->GPC['form']['migratetoid'] != 'none') ? intval($sheel->GPC['form']['migratetoid']) : 0);
                $migratelogic = ((isset($sheel->GPC['form']['migratelogic']) and $sheel->GPC['form']['migratelogic'] != 'none') ? $sheel->GPC['form']['migratelogic'] : 'none');
                $title = $field1 = $field2 = '';
                $sql_lang = $sheel->db->query("SELECT languagecode, textdirection FROM " . DB_PREFIX . "language");
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    if (!isset($sheel->GPC['form']['description_' . $languagecode]) or empty($sheel->GPC['form']['description_' . $languagecode]) or !isset($sheel->GPC['form']['title_' . $languagecode]) or empty($sheel->GPC['form']['title_' . $languagecode])) {
                        $sheel->admincp->print_action_failed('{_you_can_only_create_a_new_subscription_plan_by_filling_out}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                        exit();
                    }
                    $title .= empty($title) ? "title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "'" : " OR title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "'";
                    $field1 .= 'title_' . $languagecode . ', description_' . $languagecode . ', ';
                    $field2 .= "
                    '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "',
                    '" . $sheel->db->escape_string($sheel->GPC['form']['description_' . $languagecode]) . "',";
                }
                $sql = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "subscription
                    WHERE $title
                        AND type = 'product'
                    LIMIT 1
                ");
                if ($sheel->db->num_rows($sql) > 0) {
                    $sheel->admincp->print_action_failed('{_this_subscription_plan_already_exists_and_cannot_be_recreated}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                    exit();
                } else {
                    if (empty($sheel->GPC['form']['cost'])) {
                        $sheel->admincp->print_action_failed('{_you_can_only_create_a_new_subscription_plan_by_filling_out}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                        exit();
                    } elseif ($sheel->GPC['form']['units'] == 'Y' and intval($sheel->GPC['form']['duration']) > 10) {
                        $sheel->admincp->print_action_failed('{_maximum_length_of_subscription_plan_that_you_can_create_is_10_years}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                        exit();
                    }
                    if ($sheel->GPC['form']['visible'] == '1') {
                        $visible_registration = '1';
                        $visible_upgrade = '0';
                    } else if ($sheel->GPC['form']['visible'] == '2') {
                        $visible_registration = '0';
                        $visible_upgrade = '1';
                    } else if ($sheel->GPC['form']['visible'] == '3') {
                        $visible_registration = '1';
                        $visible_upgrade = '1';
                    } else {
                        $visible_registration = '0';
                        $visible_upgrade = '0';
                    }
                    $sort = isset($sheel->GPC['form']['sort']) ? $sheel->GPC['form']['sort'] : 0;
                    $sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "subscription
                        (subscriptionid, " . $field1 . "cost, length, units,  active, canremove, visible_registration, visible_upgrade, icon, sort, type)
                        VALUES(
                        NULL,
                        " . $field2 . "
                        '" . $sheel->db->escape_string($sheel->GPC['form']['cost']) . "',
                        '" . $sheel->db->escape_string($sheel->GPC['form']['duration']) . "',
                        '" . $sheel->db->escape_string($sheel->GPC['form']['units']) . "',
                        '" . $sheel->db->escape_string($sheel->GPC['form']['active']) . "',
                        '" . $sheel->db->escape_string($sheel->GPC['form']['canremove']) . "',
                        '" . intval($visible_registration) . "',
                        '" . intval($visible_upgrade) . "',
                        '" . $sheel->db->escape_string($sheel->GPC['form']['icon']) . "',
                        '" . intval($sort) . "',
                        'product')
                    ");
                    refresh(HTTPS_SERVER_ADMIN . 'settings/memberships/');
                    exit();
                }
            } else {
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                    $language['rslng'] = $rlanguagecode;
                    $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
                    $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
                    $languages_plan[] = $language_r;
                    $form['title_' . $languagecode] = '';
                    $form['description_' . $languagecode] = '';
                    $form['flag_' . $languagecode] = $language['rslng'];
                    $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                }
                $form['cost'] = '0.00';
                for ($i = 1; $i < 31; $i++) {
                    $arr[$i] = $i;
                } // <-- base this on selected payment gateway rules
                $form['duration_pulldown'] = $sheel->construct_pulldown('form_duration', 'form[duration]', $arr, '', 'class="draw-select"');
                $form['unit_pulldown'] = $sheel->construct_pulldown('form_units', 'form[units]', array('D' => '{_days}', 'M' => '{_months}', 'Y' => '{_years}'), '', 'class="draw-select"');
                $form['active'] = 'checked="checked"';
                $form['visible_pulldown'] = $sheel->construct_pulldown('form_visible', 'form[visible]', array('1' => '{_registration}', '2' => '{_upgrade}', '3' => '{_all}', '4' => '{_none}'), '', 'class="draw-select"');
                $badges = array();
                $temp_files = glob(DIR_ATTACHMENTS . 'plan/*.*');
                foreach ($temp_files as $file) {
                    $file = str_replace(DIR_ATTACHMENTS . 'plan/', '', $file);
                    $badges[$file] = $file;
                }
                $form['icon_pulldown'] = $sheel->construct_pulldown('form_icon', 'form[icon]', $badges, '', 'class="draw-select"');
                //$form['icon'] = 'default.png';
                $form['sort'] = '100';
            }

        } else if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'permission') {
            $sheel->template->meta['areatitle'] = 'Admin CP | Marketplace<div class="type--subdued">Membership Plans - Add Permission</div>';
            $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Marketplace - Membership Plans - Add Permission';
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
                $title = $field1 = $field2 = '';
                $sql_lang = $sheel->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    if (!isset($sheel->GPC['form']['description_' . $languagecode]) or empty($sheel->GPC['form']['description_' . $languagecode]) or !isset($sheel->GPC['form']['title_' . $languagecode]) or empty($sheel->GPC['form']['title_' . $languagecode])) {
                        $sheel->admincp->print_action_failed('{_you_can_only_create_a_new_subscription_permission_group_by_filling_out}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                        exit();
                    }
                    $title .= empty($title) ? "title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "'" : " OR title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "'";
                    $field1 .= 'title_' . $languagecode . ', description_' . $languagecode . ', ';
                    $field2 .= "
                    '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "',
                    '" . $sheel->db->escape_string($sheel->GPC['form']['description_' . $languagecode]) . "',";
                }
                $sql = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "subscription_group
                    WHERE $title
                        AND type = 'product'
                    LIMIT 1
                ");
                if ($sheel->db->num_rows($sql) > 0) {
                    $sheel->admincp->print_action_failed('{_this_subscription_permission_group_already_exists_and_cannot_be_recreated}', HTTPS_SERVER_ADMIN . 'settings/memberships/');
                    exit();
                } else {
                    $sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "subscription_group
                        (subscriptionid, " . $field1 . "canremove, type)
                        VALUES(
                        NULL,
                        " . $field2 . "
                        '1',
                        'product')
                    ");
                    refresh(HTTPS_SERVER_ADMIN . 'settings/memberships/');
                    exit();
                }
            } else {
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                    $language['rslng'] = $rlanguagecode;
                    $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
                    $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
                    $languages_permission_group[] = $language_r;
                    $form['title_' . $languagecode] = '';
                    $form['description_' . $languagecode] = '';
                    $form['flag_' . $languagecode] = $language['rslng'];
                    $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                }
            }
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'upload') {
        if (is_array($_FILES) and isset($_FILES['upload']['tmp_name'])) {
            if (is_uploaded_file($_FILES['upload']['tmp_name'])) {
                if ($fileinfo = getimagesize($_FILES['upload']['tmp_name'])) { // only accept image
                    if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) {
                        // check filesize to keep sites running fast
                        if (filesize($_FILES['upload']['tmp_name']) > 50000) {
                            $array = array(
                                'error' => 1,
                                'note' => 'File size is to big for an icon graphic. Try less than 50KB.'
                            );
                            $response = json_encode($array);
                            echo $response;
                            exit();
                        }
                        // check width / height limits
                        if ($fileinfo[0] > 64 or $fileinfo[1] > 64) {
                            $array = array(
                                'error' => 1,
                                'note' => 'The max width and height for this icon is 64x64 (yours was ' . $fileinfo[0] . 'x' . $fileinfo[1] . ').'
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
                        $targetpath = DIR_ATTACHMENTS . 'plan/' . $_FILES['upload']['name'];
                        if (file_exists($targetpath)) {
                            unlink($targetpath);
                        }
                        if (move_uploaded_file($sourcepath, $targetpath)) {
                            $array = array(
                                'error' => 0,
                                'text' => $_FILES['upload']['name'] . ' (' . $sheel->attachment->print_filesize(filesize($_FILES['upload']['tmp_name'])) . ')',
                                'value' => $_FILES['upload']['name'],
                                'note' => 'Your file was uploaded successfully.'
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
    }
    $settings = $sheel->admincp->construct_admin_input('subscriptions_settings', HTTPS_SERVER_ADMIN . 'settings/memberships/');
    while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
        $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
        $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
        $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
        $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
        $form['flag_' . $languagecode] = $rlanguagecode;
        $languages_role[] = $language_r;
        $languages_plan[] = $language_p;
        $languages_permission_group[] = $language_pg;
    }

    $sql = $sheel->db->query("
        SELECT subscriptionid,title_$slng AS title, description_$slng AS description, cost, length, units, canremove, visible_registration, visible_upgrade, icon, active, sort
        FROM " . DB_PREFIX . "subscription
        WHERE type = 'product'
        ORDER BY sort ASC
    ");
    if ($sheel->db->num_rows($sql) > 0) {
        while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            $sqla = $sheel->db->query("
                SELECT COUNT(*) AS usersactive
                FROM " . DB_PREFIX . "subscription_customer
                WHERE subscriptionid = '" . $row['subscriptionid'] . "'
                    AND active = 'yes'
            ");
            if ($sheel->db->num_rows($sqla) > 0) {
                $resactive = $sheel->db->fetch_array($sqla, DB_ASSOC);
                $row['active'] = $resactive['usersactive'];
            } else {
                $row['active'] = '0';
            }
            $sqle = $sheel->db->query("
                SELECT COUNT(*) AS usersexpired
                FROM " . DB_PREFIX . "subscription_customer
                WHERE subscriptionid = '" . $row['subscriptionid'] . "'
                    AND active = 'no'
            ");
            if ($sheel->db->num_rows($sqle) > 0) {
                $resexpired = $sheel->db->fetch_array($sqle, DB_ASSOC);
                $row['expired'] = $resexpired['usersexpired'];
            } else {
                $row['expired'] = '0';
            }
            $row['title'] = stripslashes($row['title']);
            $row['description'] = stripslashes($row['description']);
            if ($row['cost'] > 0) {
                $row['cost'] = $sheel->currency->format($row['cost']);
            } else {
                $row['cost'] = '{_free}';
            }
            $row['units'] = $sheel->subscription->print_unit($row['units']);


            if ($row['canremove'] == 1 and $row['active'] == 0 and $is_migrateto_plan == 0) {
                $sheel->show['candelete_' . $row['subscriptionid']] = 1;
            } else {
                $sheel->show['candelete_' . $row['subscriptionid']] = 0;
            }

            $sqlsetup = $sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "subscription_permissions
                WHERE subscriptionid = '" . $row['subscriptionid'] . "'
            ");
            if ($sheel->db->num_rows($sqlsetup) == 0) {
                $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/memberships/update/permissions/' . $row['subscriptionid'] . '/">{_set_up}</a>';
            } else {
                $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/memberships/update/permissions/' . $row['subscriptionid'] . '/">{_update}</a>';
            }

            $visibility = '';
            if ($row['visible_registration']) {
                $visibility .= '{_registration}, ';
            }
            if ($row['visible_upgrade']) {
                $visibility .= '{_upgrade}, ';
            }
            if (!empty($visibility)) {
                $visibility = substr($visibility, 0, -2);
            } else {
                $visibility = '{_not_visible}';
            }
            $row['visibility'] = $visibility;

            $subscription_rows[] = $row;
        }
    }
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $vars['settings'] = $settings;
    $vars['url'] = $_SERVER['REQUEST_URI'];
    $sheel->template->fetch('main', 'settings_memberships.html', 1);

    $sheel->template->parse_loop(
        'main',
        array(
            'languages_role' => $languages_role,
            'languages_plan' => $languages_plan,
            'languages_permission_group' => $languages_permission_group,
            'subscription_rows' => $subscription_rows,
            'access_permission_groups' => $access_permission_groups
        )
    );
    if (isset($access_permission_groups)) {
        foreach ($access_permission_groups as $key => $value) {
            $sheel->template->parse_loop('main', array(
                'access_permission_items' . $value['accessgroup'] => (isset($r['access_permission_items' . $value['accessgroup']]) ? $r['access_permission_items' . $value['accessgroup']] : array()),
            ), true);
        }
    }
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