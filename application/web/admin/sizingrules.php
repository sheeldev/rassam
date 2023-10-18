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

    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {

    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            //echo ($sheel->GPC['active_rules']);
            echo ($sheel->GPC['form']['impactvalue_'.$sheel->GPC['active_rules']]);
            echo ($sheel->GPC['valuelow_'.$sheel->GPC['active_rules']]);
            echo ($sheel->GPC['valuehigh_'.$sheel->GPC['active_rules']]);
            echo ($sheel->GPC['form']['uom_'.$sheel->GPC['active_rules']]);
            $type = $sheel->GPC['type'];
            if (is_array($type)) {
                foreach ($type as $key => $value) {
                    echo $value .'<BR>';
                }
                die('array');
            }
            else {
                die('test');
            }
            
        }

        //$sheel->common_location->construct_country_pulldown($countryid, $geodata['country'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid'),
        $gender = array('Male' => '{_male}', 'Female' => '{_female}');
        $form['gender'] = $sheel->common_sizingrule->construct_gender_pulldown('Male', 'form[gender]',false, 'draw-select', 'type[]','type-wrapper',false);
        $form['type'] = $sheel->common_sizingrule->construct_type_checkbox('Male', true);
        $form['impact'] = $sheel->common_sizingrule->construct_impact_pulldown('', 'form[impact]',false, 'draw-select', 'form[impactvalue_1]','value-wrapper',false);
        $form['impactvalue'] = $sheel->common_sizingrule->construct_impactvalue_pulldown('Fit', 'form[impactvalue_1]','', false, false, 'draw-select');
        $form['uom'] = $sheel->common_sizingrule->construct_uom_pulldown('form[uom_1]','CM', false, false, 'draw-select');
        $regions = array();
        $sql = $sheel->db->query("
        SELECT regionid, region_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
        FROM " . DB_PREFIX . "locations_regions
    ");
        if ($sheel->db->num_rows($sql) > 0) {
            while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $row['id'] = $row['regionid'];
                $row['traffic'] = '0';
                $row['customers'] = '0';

                $region_options[$row['regionid']] = o($row['title']);
                $regions[] = $row;
            }
        }


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
            SELECT distinct code,  mccode, mcname, gender
            FROM " . DB_PREFIX . "size_rules
            WHERE $where
            ORDER BY code
        ");
        $count = 0;
        if ($sheel->db->num_rows($sql) > 0) {
            while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $count++;
                $sqla = $sheel->db->query("
                    SELECT COUNT(*) AS totalrules
                    FROM " . DB_PREFIX . "size_rules
                    WHERE code = '" . $row['code'] . "'
                        AND active = '1'
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
                        AND active = '1'
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
                    WHERE code = '" . $row['code'] . "'
                ");
                if ($sheel->db->num_rows($sqlsetup) == 0) {
                    $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/">{_set_up}</a>';
                } else {
                    $row['access'] = ' <a href="' . HTTPS_SERVER_ADMIN . 'settings/sizingrules/update/' . $row['code'] . '/">{_update}</a>';
                }
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