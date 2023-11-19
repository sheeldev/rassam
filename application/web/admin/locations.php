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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Locations Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Locations Manager';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }


    $areanav = 'settings_locations';
    $currentarea = 'Location Manager';

    $languages = array();
    $sql_lang = $sheel->db->query("
        SELECT languagecode, title, textdirection
        FROM " . DB_PREFIX . "language
    ");
    if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save_locations') { // save country checkboxes
        $regionid = (isset($sheel->GPC['regionid']) ? intval($sheel->GPC['regionid']) : 0);
        if ($regionid <= 0) {
            $sheel->admincp->print_action_failed('{_select_region_filter}', urldecode($sheel->GPC['returnurl']));
        }
        $sheel->GPC['visible'] = (isset($sheel->GPC['visible']) ? $sheel->GPC['visible'] : array());
        $sheel->GPC['visible2'] = (isset($sheel->GPC['visible2']) ? $sheel->GPC['visible2'] : array());
        $sheel->db->query("
            UPDATE " . DB_PREFIX . "locations
            SET visible = '1',
            visible_shipping = '1'
            WHERE regionid = '" . intval($regionid) . "'
        ");
        $sql = $sheel->db->query("
            SELECT locationid
            FROM " . DB_PREFIX . "locations
            WHERE regionid = '" . intval($regionid) . "'
        ");
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            if (!in_array($res['locationid'], $sheel->GPC['visible'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations
                    SET visible = '0'
                    WHERE locationid = '" . $res['locationid'] . "'
                ");
            }
            if (!in_array($res['locationid'], $sheel->GPC['visible2'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations
                    SET visible_shipping = '0'
                    WHERE locationid = '" . $res['locationid'] . "'
                ");
            }
        }
        
        refresh(urldecode($sheel->GPC['returnurl']));
        exit();
    } else if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save_states') { // save states checkboxes
        $locationid = (isset($sheel->GPC['locationid']) ? intval($sheel->GPC['locationid']) : 0);
        if ($locationid <= 0) {
            $sheel->admincp->print_action_failed('{_select_country_filter}', urldecode($sheel->GPC['returnurl']));
            exit();
        }
        $sheel->GPC['visible'] = isset($sheel->GPC['visible']) ? $sheel->GPC['visible'] : array();
        $sheel->GPC['visible2'] = isset($sheel->GPC['visible2']) ? $sheel->GPC['visible2'] : array();
        $sheel->db->query("
            UPDATE " . DB_PREFIX . "locations_states
            SET visible = '1',
            visible_shipping = '1'
            WHERE locationid = '" . intval($locationid) . "'
        ");
        $sql = $sheel->db->query("
            SELECT id
            FROM " . DB_PREFIX . "locations_states
            WHERE locationid = '" . intval($locationid) . "'
        ");
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            if (!in_array($res['id'], $sheel->GPC['visible'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_states
                    SET visible = '0'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
            if (!in_array($res['id'], $sheel->GPC['visible2'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_states
                    SET visible_shipping = '0'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
            if (isset($sheel->GPC['state'][$res['id']]) and !empty($sheel->GPC['state'][$res['id']])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_states
                    SET state = '" . $sheel->db->escape_string(trim($sheel->GPC['state'][$res['id']])) . "'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
            if (isset($sheel->GPC['sc'][$res['id']]) and !empty($sheel->GPC['sc'][$res['id']])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_states
                    SET sc = '" . $sheel->db->escape_string(strtoupper(trim($sheel->GPC['sc'][$res['id']]))) . "'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
        }
        refresh(urldecode($sheel->GPC['returnurl']));
        exit();
    } else if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save_cities') { // save cities checkboxes
        $locationid = (isset($sheel->GPC['locationid']) ? intval($sheel->GPC['locationid']) : 0);
        if ($locationid <= 0) {
            $sheel->admincp->print_action_failed('{_select_country_filter}', urldecode($sheel->GPC['returnurl']));
            exit();
        }
        $state = (isset($sheel->GPC['state']) ? $sheel->GPC['state'] : '');
        if (empty($state)) {
            $sheel->admincp->print_action_failed('{_select_state_filter}', urldecode($sheel->GPC['returnurl']));
        }
        $sheel->GPC['visible'] = isset($sheel->GPC['visible']) ? $sheel->GPC['visible'] : array();
        $sheel->GPC['visible2'] = isset($sheel->GPC['visible2']) ? $sheel->GPC['visible2'] : array();
        $sheel->db->query("
            UPDATE " . DB_PREFIX . "locations_cities
            SET visible = '1',
            visible_shipping = '1'
            WHERE state = '" . $sheel->db->escape_string($state) . "'
                AND locationid = '" . intval($locationid) . "'
        ");
        $sql = $sheel->db->query("
            SELECT id
            FROM " . DB_PREFIX . "locations_cities
            WHERE state = '" . $sheel->db->escape_string($state) . "'
                AND locationid = '" . intval($locationid) . "'
        ");
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            if (!in_array($res['id'], $sheel->GPC['visible'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_cities
                    SET visible = '0'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
            if (!in_array($res['id'], $sheel->GPC['visible2'])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_cities
                    SET visible_shipping = '0'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
            if (isset($sheel->GPC['city'][$res['id']]) and !empty($sheel->GPC['city'][$res['id']])) {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "locations_cities
                    SET city = '" . $sheel->db->escape_string($sheel->GPC['city'][$res['id']]) . "'
                    WHERE id = '" . $res['id'] . "'
                ");
            }
        }
        if (isset($sheel->GPC['zip']) and is_array($sheel->GPC['zip'])) {
            foreach ($sheel->GPC['zip'] as $city => $zipcode) {
                if (!empty($zipcode)) {
                    $sheel->distance->update_city_zipcode($locationid, $state, $city, $zipcode);
                }
            }
        }
        if (isset($sheel->GPC['newcity']) and count($sheel->GPC['newcity']) > 0) {
            foreach ($sheel->GPC['newcity'] as $key => $cityname) {
                if ($cityname != '' and $state != '' and $locationid > 0) {
                    $sheel->GPC['newvisible'] = isset($sheel->GPC['newvisible']) ? 1 : 0;
                    $sheel->GPC['newvisible2'] = isset($sheel->GPC['newvisible2']) ? 1 : 0;
                    $sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "locations_cities (id, locationid, state, city, visible, visible_shipping)
                        VALUES (
                        NULL,
                        '" . intval($locationid) . "',
                        '" . $sheel->db->escape_string($state) . "',
                        '" . $sheel->db->escape_string($cityname) . "',
                        '" . intval($sheel->GPC['newvisible']) . "',
                        '" . intval($sheel->GPC['newvisible2']) . "'
                        )
                    ");
                    if (isset($sheel->GPC['newzip'][$key]) and !empty($sheel->GPC['newzip'][$key])) {
                        $sheel->distance->insert_city_zipcode($locationid, $state, $city, $sheel->GPC['newzip'][$key]);
                    }
                }
            }
        }
        refresh(urldecode($sheel->GPC['returnurl']));
        exit();
    }
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') { // update mode
        if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'country') {
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save' and isset($sheel->GPC['locationid']) and $sheel->GPC['locationid'] > 0) { // save country
                $location = '';
                $visible = (isset($sheel->GPC['form']['visible']) ? '1' : '0');
                $visible_shipping = (isset($sheel->GPC['form']['visible_shipping']) ? '1' : '0');
                $sql_lang = $sheel->db->query("
                    SELECT languagecode
                    FROM " . DB_PREFIX . "language
                ");
                while ($lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $slng = substr($lang['languagecode'], 0, 3);
                    if (isset($sheel->GPC['form']['title_' . $slng])) {
                        $location .= "location_$slng = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $slng]) . "', ";
                    }
                }
                if (!empty($location)) {
                    $location = substr($location, 0, -2);
                    $sheel->db->query("
                        UPDATE " . DB_PREFIX . "locations
                        SET $location,
                        visible = '" . intval($visible) . "',
                        visible_shipping = '" . intval($visible_shipping) . "',
                        cc = '" . $sheel->db->escape_string($sheel->GPC['form']['cc']) . "'
                        WHERE locationid = '" . intval($sheel->GPC['locationid']) . "'
                        LIMIT 1
                    ");
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Country location updated', 'The country location was successfully updated.');
                } else {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "failure\n" . $sheel->array2string($sheel->GPC), 'Country location not updated', 'There was a problem updating the country location.');
                }
                refresh(HTTPS_SERVER_ADMIN . 'settings/locations/update/country/' . intval($sheel->GPC['locationid']) . '/');
                exit();
            }
            $sql = $sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "locations
                WHERE locationid = '" . intval($sheel->GPC['locationid']) . "'
            ");
            $form['locationidx'] = '';
            if ($sheel->db->num_rows($sql) > 0) {
                $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                    $language['rslng'] = $rlanguagecode;
                    $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
                    $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
                    $languages[] = $language_r;
                    $form['title_' . $languagecode] = $res['location_' . $languagecode];
                    $form['flag_' . $languagecode] = $language['rslng'];
                    $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                    $form['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
                    $form['invisible'] = ($res['visible'] == '0') ? 'checked="checked"' : '';
                    $form['visible_shipping'] = ($res['visible_shipping'] == '1') ? 'checked="checked"' : '';
                    $form['invisible_shipping'] = ($res['visible_shipping'] == '0') ? 'checked="checked"' : '';
                }
                $form['groupname'] = $res['location_' . $_SESSION['sheeldata']['user']['slng']];
                $form['locationidx'] = $sheel->GPC['locationid'];
                $form['mode'] = 'country';
                $form['cc'] = $res['cc'];
            }
        } else if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'region') {
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') { // save region
                $location = '';
                $sql_lang = $sheel->db->query("
                    SELECT languagecode
                    FROM " . DB_PREFIX . "language
                ");
                while ($lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $slng = substr($lang['languagecode'], 0, 3);
                    if (isset($sheel->GPC['form']['title_' . $slng])) {
                        $location .= "region_$slng = '" . $sheel->db->escape_string($sheel->GPC['form']['title_' . $slng]) . "', ";
                    }
                }
                if (!empty($location)) {
                    $location = substr($location, 0, -2);
                    $sheel->db->query("
                        UPDATE " . DB_PREFIX . "locations_regions
                        SET $location
                        WHERE regionid = '" . intval($sheel->GPC['locationid']) . "'
                    ");
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Region updated', 'The region was successfully updated.');
                } else {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "failure\n" . $sheel->array2string($sheel->GPC), 'Region not updated', 'There was a problem updating the region location.');
                }
                refresh(HTTPS_SERVER_ADMIN . 'settings/locations/update/region/' . intval($sheel->GPC['locationid']) . '/');
                exit();
            }
            $sql = $sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "locations_regions
                WHERE regionid = '" . intval($sheel->GPC['locationid']) . "'
            ");
            $form['locationidx'] = '';
            if ($sheel->db->num_rows($sql) > 0) {
                $res = $sheel->db->fetch_array($sql, DB_ASSOC);
                while ($res_lang = $sheel->db->fetch_array($sql_lang, DB_ASSOC)) {
                    $languagecode = strtolower(substr($res_lang['languagecode'], 0, 3));
                    $rlanguagecode = strtoupper(substr($res_lang['languagecode'], 0, 2));
                    $language['rslng'] = $rlanguagecode;
                    $language_r['language'] = $language_p['language'] = $language_pg['language'] = $res_lang['title'];
                    $language_r['languagecode'] = $language_p['languagecode'] = $language_pg['languagecode'] = $languagecode;
                    $languages[] = $language_r;
                    $form['title_' . $languagecode] = $res['region_' . $languagecode];
                    $form['flag_' . $languagecode] = $language['rslng'];
                    $form['textdirection_' . $languagecode] = $res_lang['textdirection'];
                }
                $form['groupname'] = $res['region_' . $_SESSION['sheeldata']['user']['slng']];
                $form['locationidx'] = $sheel->GPC['locationid'];
                $form['mode'] = 'region';
            }
        }
    }
    $sheel->config['maxrowsadmin'] = 1000;
    $sheel->GPC['view'] = (isset($sheel->GPC['view']) ? o($sheel->GPC['view']) : '');
    $sheel->GPC['page'] = (!isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
    $limit = 'LIMIT ' . (($sheel->GPC['page'] - 1) * $sheel->config['maxrowsadmin']) . ',' . $sheel->config['maxrowsadmin'];
    $leftjoinsql = $viewsql = '';
    $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
    $regionid = $form['regionid'] = ((isset($sheel->GPC['regionid'])) ? intval($sheel->GPC['regionid']) : '');
    $country = ((isset($sheel->GPC['country'])) ? o($sheel->GPC['country']) : '');
    $state = $form['state'] = ((isset($sheel->GPC['state'])) ? o($sheel->GPC['state']) : '');
    $cid = $form['locationid'] = $sheel->common_location->fetch_country_id($country, $sheel->language->fetch_site_slng());
    $locations = array();
    $filterlocations = $filter = $filtercities = '';
    $number = 0;
    if (isset($sheel->GPC['regionid']) and $sheel->GPC['regionid'] > 0) { // countries
        $filterlocations .= "WHERE r.regionid = '" . intval($sheel->GPC['regionid']) . "'";
        if (isset($form['q']) and !empty($form['q'])) {
            $filter .= "AND l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $sheel->db->escape_string($form['q']) . "%' ";
        }
        $result = $sheel->db->query("
            SELECT l.*, r.*
            FROM " . DB_PREFIX . "locations l
            LEFT JOIN " . DB_PREFIX . "locations_regions r ON (l.regionid = r.regionid)
            $filterlocations
            $filter
            ORDER BY r.regionid
            $limit
        ");
        $numberrows = $sheel->db->query("
            SELECT l.*, r.*
            FROM " . DB_PREFIX . "locations l
            LEFT JOIN " . DB_PREFIX . "locations_regions r ON (l.regionid = r.regionid)
            $filterlocations
            $filter
            ORDER BY r.regionid
        ");
        $number = $sheel->db->num_rows($numberrows);
        if ($sheel->db->num_rows($result) > 0) {
            while ($res = $sheel->db->fetch_array($result, DB_ASSOC)) {
                $res['region'] = $res['region_' . $_SESSION['sheeldata']['user']['slng']];
                $res['country'] = $res['location_' . $_SESSION['sheeldata']['user']['slng']];
                $res['state'] = '';
                $res['city'] = '';
                $res['zipcode'] = '';
                $res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
                $res['visible2'] = ($res['visible_shipping'] == '1') ? 'checked="checked"' : '';
                $res['id'] = $res['locationid'];
                $locations[] = $res;
            }
        }
    } else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'states' and isset($sheel->GPC['country']) and !empty($sheel->GPC['country'])) { // states within country
        $filterlocations .= "AND l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $sheel->db->escape_string($sheel->GPC['country']) . "%'";
        if (isset($form['q']) and !empty($form['q'])) {
            $filter .= "AND s.state LIKE '%" . $sheel->db->escape_string($form['q']) . "%' ";
        }
        $result = $sheel->db->query("
            SELECT s.sc, s.visible, s.visible_shipping, r.regionid, l.locationid, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region, s.state, s.id
            FROM " . DB_PREFIX . "locations l
            LEFT JOIN " . DB_PREFIX . "locations_regions r on (r.regionid = l.regionid)
            LEFT JOIN " . DB_PREFIX . "locations_states s on (s.locationid = l.locationid)
            WHERE l.visible = '1'
            $filterlocations
            $filter
            ORDER BY r.regionid ASC, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " ASC, s.state ASC
            $limit
        ");
        $numberrows = $sheel->db->query("
            SELECT s.sc, s.visible, s.visible_shipping, r.regionid, l.locationid, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region, s.state, s.id
            FROM " . DB_PREFIX . "locations l
            LEFT JOIN " . DB_PREFIX . "locations_regions r on (r.regionid = l.regionid)
            LEFT JOIN " . DB_PREFIX . "locations_states s on (s.locationid = l.locationid)
            WHERE l.visible = '1'
            $filterlocations
            $filter
            ORDER BY r.regionid ASC, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " ASC, s.state ASC
        ");
        $number = $sheel->db->num_rows($numberrows);
        if ($sheel->db->num_rows($result) > 0) {
            while ($res = $sheel->db->fetch_array($result, DB_ASSOC)) {
                $res['city'] = '';
                $res['zipcode'] = '';
                $res['state'] = '<input type="text" class="draw-input-small" name="state[' . $res['id'] . ']" value="' . o($res['state']) . '">';
                $res['sc'] = '<input type="text" class="draw-input-small" name="sc[' . $res['id'] . ']" value="' . o($res['sc']) . '">';
                $res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
                $res['visible2'] = ($res['visible_shipping'] == '1') ? 'checked="checked"' : '';
                $locations[] = $res;
            }
        }
    } else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'cities' and isset($sheel->GPC['country']) and !empty($sheel->GPC['country']) and isset($sheel->GPC['state']) and !empty($sheel->GPC['state'])) { // cities within state
        $filterlocations .= "AND l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $sheel->db->escape_string($sheel->GPC['country']) . "%' AND s.state LIKE '%" . $sheel->db->escape_string($sheel->GPC['state']) . "%'";
        if (isset($form['q']) and !empty($form['q'])) {
            $filter .= "AND c.city LIKE '%" . $sheel->db->escape_string($form['q']) . "%' ";
        }
        $result = $sheel->db->query("
            SELECT s.sc, s.state, c.visible, c.visible_shipping, l.locationid, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region, r.regionid, c.city, c.id
            FROM " . DB_PREFIX . "locations_states s
            LEFT JOIN " . DB_PREFIX . "locations_cities c ON (c.state = s.state)
            LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = s.locationid)
            LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
            WHERE l.visible = '1'
                AND c.city != ''
            $filterlocations
            $filter
            ORDER BY s.state ASC
            $limit
        ");
        $numberrows = $sheel->db->query("
            SELECT s.sc, s.state, c.visible, c.visible_shipping, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region, c.city, c.id
            FROM " . DB_PREFIX . "locations_states s
            LEFT JOIN " . DB_PREFIX . "locations_cities c ON (c.state = s.state)
            LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = s.locationid)
            LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
            WHERE l.visible = '1'
                AND c.city != ''
            $filterlocations
            $filter
            ORDER BY s.state ASC
        ");
        $number = $sheel->db->num_rows($numberrows);
        if ($sheel->db->num_rows($result) > 0) {
            while ($res = $sheel->db->fetch_array($result, DB_ASSOC)) {
                if (in_array($cid, $sheel->distance->accepted_countries)) {
                    $res['zipcode'] = $sheel->distance->fetch_zipcode_from_city($cid, $res['state'], $res['city']);
                    $res['zipcode'] = '<input type="text" class="draw-input-small" name="zip[' . o($res['city']) . ']" value="' . o($res['zipcode']) . '">';
                } else {
                    $res['zipcode'] = '-';
                }
                $res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
                $res['visible2'] = ($res['visible_shipping'] == '1') ? 'checked="checked"' : '';
                $res['city'] = '<input type="text" class="draw-input-small" name="city[' . $res['id'] . ']" value="' . o($res['city']) . '">';
                $locations[] = $res;
            }
        }
    }
    $pageurl = PAGEURL;
    $prevnext = $sheel->admincp->pagination($number, $sheel->config['maxrowsadmin'], $sheel->GPC['page'], $pageurl);
    $filter_options = array(
        '' => '{_select_filter} &ndash;',
        'orderid' => '{_order_id}',
        'itemid' => '{_item_id}',
        'title' => '{_title}',
        'leftby' => '{_feedback_left_by}',
        'leftfor' => '{_feedback_left_for}',
        'comments' => '{_search_comments}'
    );
    $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
    $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
    $form['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
    $form['number'] = $number;
    unset($filter_options);
    $region_options = array('' => 'Select region &ndash;');
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
    $form['region_pulldown'] = $sheel->construct_pulldown('regionid', 'regionid', $region_options, (isset($sheel->GPC['regionid']) ? $sheel->GPC['regionid'] : ''), 'class="draw-select"');
    if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'states') { // viewing states, show country pulldown
        $form['country_pulldown'] = $sheel->common_location->construct_country_pulldown($cid, $country, 'country', true, '', false, false, false, '', false, '', '', '', '', '', false, $regionid);
    } else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'cities') {
        $form['country_pulldown'] = $sheel->common_location->construct_country_pulldown($cid, $country, 'country', false, 'state');
        $form['state_pulldown'] = '<span id="stateid">' . $sheel->common_location->construct_state_pulldown($cid, $state, 'state', false, true) . '</span>';
    }
    $sheel->template->fetch('main', 'settings_locations.html', 1);
    $sheel->template->parse_loop(
        'main',
        array(
            'regions' => (isset($regions) ? $regions : ''),
            'locations' => (isset($locations) ? $locations : ''),
            'languages' => (isset($languages) ? $languages : ''),
        )
    );
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array()),
            'ilpage' => $sheel->ilpage
        )
    );
    $vars = array(
        'sidenav' => $sidenav,
        'areanav' => (isset($areanav) ? $areanav : ''),
        'currentarea' => (isset($currentarea) ? $currentarea : ''),
        'prevnext' => (isset($prevnext) ? $prevnext : ''),
        'q' => (isset($q) ? $q : '')
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>