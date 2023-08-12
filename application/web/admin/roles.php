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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Roles</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Roles';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $slng = (isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
    $areanav = 'users_roles';
    $currentarea = 'Roles';
    if (($sidenav = $sheel->cache->fetch("sidenav_users")) === false) {
        $sidenav = $sheel->admincp_nav->print('users');
        $sheel->cache->store("sidenav_users", $sidenav);
    }
    $migrate_count = 0;
    $languages_role = $roles = array();
    $sql_lang = $sheel->db->query("
        SELECT languagecode, title, textdirection
        FROM " . DB_PREFIX . "language
    ");
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {

        $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "roles
                WHERE roleid = '" . intval($sheel->GPC['roleid']) . "'
                LIMIT 1
            ");
        refresh(HTTPS_SERVER_ADMIN . 'users/roles/');
        exit();


    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Roles - Update Role</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Roles - Update Role';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {

            $field = '';
            $sql_lang = $sheel->db->query("SELECT languagecode, title FROM " . DB_PREFIX . "language");
            while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                if (!isset($sheel->GPC['form']['purpose_' . $languagecode]) or empty($sheel->GPC['form']['purpose_' . $languagecode]) or !isset($sheel->GPC['form']['title_' . $languagecode]) or empty($sheel->GPC['form']['title_' . $languagecode])) {
                    $sheel->admincp->print_action_failed('{_please_fill_all_fields}', HTTPS_SERVER_ADMIN . 'users/roles/');
                    exit();
                }
                $field .= "
                    title_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "',
                    purpose_" . $languagecode . " = '" . $sheel->db->escape_string($sheel->GPC['form']['purpose_' . $languagecode]) . "',
                    ";
            }
            $sheel->GPC['form']['visible'] = (isset($sheel->GPC['form']['visible']) ? intval($sheel->GPC['form']['visible']) : '0');
            $sheel->GPC['form']['isdefault'] = (isset($sheel->GPC['form']['isdefault']) ? intval($sheel->GPC['form']['isdefault']) : '0');
            $sheel->GPC['form']['isadmin'] = (isset($sheel->GPC['form']['isadmin']) ? intval($sheel->GPC['form']['isadmin']) : '0');
            if ($sheel->GPC['form']['isdefault'] == '1') {
                $sheel->db->query("
                UPDATE " . DB_PREFIX . "roles
                SET isdefault = '0'
            ");
            }
            $sheel->db->query("
                    UPDATE " . DB_PREFIX . "roles
                    SET $field
                    custom = '" . $sheel->db->escape_string($sheel->GPC['form']['custom']) . "',
                    roletype = 'product',
                    roleusertype = '" . $sheel->db->escape_string($sheel->GPC['form']['roleusertype']) . "',
                    active = '" . $sheel->GPC['form']['visible'] . "',
                    isdefault = '" . $sheel->GPC['form']['isdefault'] . "',
                    isadmin = '" . $sheel->GPC['form']['isadmin'] . "'
                    WHERE roleid = '" . intval($sheel->GPC['roleid']) . "'
                    LIMIT 1
                ");
            refresh(HTTPS_SERVER_ADMIN . 'users/roles/');
            exit();
        } else {
            $sql = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "roles
                    WHERE roleid = '" . intval($sheel->GPC['roleid']) . "'
                    LIMIT 1
                ");
            if ($sheel->db->num_rows($sql) > 0) {
                $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'users/roles/">{_roles}</a> / </span> {_update_role_for::' . $res['title_' . $_SESSION['sheeldata']['user']['slng']] . '}';
                $languages_role = array();
                $sql_lang = $sheel->db->query("
                        SELECT languagecode, title, textdirection 
                        FROM " . DB_PREFIX . "language");
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {

                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                    $language['rslng'] = $rlanguagecode;
                    $language['title'] = stripslashes($res['title_' . $languagecode]);
                    $language['purpose'] = stripslashes($res['purpose_' . $languagecode]);
                    $language['language'] = $res_lang['title'];
                    $language['languagecode'] = $languagecode;
                    $form['title_' . $languagecode] = stripslashes($res['title_' . $languagecode]);
                    $form['purpose_' . $languagecode] = stripslashes($res['purpose_' . $languagecode]);
                    $form['flag_' . $languagecode] = $language['rslng'];
                    $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                    $languages_role[] = $language;
                }
                $form['roleid'] = $sheel->GPC['roleid'];
                $form['custom'] = o($res['custom']);
                $form['roletypepulldown'] = $sheel->admincp->print_roletype_pulldown($res['roletype']);
                $form['roleusertypepulldown'] = $sheel->admincp->print_roleusertype_pulldown($res['roleusertype']);
                $form['visible'] = (($res['active'] == 1) ? 'checked="checked"' : '');
                $form['isdefault'] = (($res['isdefault'] == 1) ? 'checked="checked"' : '');
                $form['isadmin'] = (($res['isadmin'] == 1) ? 'checked="checked"' : '');
            }
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        $sheel->template->meta['areatitle'] = 'Admin CP | Marketplace <div class="type--subdued">Membership Plans - Add Role</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Marketplace - Membership Plans - Add Role';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'users/roles/">{_roles}</a> / </span> {_add_new_role}';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            $field1 = $field2 = '';
            $sql_lang = $sheel->db->query("SELECT languagecode FROM " . DB_PREFIX . "language");
            while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                if (!isset($sheel->GPC['form']['title_' . $languagecode]) or empty($sheel->GPC['form']['title_' . $languagecode])) {
                    $sheel->admincp->print_action_failed('{_you_can_only_create_a_new_subscription_role_by_filling_out_all}', HTTPS_SERVER_ADMIN . 'users/roles/');
                    exit();
                }
                $field1 .= 'purpose_' . $languagecode . ', title_' . $languagecode . ', ';
                $field2 .= "
                    '" . $sheel->db->escape_string($sheel->GPC['form']['purpose_' . $languagecode]) . "',
                    '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $languagecode]) . "',";
            }
           
            if ($sheel->GPC['form']['isdefault'] == '1') {
                $sheel->db->query("
                UPDATE " . DB_PREFIX . "roles
                SET isdefault = '0'
            ");
            }
            $sheel->db->query("
                    INSERT INTO " . DB_PREFIX . "roles
                    (roleid, " . $field1 . "custom, roletype, roleusertype, active, isdefault, isadmin)
                    VALUES(
                    NULL,
                    " . $field2 . "
                    '" . $sheel->db->escape_string($sheel->GPC['form']['custom']) . "',
                    'product',
                    '" . $sheel->GPC['form']['roleusertype'] . "',
                    '" . intval($sheel->GPC['form']['visible']) . "',
                    '" . intval($sheel->GPC['form']['isdefault']) . "',
                    '" . intval($sheel->GPC['form']['isadmin']) . "')
                ");
            refresh(HTTPS_SERVER_ADMIN . 'users/roles/');
            exit();
        } else {
            while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                $language['rslng'] = $rlanguagecode;
                $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
                $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
                $languages_role[] = $language_r;
                $form['title_' . $languagecode] = '';
                $form['purpose_' . $languagecode] = '';
                $form['flag_' . $languagecode] = $language['rslng'];
                $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
            }
            $form['custom'] = '';
            $form['roleusertypepulldown'] = $sheel->admincp->print_roleusertype_pulldown('all', false);
            $form['visible'] = 'checked="checked"';
        }

    }

    while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
        $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
        $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
        $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
        $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
        $form['flag_' . $languagecode] = $rlanguagecode;
        $languages_role[] = $language_r;
        $languages_permission_group[] = $language_pg;
    }
    $sql = $sheel->db->query("
        SELECT roleid, title_$slng AS title, purpose_$slng AS purpose, custom, roletype, roleusertype, active
        FROM " . DB_PREFIX . "roles
        WHERE roletype = 'product'
        ORDER BY roleid ASC
    ");
    if ($sheel->db->num_rows($sql) > 0) { // roles
        while ($resroles = $sheel->db->fetch_array($sql, DB_ASSOC)) {

            $resroles['rtitle'] = '<a href="' . HTTPS_SERVER_ADMIN . 'users/roles/update/' . $resroles['roleid'] . '/">' . stripslashes($resroles['title']) . '</a>';
            $resroles['rpurpose'] = stripslashes($resroles['purpose']);
            $resroles['active'] = ($resroles['active']) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="active" title="{_active}" />' : '';
            $sql_sub = $sheel->db->query("
                    SELECT user_id
                    FROM " . DB_PREFIX . "users
                    WHERE roleid = '" . $resroles['roleid'] . "'
                    LIMIT 1
                    ");
            $sheel->show['canremove_' . $resroles['roleid']] = ($sheel->db->num_rows($sql_sub) > 0) ? 0 : 1;
            $resroles['roleusertype'] = $sheel->admincp->print_roleusertype_pulldown($resroles['roleusertype'], true);
            $roles[] = $resroles;
        }
    }


    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $vars['settings'] = $settings;
    $sheel->template->fetch('main', 'users_roles.html', 1);

    $sheel->template->parse_loop(
        'main',
        array(
            'languages_role' => $languages_role,
            'roles' => $roles
        )
    );
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