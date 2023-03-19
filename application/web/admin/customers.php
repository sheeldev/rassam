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
        'admin_customers',
        'inline',
        'vendor/chartist',
        'vendor/growl'
    ),
    'footer' => array()
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
$sheel->template->meta['areatitle'] = 'Admin CP | Customers';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Customers';

if (($sidenav = $sheel->cache->fetch("sidenav_customers")) === false) {
    $sidenav = $sheel->admincp_nav->print('customers');
    $sheel->cache->store("sidenav_customers", $sidenav);
}

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {


    $q = ((isset($sheel->GPC['q'])) ? o($sheel->GPC['q']) : '');
    $sheel->GPC['page'] = (!isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
    $fview = $sheel->GPC['view'];
    $vars = array(
        'sidenav' => $sidenav,
        'q' => $q,
        'fview' => $fview,
    );


    if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bc') {
       
        $customers = array();
        $searchcondition = '';
        $searchfilters = array(
            'name',
            'account'
        );
        $dynamics = $sheel->dynamics->init_dynamics(
            array(
                "base_url" => "",
                "authEndPoint" => "https://login.microsoftonline.com/c2f8c1dc-1645-49aa-9793-f6098a0f4d92/oauth2/v2.0/token",
                'tokenEndPoint' => "https://login.microsoftonline.com/c2f8c1dc-1645-49aa-9793-f6098a0f4d92/oauth2/v2.0/token",
                'crmApiEndPoint' => "https://api.businesscentral.dynamics.com/v2.0/c2f8c1dc-1645-49aa-9793-f6098a0f4d92/Production/ODataV4/Company('AVER')/Customers",
                "clientID" => "80210d2b-7710-413a-8c24-1db97ce635d4",
                "clientSecret" => "3f.8Q~P5e_aN0-zu8lQQz-O5Fuby5Yw35qZVgbk1"
            )
        );

        
        
        $pagination = '&$skip='.($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'].'&$top='.$sheel->config['globalfilters_maxrowsdisplay'];


        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'name': {
                        $searchcondition = '$filter=contains( Name, \''. $sheel->db->escape_string($q).'\')';
                        break;
                    }

                case 'account': {
                        $searchcondition = '$filter=No eq \''.$sheel->db->escape_string($q).'\'';
                        break;
                    }
            }
        }
        

        //$contactsResponse = $sheel->dynamics->select('?$select=No&$filter=No eq \'AVR-CF00001\'');
        $contactsResponse = $sheel->dynamics->select('?$count=true&'.$searchcondition. $pagination);
        $pageurl = PAGEURL;
        

        if ($contactsResponse->isSuccess()) {
            $customers = $contactsResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $contactsResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }
        $vars['prevnext'] = $sheel->admincp->pagination($contactsResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl);

        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'account' => '{_account}',
            'name' => '{_name}'
        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
        

        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $sheel->template->fetch('main', 'customers-bc.html', 1);
        $sheel->template->parse_loop(
            'main',
            array(
                'customers' => $customers
            )
        );
        $sheel->template->parse_hash(
            'main',
            array(
                'ilpage' => $sheel->ilpage,
                'form' => $form
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();


    }
     else {
        $areanav = 'customers_customers';
        $vars['areanav'] = $areanav;
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'marksuspended') { // mark suspended

            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'suspended');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(
                    json_encode(
                        array(
                            'response' => '2',
                            'success' => $sheel->template->parse_template_phrases('success'),
                            'errors' => $sheel->template->parse_template_phrases('errors'),
                            'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                            'successids' => $response['successids'],
                            'failedids' => $response['failedids']
                        )
                    )
                );
            } else {

                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(
                    json_encode(
                        array(
                            'response' => '0',
                            'message' => $sheel->template->parse_template_phrases('message')
                        )
                    )
                );
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markcancelled') { // mark cancelled
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'cancelled');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(
                    json_encode(
                        array(
                            'response' => '2',
                            'success' => $sheel->template->parse_template_phrases('success'),
                            'errors' => $sheel->template->parse_template_phrases('errors'),
                            'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                            'successids' => $response['successids'],
                            'failedids' => $response['failedids']
                        )
                    )
                );
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(
                    json_encode(
                        array(
                            'response' => '0',
                            'message' => $sheel->template->parse_template_phrases('message')
                        )
                    )
                );
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markbanned') { // mark banned
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'banned');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(
                    json_encode(
                        array(
                            'response' => '2',
                            'success' => $sheel->template->parse_template_phrases('success'),
                            'errors' => $sheel->template->parse_template_phrases('errors'),
                            'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                            'successids' => $response['successids'],
                            'failedids' => $response['failedids']
                        )
                    )
                );
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(
                    json_encode(
                        array(
                            'response' => '0',
                            'message' => $sheel->template->parse_template_phrases('message')
                        )
                    )
                );
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markactive') { // mark active
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'active');

                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(
                    json_encode(
                        array(
                            'response' => '2',
                            'success' => $sheel->template->parse_template_phrases('success'),
                            'errors' => $sheel->template->parse_template_phrases('errors'),
                            'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                            'successids' => $response['successids'],
                            'failedids' => $response['failedids']
                        )
                    )
                );
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(
                    json_encode(
                        array(
                            'response' => '0',
                            'message' => $sheel->template->parse_template_phrases('message')
                        )
                    )
                );
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markdeleted') { // mark deleted
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'deleted');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(
                    json_encode(
                        array(
                            'response' => '2',
                            'success' => $sheel->template->parse_template_phrases('success'),
                            'errors' => $sheel->template->parse_template_phrases('errors'),
                            'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                            'successids' => $response['successids'],
                            'failedids' => $response['failedids']
                        )
                    )
                );
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(
                    json_encode(
                        array(
                            'response' => '0',
                            'message' => $sheel->template->parse_template_phrases('message')
                        )
                    )
                );
            }
        }

        $searchfilters = array(
            'customer',
            'date',
            'cphone',
            'cemail'
        );
        $searchviews = array(
            'active',
            'moderated',
            'banned',
            'suspended',
            'cancelled',
            'deleted'
        );
        $searchcondition = $searchview = '';

        if (isset($sheel->GPC['view']) and !empty($sheel->GPC['view']) and in_array($sheel->GPC['view'], $searchviews)) {
            switch ($sheel->GPC['view']) {
                case 'active': {
                        $searchview = " WHERE (c.status = 'active')";
                        break;
                    }
                case 'banned': {
                        $searchview = " WHERE (c.status = 'banned')";
                        break;
                    }
                case 'cancelled': {
                        $searchview = " WHERE (c.status = 'cancelled')";
                        break;
                    }
                case 'deleted': {
                        $searchview = " WHERE (c.status = 'deleted')";
                        break;
                    }
                case 'suspended': {
                        $searchview = " WHERE (c.status = 'suspended')";
                        break;
                    }
                case 'moderated': {
                        $searchview = " WHERE (c.status = 'moderated')";
                        break;
                    }
            }
        } else {
            $searchview = " WHERE (c.status = 'active')";
        }

        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'name': {
                        $searchcondition = "AND (c.customername Like '%" . $sheel->db->escape_string($q) . "%' OR c.description Like '%" . $sheel->db->escape_string($q) . "%')";
                        break;
                    }

                case 'date': {
                        $searchcondition = "AND (DATE(c.date_added) = '" . $sheel->db->escape_string($q) . "')";
                        break;
                    }
            }
        }
        $vars['prevnext'] = '';
        $requests = array();
        $sql = $sheel->db->query("
            SELECT c.*, sc.paymethod, sc.startdate, sc.renewdate, sc.active, s.title_eng,s.description_eng
            FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "subscription_customer sc ON c.customer_id = sc.customerid
            LEFT JOIN " . DB_PREFIX . "subscription s ON sc.subscriptionid = s.subscriptionid
            $searchview			
            $searchcondition	
			ORDER BY c.customer_id DESC
			LIMIT " . (($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay']) . "," . $sheel->config['globalfilters_maxrowsdisplay'] . "
		");

        $sql2 = $sheel->db->query("
            SELECT c.*, sc.paymethod, sc.startdate, sc.renewdate, sc.active, s.title_eng,s.description_eng
            FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "subscription_customer sc ON c.customer_id = sc.customerid
            LEFT JOIN " . DB_PREFIX . "subscription s ON sc.subscriptionid = s.subscriptionid
            $searchview			
            $searchcondition	
            ORDER BY c.customer_id DESC
		");

        $number = (int) $sheel->db->num_rows($sql2);
        $form['number'] = number_format($number);
        $form['filter'] = ((isset($sheel->GPC['filter'])) ? $sheel->GPC['filter'] : '');
        if ($sheel->db->num_rows($sql) > 0) {
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $res['date_added'] = $sheel->common->print_date($res['date_added'], 'M. j, Y g:ia', 0, 0);
                // $res['lineitems'] = $sheel->admincp->fetch_line_items($res['orderidpublic']);
                // $res['pictures'] = $sheel->buynow->fetch_line_item_pictures($res['orderidpublic']);
                $res['countrycode'] = $sheel->common_location->print_country_name($res['country']);
                $res['switchadd'] = '<span class="badge badge--info title="{_add_item}"><a href="' . HTTPS_SERVER_ADMIN . 'customers/items/add/' . $res['customer_id'] . '/?view=' . $fview . '&customer_id=' . $res['customer_id'] . '" data-no-turbolink>{_add}</a></span>';
                $res['switchview'] = '<span class="badge badge--success" title="{_view_item}"><a href="' . HTTPS_SERVER_ADMIN . 'customers/items/view/' . $res['customer_id'] . '/?view=' . $fview . '&customer_id=' . $res['customer_id'] . '" data-no-turbolink>{_view}</a></span>';

                $customers[] = $res;
            }
        }

        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($number, $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl);
        $vars['fview'] = $fview;

        $form['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'name' => '{_customer_name}',
            'date' => '{_date_added} (YYYY-MM-DD)'
        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
    }

    $sheel->template->fetch('main', 'customers.html', 1);
    $sheel->template->parse_loop(
        'main',
        array(
            'customers' => $customers
        )
    );
    $sheel->template->parse_hash(
        'main',
        array(
            'ilpage' => $sheel->ilpage,
            'form' => $form
        )
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode('/admin/'));
    exit();
}
?>