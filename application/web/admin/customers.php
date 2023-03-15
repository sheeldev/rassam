<?php
define('LOCATION', 'admin');
require_once (SITE_ROOT . 'application/config.php');
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
if (! empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {


    $q = ((isset($sheel->GPC['q'])) ? o($sheel->GPC['q']) : '');
    $sheel->GPC['page'] = (! isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
    $fview = $sheel->GPC['view'];
    $vars = array(
        'sidenav' => $sidenav,
        'rid' => ((isset($sheel->GPC['rid'])) ? $sheel->GPC['rid'] : ''),
        'q' => $q,
        'fview' => $fview,
    );
    if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'view') {
        
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'save') {
            
            $customerid= $sheel->admincp_users->get_user_id($sheel->GPC['form'][useraccount]);
            if ($customerid > 0) {
                
                $sheel->db->query("
						UPDATE " . DB_PREFIX . "requests r
						SET cid = '" . $sheel->db->escape_string($sheel->GPC['form']['cid']) . "',
						vehicle_registration = '" . $sheel->db->escape_string($sheel->GPC['form']['vehicleregistration']) . "',
                        vehicle_c_number = '" . $sheel->db->escape_string($sheel->GPC['form']['vehiclenumber']) . "',
                        vehicle_make = '" . $sheel->db->escape_string($sheel->GPC['form']['vehiclemake']) . "',
                        vehicle_model = '" . $sheel->db->escape_string($sheel->GPC['form']['vehiclemodel']) . "',
                        vehicle_year = '" . $sheel->db->escape_string($sheel->GPC['form']['vehicleyear']) . "',
                        date_added = '" . $sheel->db->escape_string($sheel->GPC['form']['requestdate']) . "',
                        vehicle_value = '" . $sheel->db->escape_string($sheel->GPC['form']['vehiclevalue']) . "',
                        value_currency = '" . $sheel->db->escape_string($sheel->GPC['form']['currency']) . "',
                        customer_id = '" . intval($customerid) . "',
						r.status = '" . $sheel->db->escape_string($sheel->GPC['form']['status']) . "',
                        r.condition = '" . $sheel->db->escape_string($sheel->GPC['form']['condition']) . "',
                        used_for = '" . $sheel->db->escape_string($sheel->GPC['form']['usedfor']) . "',
                        used_for_unit = '" . $sheel->db->escape_string($sheel->GPC['form']['usedunit']) . "',
                        total_parts = '" . $sheel->db->escape_string($sheel->GPC['form']['totalparts']) . "',
                        storeid = '" . intval($sheel->GPC['form']['store']) . "'
						WHERE request_id = '" . intval($sheel->GPC['form']['rid']) . "'
						LIMIT 1
					");		
                		
            }
            else {
                refresh(HTTPS_SERVER_ADMIN . 'requests/view/'.$sheel->GPC['rid'].'/?error=wan');
            }
            
            
            if ($sheel->GPC['form']['rid'] > 0)
            {
                $sheel->attachment->update_request_attachments($sheel->GPC['form']['pid'],$sheel->GPC['form']['rid']);
                unset($_SESSION['sheeldata']['tmp']['new_project_id']);
                refresh(HTTPS_SERVER_ADMIN . 'requests/view/'.$sheel->GPC['rid'].'/');
                exit();
            }
            
        }
        
        $areanav = 'request_requestview';
        $form = array();
        $rid = $sheel->GPC['rid'];
        $requests = array();
        $sql = $sheel->db->query("
			SELECT c.*, sc.paymentmethod, sc.startdate, sc.renewdate, sc.active, s.title_eng,s.description_eng
			FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "subscription_customer sc ON c.customer_id = sc.customerid
            LEFT JOIN " . DB_PREFIX . "subscription s ON sc.subscriptionid = s.subscriptionid
		");
        if ($sheel->db->num_rows($sql) > 0) {
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $form['cid'] =  $res['cid'];
                $form['rid'] =  $res['request_id'];
                $form['vehicleregistration'] =  $res['vehicle_registration'];
                $form['vehiclenumber'] =  $res['vehicle_c_number'];
                $form['vehiclemake'] =  $res['vehicle_make'];
                $form['vehiclemodel'] =  $res['vehicle_model'];
                $form['vehicleyear'] =  $res['vehicle_year'];
                $form['requestdate'] =  $res['date_added'];
                $form['vehiclevalue'] =  $res['vehicle_value'];
                $form['totalparts'] =  $res['total_parts'];
                $form['valuecurrency'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currency]', 'currencyid', $res['value_currency']);
                $form['useraccount'] =  $sheel->admincp_users->get_user_account($res['customer_id']);
                $requeststatuses = array('registered' => '{_registered}', 'read' => '{_ready}', 'accepted' => '{_accepted}', 'dispatched' => '{_dispatched}', 'picked' => '{_picked}', 'closed' => '{_closed}');
                $form['requeststatus'] = $sheel->construct_pulldown('status', 'form[status]', $requeststatuses, $res['status'], 'class="draw-select"');
                $requestconditions = array('bad' => '{_bad}', 'fair' => '{_fair}', 'good' => '{_good}', 'verygood' => '{_very_good}', 'excellent' => '{_excellent}');
                $form['requestcondition'] = $sheel->construct_pulldown('condition', 'form[condition]', $requestconditions, $res['condition'], 'class="draw-select"');
                $form['usedfor'] =  $res['used_for'];
                $requestusedunits = array('km' => '{_km}', 'hour' => '{_hour}');
                $form['requestusedunit'] = $sheel->construct_pulldown('usedunit', 'form[usedunit]', $requestusedunits, $res['used_for_unit'], 'class="draw-select"');
                $form['pid'] =  $res['project_id'];
                $form['adminstores'] = $sheel->stores->pulldown('draw-select', 'form[store]', 'storeid', $res['storeid']);
            }
        }
        
        $sheel->template->fetch('main', 'requests_view.html', 1);
        $sheel->template->parse_hash('main', array(
            'ilpage' => $sheel->ilpage,
            'form' => $form
        ));
        $sheel->template->pprint('main', $vars);
        exit();
        
    }
    
    else if (isset($sheel->GPC['cmd']) AND $sheel->GPC['cmd'] == 'add')
    {
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'save') {
            //die($sheel->GPC['subscriptionid']);

            $sheel->GPC['form']['customerref'] = md5(md5($_SESSION['sheeldata']['tmp']['new_customer_ref']));
            $sheel->GPC['form']['subscriptionid'] = $sheel->GPC['subscriptionid'];
                
            $newcustomerid = $sheel->admincp_customers->construct_new_customer($sheel->GPC['form']);
            if ($newcustomerid > 0)
            {
                unset($_SESSION['sheeldata']['tmp']['new_customer_ref']);
                refresh(HTTPS_SERVER_ADMIN . 'customers/');
                exit();
            }
            else {
                refresh(HTTPS_SERVER_ADMIN . 'customers/add/?error=wan');
            }
           
        }
        
        
        $form = array();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        if (empty($_SESSION['sheeldata']['tmp']['new_customer_ref']) OR !isset($_SESSION['sheeldata']['tmp']['new_customer_ref']))
        {
            $customer_ref = $sheel->admincp_customers->construct_new_ref();
            $_SESSION['sheeldata']['tmp']['new_customer_ref'] = $customer_ref;
        }
        else
        {
            $customer_ref = $_SESSION['sheeldata']['tmp']['new_customer_ref'];
        }

        
        $sheel->template->meta['areatitle'] = 'Admin CP | Customers - Add';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Customers - Add';
        
        $form['customername'] = '';
        $form['customerabout'] = '';
        $form['customerdescription'] = '';
        $form['customeraccount'] = '';
        $form['customervat'] = '';
        $form['customerreg'] = '';
        $form['subscriptions'] = $sheel->subscription->pulldown();
        $form['currency'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currency]', 'currencyid', '');
        $customerstatuses = array('active' => '{_active}', 'banned' => '{_banned}', 'moderated' => '{_moderated}', 'cancelled' => '{_cancelled}', 'suspended' => '{_suspended}');
        $form['customerstatus'] = $sheel->construct_pulldown('status', 'form[customerstatus]', $customerstatuses, '', 'class="draw-select"');
        $form['tz'] = $sheel->construct_pulldown('tz', 'form[tz]', $tzlist, '', 'class="draw-select"');
        $form['imagename'] = '';
        $form['customeraddress1'] = '';
        $form['customeraddress2'] = '';
        $form['customercity'] = '';
        $form['customerstate'] = '';
        $form['customerzip'] = '';
        $form['customercountry'] = $sheel->common_location->construct_country_pulldown('', '', 'draw-select', 'form[customercountry]', 'locationid', '');
        $form['customeremail'] = '';
        $form['customerphone'] = '';  
    }
    
    
    else {
        $areanav = 'customers_customers';
        
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'marksuspended') { // mark suspended

            if (! empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids,'suspended');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(json_encode(array(
                    'response' => '2',
                    'success' => $sheel->template->parse_template_phrases('success'),
                    'errors' => $sheel->template->parse_template_phrases('errors'),
                    'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                    'successids' => $response['successids'],
                    'failedids' => $response['failedids']
                )));
            } else {
                
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(json_encode(array(
                    'response' => '0',
                    'message' => $sheel->template->parse_template_phrases('message')
                )));
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markcancelled') { // mark cancelled
            if (! empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids,'cancelled');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(json_encode(array(
                    'response' => '2',
                    'success' => $sheel->template->parse_template_phrases('success'),
                    'errors' => $sheel->template->parse_template_phrases('errors'),
                    'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                    'successids' => $response['successids'],
                    'failedids' => $response['failedids']
                )));
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(json_encode(array(
                    'response' => '0',
                    'message' => $sheel->template->parse_template_phrases('message')
                )));
            }
        }
        else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markbanned') { // mark banned
            if (! empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids,'banned');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(json_encode(array(
                    'response' => '2',
                    'success' => $sheel->template->parse_template_phrases('success'),
                    'errors' => $sheel->template->parse_template_phrases('errors'),
                    'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                    'successids' => $response['successids'],
                    'failedids' => $response['failedids']
                )));
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(json_encode(array(
                    'response' => '0',
                    'message' => $sheel->template->parse_template_phrases('message')
                )));
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markactive') { // mark active
            if (! empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids,'active');
                
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(json_encode(array(
                    'response' => '2',
                    'success' => $sheel->template->parse_template_phrases('success'),
                    'errors' => $sheel->template->parse_template_phrases('errors'),
                    'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                    'successids' => $response['successids'],
                    'failedids' => $response['failedids']
                )));
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(json_encode(array(
                    'response' => '0',
                    'message' => $sheel->template->parse_template_phrases('message')
                )));
            }
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markdeleted') { // mark deleted
            if (! empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids,'deleted');
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                die(json_encode(array(
                    'response' => '2',
                    'success' => $sheel->template->parse_template_phrases('success'),
                    'errors' => $sheel->template->parse_template_phrases('errors'),
                    'ids' => $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']],
                    'successids' => $response['successids'],
                    'failedids' => $response['failedids']
                )));
            } else {
                $sheel->template->templateregistry['message'] = '{_no_customer_were_selected_please_try_again}';
                die(json_encode(array(
                    'response' => '0',
                    'message' => $sheel->template->parse_template_phrases('message')
                )));
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

        if (isset($sheel->GPC['view']) and ! empty($sheel->GPC['view']) and in_array($sheel->GPC['view'], $searchviews)) {
            switch ($sheel->GPC['view']) {
                case 'active':
                    {
                        $searchview = " WHERE (c.status = 'active')";
                        break;
                    }
                case 'banned':
                    {
                        $searchview = " WHERE (c.status = 'banned')";
                        break;
                    }
                case 'cancelled':
                    {
                        $searchview = " WHERE (c.status = 'cancelled')";
                        break;
                    }
                case 'deleted':
                    {
                        $searchview = " WHERE (c.status = 'deleted')";
                        break;
                    }
                case 'suspended':
                    {
                        $searchview = " WHERE (c.status = 'suspended')";
                        break;
                    }
                case 'moderated':
                    {
                        $searchview = " WHERE (c.status = 'moderated')";
                        break;
                    }
            }
        } else {
            $searchview = " WHERE (c.status = 'active')";
        }

        if (isset($sheel->GPC['filter']) and ! empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and ! empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'name':
                    {
                        $searchcondition = "AND (c.customername Like '%" . $sheel->db->escape_string($q) . "%' OR c.description Like '%" . $sheel->db->escape_string($q) . "%')";
                        break;
                    }

                case 'date':
                    {
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
                $res['switchadd'] = '<span class="badge badge--info title="{_add_item}"><a href="' . HTTPS_SERVER_ADMIN .'customers/items/add/' . $res['customer_id'] . '/?view='.$fview.'&customer_id=' . $res['customer_id'] .'" data-no-turbolink>{_add}</a></span>';
                $res['switchview'] = '<span class="badge badge--success" title="{_view_item}"><a href="' . HTTPS_SERVER_ADMIN .'customers/items/view/' . $res['customer_id'] . '/?view='.$fview.'&customer_id=' . $res['customer_id'] .'" data-no-turbolink>{_view}</a></span>';
                
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
    $sheel->template->parse_loop('main', array(
        'customers' => $customers
    ));
    $sheel->template->parse_hash('main', array(
        'ilpage' => $sheel->ilpage,
        'form' => $form
    ));
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode('/admin/'));
    exit();
}
?>

