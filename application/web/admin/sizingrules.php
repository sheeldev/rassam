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
    'addition',
    'vendor' => array(
        'growl',
        'font-awesome',
        'glyphicons',
        'chartist',
        'balloon',
        'growl'
    )
);


if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $sheel->template->meta['jsinclude']['header'][] = 'vendor/upclick';
    $sheel->template->meta['jsinclude']['footer'][] = 'admin_sizingrules';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
        $sheel->db->query("
        DELETE FROM " . DB_PREFIX . "size_rules
        WHERE code = '" . $sheel->GPC['code'] . "' AND gender = '" . $sheel->GPC['gender'] . "'
        ");
        $sheel->template->templateregistry['message'] = '{_successfully_deleted_sizerule}';
        die(
            json_encode(
                array(
                    'response' => '1',
                    'message' => $sheel->template->parse_template_phrases('message')
                )
            )
        );
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Sizing Rules - Update</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Sizing Rules - Update';
        $areanav = 'settings_sizingrules';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/">Sizing Rules</a> / </span>' . $sheel->GPC['code'];

        if (isset($sheel->GPC['gender']) and isset($sheel->GPC['impact']) and $sheel->GPC['gender'] != '' and $sheel->GPC['impact'] != '') {
            if ($sheel->GPC['gender'] == 'Male') {
                $where = "gender = 'Male'";
            } else if ($sheel->GPC['gender'] == 'Female') {
                $where = "gender = 'Female'";
            }
    
            $sql = $sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "size_rules
                WHERE code ='".  $sheel->GPC['code'] . "' AND $where
                ORDER BY  type, mvaluelow 
            ");
            $count = 0;
            $impact=$sheel->GPC['impact'];
            $impactvaluearray = $sheel->common_sizingrule->construct_impactvalue_pulldown($impact, 'form[impactvalue]', '', false, false, 'draw-select', true);
            if ($sheel->db->num_rows($sql) > 0) {
                while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) { 
                    $count++;    
                    if ($sheel->db->num_rows($sqlsetup) == 0) {
                        $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/?gender='.$row['gender'].'">{_set_up}</a>';
                    } else {
                        $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/?gender='.$row['gender'].'">{_update}</a>';
                    }
                    $row['impactvaluefinal'] = $sheel->construct_pulldown('form[impactvalue_'.$count.']', 'form[impactvalue_'.$count.']', $impactvaluearray, $row['impactvalue'], 'class="draw-select"');
                    $row['rulenumber'] = $count;
                    $rules_rows[] = $row;
                }
            }
        }
        else {

        }


    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            $type = $sheel->GPC['type'];
            $rulescount = $sheel->GPC['active_rules'];
            if (is_array($type)) {
                foreach ($type as $key => $value) {
                    for ($x = 1; $x <= $rulescount; $x++) {
                        $sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "size_rules
                            (id, code, iscalculated, mcformula, mccode, mcname, mvaluelow, mvaluehigh, uom, gender, type, impact, impactvalue, `rank`, priority, active)
                            VALUES
                            (NULL,
                            '" . $sheel->db->escape_string($sheel->GPC['code']) . "',
                            '" . $sheel->GPC['isformula'] . "',
                            '" . $sheel->db->escape_string($sheel->GPC['mformula']) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['mcode']) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['mname']) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['valuelow_' . $x]) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['valuehigh_' . $x]) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['form']['uom_' . $x]) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['form']['gender']) . "',
                            '" . $sheel->db->escape_string($value) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['form']['impact']) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['form']['impactvalue_' . $x]) . "',
                            '" . $sheel->db->escape_string($sheel->GPC['rank_' . $x]) . "',
                            '" . intval($sheel->GPC['priority']) . "',
                            '1')
                        ");
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'New size rule line added', 'A new size rule line was added to the portal');

                    }
                }
            } else {

            }
            refresh(HTTPS_SERVER_ADMIN . 'settings/sizingrules/');
        }

        //$sheel->common_location->construct_country_pulldown($countryid, $geodata['country'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid'),
        $gender = array('Male' => '{_male}', 'Female' => '{_female}');
        $form['gender'] = $sheel->common_sizingrule->construct_gender_pulldown('Male', 'form[gender]', false, 'draw-select', 'type[]', 'type-wrapper', false);
        $form['type'] = $sheel->common_sizingrule->construct_type_checkbox('Male', true);
        $form['impact'] = $sheel->common_sizingrule->construct_impact_pulldown('', 'form[impact]', false, 'draw-select', 'form[impactvalue_1]', 'value-wrapper', false);
        $form['impactvalue'] = $sheel->common_sizingrule->construct_impactvalue_pulldown('Fit', 'form[impactvalue_1]', '', false, false, 'draw-select');
        $form['uom'] = $sheel->common_sizingrule->construct_uom_pulldown('form[uom_1]', 'CM', false, false, 'draw-select');
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Sizing Rules - Add</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Sizing Rules - Add';
        $areanav = 'settings_sizingrules';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/">Sizing Rules</a> / </span> {_add}';

    } else {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Sizing Rules</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Sizing Rules';
        $areanav = 'settings_sizingrules';
        $currentarea = 'Sizing Rules';
        $where = "gender = 'Male'";
        if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'male') {
            $where = "gender = 'Male'";
        } else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'female') {
            $where = "gender = 'Female'";
        }

        $sql = $sheel->db->query("
            SELECT distinct code,  mccode, mcname, gender, priority, impact, iscalculated, mcformula
            FROM " . DB_PREFIX . "size_rules
            WHERE $where
            ORDER BY  priority ASC
        ");
        $count = 0;
        if ($sheel->db->num_rows($sql) > 0) {
            while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $count++;
                $sqla = $sheel->db->query("
                    SELECT COUNT(*) AS totalrules
                    FROM " . DB_PREFIX . "size_rules
                    WHERE code = '" . $row['code'] . "'
                        AND active = '1' AND $where
                ");
                if ($sheel->db->num_rows($sqla) > 0) {
                    $resactive = $sheel->db->fetch_array($sqla, DB_ASSOC);
                    $row['totalrules'] = $resactive['totalrules'];
                } else {
                    $row['totalrules'] = '0';
                }

                $sqla = $sheel->db->query("
                    SELECT COUNT(distinct type) AS totaltypes
                    FROM " . DB_PREFIX . "size_rules
                    WHERE code = '" . $row['code'] . "'
                        AND active = '1' AND $where
                ");
                if ($sheel->db->num_rows($sqla) > 0) {
                    $resactive = $sheel->db->fetch_array($sqla, DB_ASSOC);
                    $row['totaltypes'] = $resactive['totaltypes'];
                } else {
                    $row['totaltypes'] = '0';
                }
                $sqlsetup = $sheel->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "size_rules
                    WHERE code = '" . $row['code'] . "' AND $where
                ");
                if ($sheel->db->num_rows($sqlsetup) == 0) {
                    $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/?gender='.$row['gender'].'&impact='.$row['impact'].'">{_set_up}</a>';
                } else {
                    $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/?gender='.$row['gender'].'&impact='.$row['impact'].'">{_update}</a>';
                }
                $row['iscalculated'] = ($row['iscalculated'] == '1') ? 'Yes' : 'No';
                $row['mcformula'] = ($row['mcformula'] == '0') ? '-' : $row['mcformula'];
                $rules_rows[] = $row;
            }
        }
    }


    $form['count'] = $count;
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $vars['settings'] = $settings;
    $vars['url'] = $_SERVER['REQUEST_URI'];
    $sheel->template->fetch('main', 'settings_sizingrules.html', 1);

    $sheel->template->parse_loop(
        'main',
        array(
            'rules_rows' => $rules_rows
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