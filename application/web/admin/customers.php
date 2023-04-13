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
$sheel->template->meta['areatitle'] = 'Admin CP | Customers';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Customers';

if (($sidenav = $sheel->cache->fetch("sidenav_customers")) === false) {
    $sidenav = $sheel->admincp_nav->print('customers');
    $sheel->cache->store("sidenav_customers", $sidenav);
}

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {


    $q = ((isset($sheel->GPC['q'])) ? o($sheel->GPC['q']) : '');
    $sheel->GPC['page'] = (!isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
    $vars = array(
        'sidenav' => $sidenav,
        'q' => $q,
    );
    $defaulcompany = '';
    $sqldefault = $sheel->db->query("
    SELECT bc_code
    FROM " . DB_PREFIX . "companies 
    WHERE isdefault='1' 
    LIMIT 1");
    if ($sheel->db->num_rows($sqldefault) > 0) {
        while ($res = $sheel->db->fetch_array($sqldefault, DB_ASSOC)) {
            $defaulcompany = $res['bc_code'];
        }
    }

    if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bc') {
        $customers = array();
        $searchcondition = '';
        $searchfilters = array(
            'name',
            'account'
        );
        $companies = array();


        $sql = $sheel->db->query("
        SELECT name, bc_code
        FROM " . DB_PREFIX . "companies");
        if ($sheel->db->num_rows($sql) > 0) {
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $companies[$res['bc_code']] = $res['name'];
            }
        }

        $form['company'] = (isset($sheel->GPC['company']) ? $sheel->GPC['company'] : $defaulcompany);
        $form['company_pulldown'] = $sheel->construct_pulldown('company', 'company', $companies, (isset($sheel->GPC['company']) ? $sheel->GPC['company'] : $defaulcompany), 'class="draw-select" onchange="this.form.submit()"');
        $sheel->dynamics->init_dynamics('erCustomerList', (isset($sheel->GPC['company']) ? $sheel->GPC['company'] : $defaulcompany));
        $pagination = '&$skip=' . ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'] . '&$top=' . $sheel->config['globalfilters_maxrowsdisplay'];
        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'name': {
                        $searchcondition = '$filter=contains( name, \'' . $sheel->db->escape_string($q) . '\')';
                        break;
                    }
                case 'account': {
                        $searchcondition = '$filter=number eq \'' . $sheel->db->escape_string($q) . '\'';
                        break;
                    }
            }
        }
        //$contactsResponse = $sheel->dynamics->select('?$select=No&$filter=No eq \'AVR-CF00001\'');
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $pagination);
        $pageurl = PAGEURL;
        if ($apiResponse->isSuccess()) {
            $customers = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }


        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl);
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
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] != 'departments') {
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                //$response = array();
                //$response = $sheel->admincp_customers->changestatus($ids, 'active');

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
            } else if (isset($sheel->GPC['no']) and $sheel->GPC['no'] > 0) {

                //$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Company deleted', 'A managed company has been successfully deleted.');
                $sheel->template->templateregistry['message'] = 'A Customer Department has been successfully deleted.';
                die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));

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

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markactive') { // mark active
            
        }

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
            
            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
            $addResponse =$sheel->dynamics->insert(array(
                "departmentCode"     => $sheel->GPC['departments'],
                "customerNo"  => $sheel->GPC['customer_ref']
            ));
            
            if($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
                // $contactsResponse->getGuidCreated(); - Get the GUID of the created entity
            }
            else {
                $sheel->GPC['note'] = 'adderror';
                // $contactsResponse->getErrorMessage(); - Get the error message as string
            }
        }

        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Departments';
        $tempdep = array();
        $departments = array();
        $custdepartments = array();
        $customer = array();
        $sql = $sheel->db->query("
        SELECT customer_id, customer_ref, company_id
            FROM " . DB_PREFIX . "customers 
        WHERE customer_id = '" . $sheel->GPC['no'] . "'
        LIMIT 1
        ");

        if ($sheel->db->num_rows($sql) > 0) {
            $customer = $sheel->db->fetch_array($sql, DB_ASSOC);
        }
        $companycode = $sheel->admincp_customers->get_company_name($customer['company_id'], true);
        $sheel->dynamics->init_dynamics('erDepartments', $companycode);
        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $tempdep = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }

        foreach ($tempdep as $key => $value) {
   
            foreach ($value as $key1 => $value1) {
                if ($key1=='code') {
                    $code = $value1;
                }
                if ($key1=='name') {
                    $name = $value1;
                }   
            }
            $departments += [$code => $name];
        }

        $form['department_pulldown'] = $sheel->construct_pulldown('departments', 'departments', $departments, '', 'class="draw-select"');
        $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $departments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }
        $sheel->template->fetch('main', 'customer-departments.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'ilpage' => $sheel->ilpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'departments' => $departments
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bcview' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
        $sheel->template->meta['jsinclude']['footer'][] = 'admin_customers';
        $customer = array();
        $dynamics = $sheel->dynamics->init_dynamics('erCustomer', $sheel->GPC['company']);
        $searchcondition = '$filter=number eq \'' . $sheel->GPC['no'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $customer = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }

        $customer = $customer['0'];

        $sheel->GPC['activated'] = '0';
        $sql = $sheel->db->query("
        SELECT customer_ref, subscriptionid, currencyid, timezone, status, logo
        FROM " . DB_PREFIX . "customers
        WHERE customer_ref = '" . $sheel->GPC['no'] . "'
        LIMIT 1
        ");
        if ($sheel->db->num_rows($sql) > 0) {
            $sheel->GPC['activated'] = '1';
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $customer['logo'] = $res['logo'];
                $customer['status'] = $res['status'];
                $customer['tz'] = $res['timezone'];
                $customer['currency'] = $res['currencyid'];
                $customer['subscription'] = $sheel->subscription->getname($res['subscriptionid']);
                ;
            }
        }


        if (isset($sheel->GPC['activate']) and $sheel->GPC['activate'] == 'yes' and $sheel->GPC['activated'] == '0') {
            $payload = array();
            $ext = mb_substr($_FILES['imagename']['name'], strpos($_FILES['imagename']['name'], '.'), strlen($_FILES['imagename']['name']) - 1);
            list($width, $height) = getimagesize($_FILES['imagename']['tmp_name']);
            if (in_array($ext, array('.png', '.jpg', '.jpeg', '.gif')) and filesize($_FILES['imagename']['name']) <= 250000 and $width >= 150 and $height >= 150) {
                if (file_exists(DIR_ATTACHMENTS . 'customers/' . $_FILES['imagename']['name'])) {
                    if (!unlink(DIR_ATTACHMENTS . 'customers/' . $_FILES['imagename']['name'])) {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old customer logo file', 'Old customer logo file could not be removed (check permission)');
                    }
                }
                if (!move_uploaded_file($_FILES['imagename']['tmp_name'], DIR_ATTACHMENTS . 'customers/' . $sheel->GPC['no'] . $ext)) {
                    die($_FILES["imagename"]["error"]);
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error saving customer logo file', 'customer logo file could not be uploaded (check folder permission)');
                } else {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'Added customer logo file', 'A new customer logo file was saved ');
                }
            } else {
                if (!in_array($ext, array('.png', '.jpg', '.jpeg', '.gif'))) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be .png, .jpg, .jpeg, or .gif.');
                    $sheel->GPC['note'] = 'extension';
                }
                if (filesize($_FILES['imagename']['name']) >= 250000) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be under 250kb in file size.');
                    $sheel->GPC['note'] = 'size';
                }
                if ($width < 150 || $height < 150) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be at least 150x150.');
                    $sheel->GPC['note'] = 'wh';
                }
            }
            $payload['companycode'] = $sheel->GPC['company'];
            $payload['customerref'] = $customer['number'];
            $payload['customername'] = $customer['name'];
            $payload['subscriptionid'] = $sheel->GPC['subscriptionid'];
            $payload['customername2'] = $customer['name2'];
            $payload['customerabout'] = $customer['nameArabic'];
            $payload['customerdescription'] = $customer['natureOfBusiness'];
            $payload['date_added'] = date("Y/m/d H:i:s");
            $payload['status'] = $sheel->GPC['form']['status'];
            $payload['accountnumber'] = $customer['number'];
            $payload['available_balance'] = '';
            $payload['total_balance'] = '';
            $payload['currencyid'] = $sheel->GPC['form']['currencyid'];
            $payload['timezone'] = $sheel->GPC['form']['tz'];
            $payload['vatnumber'] = $customer['vatNumber'];
            $payload['regnumber'] = $customer['crNumber'];
            $payload['address'] = $customer['address'];
            $payload['address2'] = $customer['address2'];
            $payload['phone'] = $customer['phoneNumber'];
            $payload['mobile'] = $customer['mobileNumber'];
            $payload['contact'] = $customer['contact'];
            $payload['email'] = $customer['email'];
            $payload['city'] = $customer['city'];
            $payload['state'] = '';
            $payload['zipcode'] = $customer['postalCode'];
            $payload['country'] = $customer['country'];
            $payload['autopayment'] = '0';
            $payload['requestdeletion'] = '0';
            $payload['logo'] = $sheel->GPC['no'] . $ext;
            $newcustomerid = $sheel->admincp_customers->construct_new_customer($payload);
            if ($newcustomerid > 0) {
                unset($_SESSION['sheeldata']['tmp']['new_customer_ref']);
                refresh(HTTPS_SERVER_ADMIN . 'customers/');
                exit();
            } else {
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be at least 150x150.');
                $sheel->GPC['note'] = 'error';
            }

        } else {
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'The selected customer is already activated. Visit the main customer list for update');
            $sheel->GPC['note'] = 'exist';
        }

        $areanav = 'customers_bc';
        $currentarea = $sheel->GPC['no'];
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $tzlistfinal = array();
        foreach ($tzlist as $key => $value) {
            $tzlistfinal[$value] = $value;
        }

        $form['tz'] = $sheel->construct_pulldown('tz', 'form[tz]', $tzlistfinal, $customer['tz'], 'class="draw-select"');
        $form['no'] = $sheel->GPC['no'];
        $form['company'] = $sheel->GPC['company'];
        $form['currency'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', $customer['currency']);
        $statuses = array('active' => '{_active}', 'inactive' => '{_inactive}');
        $form['status'] = $sheel->construct_pulldown('status', 'form[status]', $statuses, $customer['status'], 'class="draw-select"');
        $form['subscriptions'] = $sheel->subscription->pulldown();


        $sheel->template->fetch('main', 'customers-bc.html', 1);

        $sheel->template->parse_hash(
            'main',
            array(
                'ilpage' => $sheel->ilpage,
                'form' => $form,
                'customercard' => $customer
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'view' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            $ext = mb_substr($_FILES['imagename']['name'], strpos($_FILES['imagename']['name'], '.'), strlen($_FILES['imagename']['name']) - 1);
            list($width, $height) = getimagesize($_FILES['imagename']['tmp_name']);
            if (in_array($ext, array('.png', '.jpg', '.jpeg', '.gif')) and filesize($_FILES['imagename']['name']) <= 250000 and $width >= 150 and $height >= 150) {
                if (file_exists(DIR_ATTACHMENTS . 'customers/' . $_FILES['imagename']['name'])) {
                    if (!unlink(DIR_ATTACHMENTS . 'customers/' . $_FILES['imagename']['name'])) {
                        $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error removing old customer logo file', 'Old customer logo file could not be removed (check permission)');
                    }
                }
                if (!move_uploaded_file($_FILES['imagename']['tmp_name'], DIR_ATTACHMENTS . 'customers/' . $sheel->GPC['customer_ref'] . $ext)) {
                    die($_FILES["imagename"]["error"]);
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error saving customer logo file', 'customer logo file could not be uploaded (check folder permission)');
                } else {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'Added customer logo file', 'A new customer logo file was saved ');
                }
            } else {
                if (!in_array($ext, array('.png', '.jpg', '.jpeg', '.gif'))) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be .png, .jpg, .jpeg, or .gif.');
                    $sheel->GPC['note'] = 'extension';
                }
                if (filesize($_FILES['imagename']['name']) >= 250000) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be under 250kb in file size.');
                    $sheel->GPC['note'] = 'size';
                }
                if ($width < 150 || $height < 150) {
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be at least 150x150.');
                    $sheel->GPC['note'] = 'wh';
                }
            }
            $sheel->db->query("UPDATE " . DB_PREFIX . "customers
                SET subscriptionid = '" . $sheel->GPC['form']['subscriptionid'] . "',
                status = '" . $sheel->GPC['form']['status'] . "',
                timezone = '" . $sheel->GPC['form']['tz'] . "',
                currencyid = '" . $sheel->GPC['form']['currencyid'] . "',
                logo ='" . $sheel->GPC['customer_ref'] . $ext . "'
                WHERE customer_id = '" . $sheel->GPC['no'] . "'
            ");

        }
        $areanav = 'customers_customers';
        $currentarea = $sheel->GPC['no'];
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $customer = array();
        $sql = $sheel->db->query("
        SELECT c.*, cp.address, cp.address2, cp.phone, cp.mobile, cp.contact, cp.email, cp.city, cp.state, cp.zipcode, cp.country, cp.dateadded
            FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "customer_profiles cp ON c.customer_id = cp.customer_id
        WHERE c.customer_id = '" . $sheel->GPC['no'] . "'
        LIMIT 1
        ");

        if ($sheel->db->num_rows($sql) > 0) {
            $sheel->GPC['activated'] = '1';
            $customer = $sheel->db->fetch_array($sql, DB_ASSOC);
            $customer['subscription'] = $sheel->subscription->getname($customer['subscriptionid']);

        }
        $companycode = $sheel->admincp_customers->get_company_name($customer['company_id'], true);
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $tzlistfinal = array();
        foreach ($tzlist as $key => $value) {
            $tzlistfinal[$value] = $value;
        }
        $form['tz'] = $sheel->construct_pulldown('tz', 'form[tz]', $tzlistfinal, $customer['timezone'], 'class="draw-select"');
        $form['no'] = $sheel->GPC['no'];
        $form['company'] = $companycode;
        $form['currency'] = $sheel->currency->pulldown('', '', 'draw-select', 'form[currencyid]', 'currencyid', $customer['currencyid']);
        $statuses = array('active' => '{_active}', 'inactive' => '{_inactive}');
        $form['status'] = $sheel->construct_pulldown('status', 'form[status]', $statuses, $customer['status'], 'class="draw-select"');
        $form['subscriptions'] = $sheel->subscription->plans_pulldown('draw-select', $customer['subscriptionid']);
        $form['imagename'] = $customer['logo'];

        $departments = array();
        $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $departments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }

        $positions = array();
        $sheel->dynamics->init_dynamics('erCustomerPositions', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $positions = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }

        $staff = array();
        $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $staff = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }

        $sheel->template->fetch('main', 'customers.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'ilpage' => $sheel->ilpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'departments' => $departments,
                'positions' => $positions,
                'staff' => $staff
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();

    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'refresh' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
        $customer = array();
        $companycode = $defaulcompany;
        $sql = $sheel->db->query("
            SELECT customer_ref
            FROM " . DB_PREFIX . "customers
            WHERE customer_id = '" . $sheel->GPC['no'] . "'
            LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($sheel->db->num_rows($sql) > 0) {
            $res = $sheel->db->fetch_array($sql, DB_ASSOC);
        } else {

        }
        $searchcondition = '$filter=number eq \'' . $res['customer_ref'] . '\'';
        $dynamics = $sheel->dynamics->init_dynamics('erCustomer', $companycode);
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $customer = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die(
                json_encode(
                    array(
                        'response' => '0',
                        'message' => $sheel->template->parse_template_phrases('message')
                    )
                )
            );
        }
        $customer = $customer['0'];
        $payload = array();
        $payload['customer_id'] = $sheel->GPC['no'];
        $payload['customerref'] = $customer['number'];
        $payload['customername'] = $customer['name'];
        $payload['subscriptionid'] = $sheel->GPC['subscriptionid'];
        $payload['customername2'] = $customer['name2'];
        $payload['customerabout'] = $customer['nameArabic'];
        $payload['customerdescription'] = $customer['natureOfBusiness'];
        $payload['date_added'] = date("Y/m/d H:i:s");
        $payload['status'] = $sheel->GPC['form']['status'];
        $payload['accountnumber'] = $customer['number'];
        $payload['available_balance'] = '';
        $payload['total_balance'] = '';
        $payload['currencyid'] = $sheel->GPC['form']['currencyid'];
        $payload['timezone'] = $sheel->GPC['form']['tz'];
        $payload['vatnumber'] = $customer['vatNumber'];
        $payload['regnumber'] = $customer['crNumber'];
        $payload['address'] = $customer['address'];
        $payload['address2'] = $customer['address2'];
        $payload['phone'] = $customer['phoneNumber'];
        $payload['mobile'] = $customer['mobileNumber'];
        $payload['contact'] = $customer['contact'];
        $payload['email'] = $customer['email'];
        $payload['city'] = $customer['city'];
        $payload['state'] = '';
        $payload['zipcode'] = $customer['postalCode'];
        $payload['country'] = $customer['country'];
        $payload['autopayment'] = '0';
        $payload['requestdeletion'] = '0';
        $payload['logo'] = $sheel->GPC['no'] . $ext;
        $refreshed = $sheel->admincp_customers->refresh_customer($payload);
        if ($refreshed) {
            unset($_SESSION['sheeldata']['tmp']['new_customer_ref']);
            refresh(HTTPS_SERVER_ADMIN . 'customers/view/' . $sheel->GPC['no'] . '/');
            exit();
        } else {
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error uploading customer logo file', 'The customer logo file must be at least 150x150.');
            $sheel->GPC['note'] = 'error';
        }


    } else {
        $areanav = 'customers_customers';
        $vars['areanav'] = $areanav;
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markactive') { // mark active
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
        } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'markinactive') { // mark inactive
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->changestatus($ids, 'inactive');
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
            'name',
            'account',
            'date'
        );
        $searchviews = array(
            'active',
            'inactive'
        );
        $searchcondition = $searchview = '';

        if (isset($sheel->GPC['view']) and !empty($sheel->GPC['view']) and in_array($sheel->GPC['view'], $searchviews)) {
            switch ($sheel->GPC['view']) {
                case 'active': {
                        $searchview = " WHERE (c.status = 'active')";
                        break;
                    }
                case 'inactive': {
                        $searchview = " WHERE (c.status = 'inactive')";
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
                case 'account': {
                        $searchcondition = "AND (c.customer_ref = '" . $sheel->db->escape_string($q) . "' OR c.account_number = '" . $sheel->db->escape_string($q) . "')";
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
                $customers[] = $res;
            }
        }

        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($number, $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl);

        $form['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'account' => '{_account}',
            'name' => '{_name}',
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