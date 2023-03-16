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
        'font-awesome',
        'glyphicons',
        'chartist',
        'growl',
        'balloon'
    )
);
$sheel->template->meta['areatitle'] = 'Admin CP | {_currency_manager}';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | {_currency_manager}';
if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    $areanav = 'settings_currency';
    $currentarea = '{_currency_manager}';
    $currencies = $form = array();
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') { // remove currency

        if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
            if ($sheel->config['globalserverlocale_defaultcurrency'] != $sheel->GPC['xid']) {
                $sheel->db->query("
						DELETE FROM " . DB_PREFIX . "currency
						WHERE currency_id = '" . intval($sheel->GPC['xid']) . "'
					");
                $sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET currencyid = '" . $sheel->config['globalserverlocale_defaultcurrency'] . "'
						WHERE currencyid = '" . intval($sheel->GPC['xid']) . "'
					");
                $sheel->cachecore->delete('currencies');
                die(json_encode(array('response' => 1, 'message' => 'Successfully deleted currency ID ' . $sheel->GPC['xid'])));
            } else {
                die(json_encode(array('response' => 0, 'message' => '{_you_cannot_delete_this_currency_because_it_appears_it_is_associated_as_the_main_marketplace_currency}')));
            }
        } else {
            $sheel->template->templateregistry['message'] = 'No currency was selected.  Please try again.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'default') { // update marketplace to this new currency

        if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . intval($sheel->GPC['xid']) . "'
					WHERE name = 'globalserverlocale_defaultcurrency'
					LIMIT 1
				");
            die(json_encode(array('response' => 1, 'message' => 'Successfully set default marketplace currency to ID ' . $sheel->GPC['xid'])));
        } else {
            $sheel->template->templateregistry['message'] = 'No currency was selected.  Please try again.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'defaultusers') { // update all users to use this currency

        if (isset($sheel->GPC['xid']) and $sheel->GPC['xid'] > 0) {
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET currencyid = '" . intval($sheel->GPC['xid']) . "'
				");
            die(json_encode(array('response' => 1, 'message' => 'Successfully set all customers on currency ID ' . $sheel->GPC['xid'])));
        } else {
            $sheel->template->templateregistry['message'] = 'No currency was selected.  Please try again.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'save') { // update currencies
        $query = '';
        foreach ($sheel->GPC['currency'] as $currencyid => $array) {
            if (!isset($sheel->GPC['currency'][$currencyid]['iscrypto'])) {
                $array['iscrypto'] = 0;
            } else {
                $array['iscrypto'] = 1;
            }
            $currencyidx = $currencyid;
            foreach ($array as $key => $value) {
                $query .= "" . $sheel->db->escape_string($key) . " = '" . $sheel->db->escape_string($value) . "', ";
            }
            $query .= "`time` = '" . DATETIME24H . "' WHERE currency_id = '" . intval($currencyidx) . "' LIMIT 1";
            $sheel->db->query("
					UPDATE " . DB_PREFIX . "currency
					SET " . $query . "
				");
            $query = '';
        }
        if (!empty($sheel->GPC['newcurrency']['currency_name']) and !empty($sheel->GPC['newcurrency']['rate']) and !empty($sheel->GPC['newcurrency']['currency_abbrev'])) {
            if (!isset($sheel->GPC['newcurrency']['iscrypto'])) {
                $sheel->GPC['newcurrency']['iscrypto'] = 0;
            } else {
                $sheel->GPC['newcurrency']['iscrypto'] = 1;
            }
            $sql = $sheel->db->query("
					INSERT INTO " . DB_PREFIX . "currency
					(currency_id, currency_abbrev, currency_name, currency_subunit, rate, time, isdefault, symbol_left, symbol_right, symbol_local, decimal_point, thousands_point, decimal_places, decimal_places_local, iscrypto)
					VALUES(
					NULL,
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['currency_abbrev']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['currency_name']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['currency_subunit']) . "',
					'" . $sheel->db->escape_string(floatval($sheel->GPC['newcurrency']['rate'])) . "',
					'" . DATETIME24H . "',
					'0',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['symbol_left']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['symbol_right']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['symbol_local']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['decimal_point']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['thousands_point']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['decimal_places']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['decimal_places_local']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['newcurrency']['iscrypto']) . "')
				");
        }
        $sheel->cachecore->delete('currencies');
        refresh(HTTPS_SERVER_ADMIN . 'currency/');
        exit();
    } else { // all currencies list
        $sheel->show['nocurrencies'] = false;
        $result = $sheel->db->query("
				SELECT currency_id, currency_name, currency_subunit, rate, currency_abbrev, symbol_left, symbol_right, symbol_local, decimal_point, thousands_point, decimal_places, decimal_places_local, isdefault, iscrypto
				FROM " . DB_PREFIX . "currency
			");
        if ($sheel->db->num_rows($result) > 0) {
            while ($res = $sheel->db->fetch_array($result, DB_ASSOC)) {
                $res['currencyname'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_name]" value="' . stripslashes($res['currency_name']) . '" size="20" class="draw-input" />';
                $res['subunit'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_subunit]" value="' . stripslashes($res['currency_subunit']) . '" size="10" class="draw-input" />';
                $res['rate'] = '<input type="text" name="currency[' . $res['currency_id'] . '][rate]" value="' . stripslashes($res['rate']) . '" size="10" class="draw-input" />';
                $res['abbrev'] = '<input type="text" name="currency[' . $res['currency_id'] . '][currency_abbrev]" value="' . stripslashes($res['currency_abbrev']) . '" size="4" class="draw-input" />';
                $res['symbolleft'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_left]" value="' . stripslashes($res['symbol_left']) . '" size="4" class="draw-input" />';
                $res['symbolright'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_right]" value="' . stripslashes($res['symbol_right']) . '" size="4" class="draw-input" />';
                $res['symbollocal'] = '<input type="text" name="currency[' . $res['currency_id'] . '][symbol_local]" value="' . stripslashes($res['symbol_local']) . '" size="4" class="draw-input" />';
                $res['decimalpoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_point]" value="' . stripslashes($res['decimal_point']) . '" size="2" class="draw-input" />';
                $res['thousandspoint'] = '<input type="text" name="currency[' . $res['currency_id'] . '][thousands_point]" value="' . stripslashes($res['thousands_point']) . '" size="2" class="draw-input" />';
                $res['decimalplaces'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places]" value="' . stripslashes($res['decimal_places']) . '" size="2" class="draw-input" />';
                $res['decimalplaceslocal'] = '<input type="text" name="currency[' . $res['currency_id'] . '][decimal_places_local]" value="' . stripslashes($res['decimal_places_local']) . '" size="2" class="draw-input" />';
                $iscrypto = $res['iscrypto'];
                $res['iscrypto'] = '<input type="checkbox" name="currency[' . $res['currency_id'] . '][iscrypto]" value="' . intval($res['iscrypto']) . '"' . (($res['iscrypto'] == 1) ? ' checked="checked"' : '') . ' />';
                $sql = $sheel->db->query("
						SELECT project_id
						FROM " . DB_PREFIX . "projects
						WHERE currencyid = '" . $res['currency_id'] . "'
							AND status = 'open'
					");
                $num = $sheel->db->num_rows($sql);

                $res['action'] = '<ul class="segmented">' . (($num == 0)
                    ? '<li><a href="javascript:;"' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '' : ' data-bind-event-click="acp_confirm(\'default\', \'{_set_currency_marketplace_default}\', \'{_set_currency_marketplace_default_message::' . $res['currency_abbrev'] . '}\', \'' . $res['currency_id'] . '\', 1, \'\', \'\')"') . ' class="btn btn-slim btn--icon" title="' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '{_default_currency}' : '{_set_as_default_currency}') . '"><span class="ico-16-svg halflings halflings-star draw-icon' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '--sky-darker' : '') . '" aria-hidden="true"></span></a></li><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'defaultusers\', \'{_set_currency_default_everyone}\', \'{_set_currency_default_everyone_message::' . $res['currency_abbrev'] . '}\', \'' . $res['currency_id'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="{_set_this_currency_default_all_users}"><span class="ico-16-svg halflings halflings-user draw-icon" aria-hidden="true"></span></a></li><li><a href="javascript:;"' . (($iscrypto and $sheel->config['globalserverlocale_defaultcryptocurrency'] == $res['currency_id']) ? '' : ' data-bind-event-click="acp_confirm(\'delete\', \'{_delete_selected_currency}\', \'{_delete_selected_currency_message::' . $res['currency_abbrev'] . '}\', \'' . $res['currency_id'] . '\', 1, \'\', \'\')"') . ' class="btn btn-slim btn--icon" title="' . (($iscrypto and $sheel->config['globalserverlocale_defaultcryptocurrency'] == $res['currency_id']) ? 'Cryptocurrency is set as default' : '{_delete}') . '"><span class="ico-16-svg halflings halflings-trash draw-icon' . (($iscrypto and $sheel->config['globalserverlocale_defaultcryptocurrency'] == $res['currency_id']) ? '--sky-darker' : '') . '" aria-hidden="true"></span></a></li>'
                    : '<li><a href="javascript:;"' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '' : ' data-bind-event-click="acp_confirm(\'default\', \'{_set_currency_marketplace_default}\', \'{_set_currency_marketplace_default_message::' . $res['currency_abbrev'] . '}\', \'' . $res['currency_id'] . '\', 1, \'\', \'\')"') . ' class="btn btn-slim btn--icon" title="' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '{_default_currency}' : '{_set_as_default_currency}') . '"><span class="ico-16-svg halflings halflings-star draw-icon' . (($sheel->config['globalserverlocale_defaultcurrency'] == $res['currency_id']) ? '--sky-darker' : '') . '" aria-hidden="true"></span></a></li><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'defaultusers\', \'{_set_currency_default_everyone}\', \'{_set_currency_default_everyone_message::' . $res['currency_abbrev'] . '}\', \'' . $res['currency_id'] . '\', 1, \'\', \'\')" class="btn btn-slim btn--icon" title="{_set_this_currency_default_all_users}"><span class="ico-16-svg halflings halflings-user draw-icon" aria-hidden="true"></span></a></li><li><a href="javascript:;" class="btn btn-slim btn--icon" title="{_currently_in_use}"><span class="ico-16-svg halflings halflings-trash draw-icon--sky-darker" aria-hidden="true"></span></a></li>') . '</ul>';

                $currencies[] = $res;
            }
        } else {
            $sheel->show['nocurrencies'] = true;
        }
        $form['currencyname'] = '<input type="text" name="newcurrency[currency_name]" size="20" class="draw-input" placeholder="{_title}" />';
        $form['subunit'] = '<input type="text" name="newcurrency[currency_subunit]" size="10" class="draw-input"  placeholder="Sub unit" />';
        $form['rate'] = '<input type="text" name="newcurrency[rate]" size="10" class="draw-input" placeholder="{_rate}" />';
        $form['abbrev'] = '<input type="text" name="newcurrency[currency_abbrev]" size="4" class="draw-input" placeholder="Abbrv." />';
        $form['symbolleft'] = '<input type="text" name="newcurrency[symbol_left]" size="4" class="draw-input" placeholder="{_symbol}" />';
        $form['symbolright'] = '<input type="text" name="newcurrency[symbol_right]" size="4" class="draw-input" />';
        $form['symbollocal'] = '<input type="text" name="newcurrency[symbol_local]" size="4" class="draw-input" />';
        $form['decimalpoint'] = '<input type="text" name="newcurrency[decimal_point]" size="2" value="." class="draw-input" />';
        $form['thousandspoint'] = '<input type="text" name="newcurrency[thousands_point]" size="2" value="," class="draw-input" />';
        $form['decimalplaces'] = '<input type="text" name="newcurrency[decimal_places]" size="2" value="2" class="draw-input" />';
        $form['decimalplaceslocal'] = '<input type="text" name="newcurrency[decimal_places_local]" size="2" value="2" class="draw-input" />';
        $form['iscrypto'] = '<input type="checkbox" name="newcurrency[iscrypto]" value="1" />';
    }
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $sheel->template->fetch('main', 'currency.html', 1);
    $sheel->template->parse_loop('main', array('currencies' => $currencies), false);
    $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => $form));
    $sheel->template->pprint('main', $vars);
    exit();
}
?>