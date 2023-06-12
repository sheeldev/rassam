<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
require_once(DIR_CLASSES . '/vendor/office/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
    'footer' => array(
    )
);
$sheel->template->meta['cssinclude'] = array(
    'common',
    'addition',
    'spinner',
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
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] == 'departments') {
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {

                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->dynamics_activities->bulkdelete($ids, 'erCustomerDepartments', $sheel->GPC['company_id']);
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
            } else if (isset($sheel->GPC['systemid']) and $sheel->GPC['systemid'] != '') {
                $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
                $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['systemid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer Department deleted', 'A customer department has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Customer Department has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }



            } else {
                $sheel->template->templateregistry['message'] = '{_no_record_selected}';
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

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {

            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            $sheel->dynamics->init_dynamics('erCustomerDepartments', $defaulcompany);
            $addResponse = $sheel->dynamics->insert(
                array(
                    "departmentCode" => $sheel->GPC['departments'],
                    "customerNo" => $sheel->GPC['customer_ref']
                )
            );

            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
                // $contactsResponse->getGuidCreated(); - Get the GUID of the created entity
            } else {
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
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
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
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] == 'positions') {
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {

                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->dynamics_activities->bulkdelete($ids, 'erCustomerPositions', $sheel->GPC['company_id']);
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
            } else if (isset($sheel->GPC['systemid']) and $sheel->GPC['systemid'] != '') {
                $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
                $sheel->dynamics->init_dynamics('erCustomerPositions', $companycode);
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['systemid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Customer Position deleted', 'A customer position has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Customer Position has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }



            } else {
                $sheel->template->templateregistry['message'] = '{_no_record_selected}';
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

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {

            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            $sheel->dynamics->init_dynamics('erCustomerPositions', $defaulcompany);
            $addResponse = $sheel->dynamics->insert(
                array(
                    "positionCode" => $sheel->GPC['positions'],
                    "departmentCode" => $sheel->GPC['departments'],
                    "customerNo" => $sheel->GPC['customer_ref']
                )
            );

            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
                // $contactsResponse->getGuidCreated(); - Get the GUID of the created entity
            } else {
                $sheel->GPC['note'] = 'adderror';
                // $contactsResponse->getErrorMessage(); - Get the error message as string
            }
        }
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Positions';
        $temppos = array();
        $positions = array();
        $tempcustdepartments = array();
        $custdepartments = array();

        $custpositions = array();
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

        $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustdepartments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempcustdepartments as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'departmentCode') {
                    $code = $value1;
                }
                if ($key1 == 'departmentName') {
                    $name = $value1;
                }
            }
            $custdepartments += [$code => $name];
        }


        $sheel->dynamics->init_dynamics('erPositions', $companycode);
        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $temppos = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($temppos as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $positions += [$code => $name];
        }
        $form['department_pulldown'] = $sheel->construct_pulldown('departments', 'departments', $custdepartments, '', 'class="draw-select"');
        $form['position_pulldown'] = $sheel->construct_pulldown('positions', 'positions', $positions, '', 'class="draw-select"');

        $sheel->dynamics->init_dynamics('erCustomerPositions', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $custpositions = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }
        $sheel->template->fetch('main', 'customer-positions.html', 1);
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
                'custpositions' => $custpositions
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] == 'staffs') {
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
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'staffsample') {
            $samplefile = file_get_contents(DIR_OTHER . 'staffsample.xlsx');
            $sheel->common->download_file($samplefile, "staffsample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            exit();
        } 

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] =='cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_staffs
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/staffs/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/staffs/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] .'_STAFF'.'.'. pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader  = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);
                    
                    //die ($spreadsheet->getSheet(0)->getHighestDataColumn().'-------'.$spreadsheet->getSheet(0)->getHighestDataRow());
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn()!='D') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/staffs/?error=invalidff');
                    }
                    $sheetData=$spreadsheet->getSheet(0)->toArray();
                    $fileinfo = serialize($sheetData);
                    if (isset($sheel->GPC['containsheader']) and $sheel->GPC['containsheader'] == '1') {
                        unset($sheetData[0]);
                    }
                    $sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "bulk_sessions
                            (id, user_id, dateupload, uploadtype, linescount, linesuploaded, picturesuploaded, haserrors, fileinfo)
                            VALUES (
                            NULL,
                            '" . $_SESSION['sheeldata']['user']['userid'] . "',
                            '" . $sheel->db->escape_string(DATETIME24H) . "',
                            'STAFF',
                            '".(intval($spreadsheet->getSheet(0)->getHighestDataRow())-1)."',
                            '0',
                            '0',
                            '0',
                            '" . $sheel->db->escape_string($fileinfo) . "')
                        ", 0, null, __FILE__, __LINE__);
                    $bulk_id = $sheel->db->insert_id();
                    unset($fileinfo);
    
                    $sheel->xlsx->staff_xlsx_to_db($sheetData, $customer['customer_ref'], $sheel->GPC['nextstaffid'], $_SESSION['sheeldata']['user']['userid'], $bulk_id);
    
                    if (file_exists($file_name)) { // remove uploaded xlsx file...
                        unlink($file_name);
                    }
                }
                $sql = $sheel->db->query("
                    SELECT id, code, name, gender, positioncode, departmentcode, customerno
                        FROM " . DB_PREFIX . "bulk_tmp_staffs 
                    WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
                    ORDER BY code
                ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
                    $addResponse = $sheel->dynamics->insert(
                        array(
                            "code" => $res['code'],
                            "name" => $res['name'],
                            "gender" => $res['gender'],
                            "positionCode" => $res['positioncode'],
                            "departmentCode" => $res['departmentcode'],
                            "customerNo" => $res['customerno'],
                        )
                    );
                    if ($addResponse->isSuccess()) {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_staffs
                            SET errors = '[No Errors]',
                                uploaded = '1'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
          
                    } else {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_staffs
                            SET errors = '[". $sheel->db->escape_string($addResponse->getErrorMessage())."]'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
                       
                    }
                }
            }
        } 
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->dynamics_activities->bulkdelete($ids, 'erCustomerStaffs', $sheel->GPC['company_id']);
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
            } else if (isset($sheel->GPC['systemid']) and $sheel->GPC['systemid'] != '') {
                $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
                $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['systemid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Memeber deleted', 'A customer staff member has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Customer Staff Member has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }
            } else {
                $sheel->template->templateregistry['message'] = '{_no_record_selected}';
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

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {

            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            $sheel->dynamics->init_dynamics('erCustomerStaffs', $defaulcompany);
            $addResponse = $sheel->dynamics->insert(
                array(
                    "code" => $sheel->GPC['form']['staffcode'],
                    "name" => $sheel->GPC['form']['staffname'],
                    "gender" => $sheel->GPC['form']['gender'],
                    "positionCode" => $sheel->GPC['positions'],
                    "departmentCode" => $sheel->GPC['departments'],
                    "customerNo" => $sheel->GPC['customer_ref']
                )
            );

            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
            } else {
                $sheel->GPC['note'] = 'adderror';
            }
        }
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Staffs';
        $tempcustpos = array();
        $custpositions = array();
        $tempcustdepartments = array();
        $custdepartments = array();

        $custstaffs = array();
        $gender = array('Male' => '{_male}', 'Female' => '{_female}');
        $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=departmentCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustdepartments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempcustdepartments as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'departmentCode') {
                    $code = $value1;
                }
                if ($key1 == 'departmentName') {
                    $name = $value1;
                }
            }
            $custdepartments += [$code => $code.' > '.$name];
        }


        $sheel->dynamics->init_dynamics('erCustomerPositions', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=positionCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustpos = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempcustpos as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'positionCode') {
                    $code = $value1;
                }
                if ($key1 == 'positionName') {
                    $name = $value1;
                }
            }
            $custpositions += [$code => $code.' > '.$name];
        }
        $form['department_pulldown'] = $sheel->construct_pulldown('departments', 'departments', $custdepartments, '', 'class="draw-select"');
        $form['position_pulldown'] = $sheel->construct_pulldown('positions', 'positions', $custpositions, '', 'class="draw-select"');
        $form['gender'] = $sheel->construct_pulldown('gender', 'form[gender]', $gender, '', 'class="draw-select"');
        $form['staffname'] = '';
        $form['staffcode'] = '';
        $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $custstaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }
        $tempids = array();
        $ids = array();
        foreach ($custstaffs as $key => $value) {
            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $tempids[] = explode("-", $value1); 
                }
            }
        }
        
        foreach ($tempids as $key => $value) {
            $ids[] = $value[2]; 
        }
        $form['staffcode'] = max($ids)>0? $customer['customer_ref'].'-'. intval(max($ids)+1): $customer['customer_ref'].'-1';
        $form['staffid'] = max($ids) > 0 ? ''.intval(max($ids)+1) : '1';
        $uploadedstaffs = array();
        $sqlupd = $sheel->db->query("
            SELECT id, code, name, gender, positioncode, departmentcode, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_staffs 
            WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
            ORDER BY code
        ");

        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {
           
            $uploadedstaffs[] = $res;
        }

        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads']='1';
        }
        $sheel->template->fetch('main', 'customer-staffs.html', 1);
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
                'uploadedstaffs' => $uploadedstaffs,
                'custstaffs' => $custstaffs,
                
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] == 'measurements') {
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
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'measurementsample') {
            $samplefile = file_get_contents(DIR_OTHER . 'measurementsample.xlsx');
            $sheel->common->download_file($samplefile, "measurementsample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            exit();
        } 

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] =='cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_measurements
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/measurements/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/measurements/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] .'_MEASUREMENT'.'.'. pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader  = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn()!='D') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/measurements/?error=invalidff');
                    }
                    $sheetData=$spreadsheet->getSheet(0)->toArray();
                    $fileinfo = serialize($sheetData);
                    if (isset($sheel->GPC['containsheader']) and $sheel->GPC['containsheader'] == '1') {
                        unset($sheetData[0]);
                    }
                    $sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "bulk_sessions
                            (id, user_id, dateupload, uploadtype, linescount, linesuploaded, picturesuploaded, haserrors, fileinfo)
                            VALUES (
                            NULL,
                            '" . $_SESSION['sheeldata']['user']['userid'] . "',
                            '" . $sheel->db->escape_string(DATETIME24H) . "',
                            'MEASUREMENT',
                            '".(intval($spreadsheet->getSheet(0)->getHighestDataRow())-1)."',
                            '0',
                            '0',
                            '0',
                            '" . $sheel->db->escape_string($fileinfo) . "')
                        ", 0, null, __FILE__, __LINE__);
                    $bulk_id = $sheel->db->insert_id();
                    unset($fileinfo);
                    $tempcuststaffs = array();
                    $custstaffs = array();

                    $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $tempcuststaffs = $apiResponse->getData();
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        die($sheel->template->parse_template_phrases('message'));
                    }
                    foreach ($tempcuststaffs as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            
                            if ($key1 == 'code') {
                                $code = $value1;
                            }
                            if ($key1 == 'positionCode') {
                                $name = $value1;
                            }
                            if ($key1 == 'departmentCode') {
                                $name = $name . '|' . $value1;
                            }
                        }
                        $custstaffs += [$code => $name];
                    }
                    $sheel->xlsx->measurement_xlsx_to_db($sheetData, $custstaffs, $customer['customer_ref'], $_SESSION['sheeldata']['user']['userid'], $bulk_id);
    
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                }
                $sql = $sheel->db->query("
                    SELECT id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno
                        FROM " . DB_PREFIX . "bulk_tmp_measurements 
                    WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
                    ORDER BY staffcode
                ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
                    $addResponse = $sheel->dynamics->insert(
                        array(
                            "customerNo" => $res['customerno'],
                            "staffCode" => $res['staffcode'],
                            "positionCode" => $res['positioncode'],
                            "departmentCode" => $res['departmentcode'],
                            "measurementCode" => $res['measurementcategory'],
                            "value" => intval($res['mvalue']),
                            "uomCode" => $res['uom']
                        )
                    );
                    if ($addResponse->isSuccess()) {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_measurements
                            SET errors = '[No Errors]',
                                uploaded = '1'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
          
                    } else {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_measurements
                            SET errors = '[". $sheel->db->escape_string($addResponse->getErrorMessage())."]'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
                       
                    }
                }
            }
        } 

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->dynamics_activities->bulkdelete($ids, 'erStaffMeasurements', $customer['company_id']);
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
            } else if (isset($sheel->GPC['systemid']) and $sheel->GPC['systemid'] != '') {
                
                $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['systemid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Measurememnt deleted', 'A customer staff measurement has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Staff Measurements has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }
            } else {
                $sheel->template->templateregistry['message'] = '{_no_record_selected}';
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

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
            $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
            $staffdetails = explode('|',$sheel->GPC['staffs']);

            $addResponse = $sheel->dynamics->insert(
                array(
                    "customerNo" => $sheel->GPC['customer_ref'],
                    "staffCode" => $staffdetails[0],
                    "positionCode" => $staffdetails[1],
                    "departmentCode" => $staffdetails[2],
                    "measurementCode" => $sheel->GPC['measurements'],
                    "value" => intval($sheel->GPC['form']['value']),
                    "uomCode" => $sheel->GPC['uoms']
                    
                )
            );

            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
            } else {
                $sheel->GPC['note'] = 'adderror';
                $vars['errorMessage'] = $addResponse->getErrorMessage();
            }
        }
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Measurements';
        $tempcuststaffs = array();
        $custstaffs = array();
        $tempmeasurements = array();
        $measurements = array();
        $tempuom = array();
        $uom = array();
        $searchfilters = array(
            'staff',
            'measurement',
            'position',
            'department'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'measurement' => '{_measurement}',
            'position' => '{_position}',
            'department' => '{_department}'
            
        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);

        $staffmeasurements = array();
        
        $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuststaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }
        foreach ($tempcuststaffs as $key => $value) {
            foreach ($value as $key1 => $value1) {
                
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'departmentCode') {
                    $code = $code . '|' . $value1;
                }
                if ($key1 == 'positionCode') {
                    $code = $code . '|' . $value1;
                }

                if ($key1 == 'name') {
                    $name = $value1;
                }
                if ($key1 == 'departmentName') {
                    $name = $name . ' > ' . $value1;
                }
                if ($key1 == 'positionName') {
                    $name = $name . ' > ' . $value1;
                }
            }
            $custstaffs += [$code => $name];
        }
        $sheel->dynamics->init_dynamics('erMeasurementCategories', $companycode);
        $searchcondition = '$orderby=name asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempmeasurements = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempmeasurements as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $measurements += [$code => $code. ' > ' . $name];
        }
        $sheel->dynamics->init_dynamics('erUOM', $companycode);
        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $tempuom = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }
        foreach ($tempuom as $key => $value) {
            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $uom += [$code => $name];
        }
        $form['staff_pulldown'] = $sheel->construct_pulldown('staffs', 'staffs', $custstaffs, '', 'class="draw-select"');
        $form['measurement_pulldown'] = $sheel->construct_pulldown('measurements', 'measurements', $measurements, '', 'class="draw-select"');
        $form['uom_pulldown'] = $sheel->construct_pulldown('uoms', 'uoms', $uom, 'CM', 'class="draw-select"');
        $form['value']='';


        $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'measurement': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and measurementCode eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'position': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and positionName eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'department': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and departmentName eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
            }
        }
        else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }
        
        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition . $ordercondition);
        if ($apiResponse->isSuccess()) {
            $staffmeasurements = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        $uploadedmeaasurements = array();
        $sqlupd = $sheel->db->query("
            SELECT id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_measurements 
            WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
            ORDER BY staffcode
        ");

        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {
           
            $uploadedmeaasurements[] = $res;
        }

        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads']='1';
        }

        $sheel->template->fetch('main', 'staff-measurements.html', 1);
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
                'staffmeasurements' => $staffmeasurements,
                'uploadedmeaasurements' => $uploadedmeaasurements
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'org' and isset($sheel->GPC['sub']) and $sheel->GPC['sub'] == 'sizes') {
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
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'sizesample') {
            $samplefile = file_get_contents(DIR_OTHER . 'sizesample.xlsx');
            $sheel->common->download_file($samplefile, "sizesample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            exit();
        } 
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'suggest') {
            $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_sizes
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            $custstaffs = array();
            $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $custstaffs = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                die($sheel->template->parse_template_phrases('message'));
            }
            $suggestdata = array();
            foreach ($custstaffs as $keycust => $valuecust) {
                $tempsm = array();
                $sm = array();
                $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
                $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $valuecust['code'] . '\'';
                $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                    $tempsm = $apiResponse->getData();
                } else {
                    $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                    die($sheel->template->parse_template_phrases('message'));
                }

                foreach ($tempsm as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        if ($key1 == 'measurementCode') {
                            $code = $value1;
                        }
                        if ($key1 == 'value') {
                            $name['value'] = $value1;
                        }
                        if ($key1 == 'uomCode') {
                            $name['uomCode'] = $value1;
                        }
                    }
                    $sm += [$code => $name];
                }

                $sqlupd = $sheel->db->query("
                    SELECT id, code, gender
                        FROM " . DB_PREFIX . "size_types
                    WHERE  (gender = '" . substr($valuecust['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                    ORDER BY code
                ");
                while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {
                    $res['staffcode'] =  $valuecust['code'];
                    $res['positioncode'] =  $valuecust['positionCode'];
                    $res['departmentcode'] =  $valuecust['departmentCode'];
                    $ismavailable = $sheel->admincp_customers->is_staff_measurements_available($companycode, $customer['customer_ref'], $res['staffcode'], $sm, $valuecust['gender'], $res['code']);
                    $autosizearray = array();
                    if ($ismavailable == '0') {
                        $autosizearray = $sheel->admincp_customers->calculate_staff_size($sm, $valuecust['gender'], $res['code']);
                        $res['fit'] = $autosizearray['Fit'];
                        $res['cut'] = $autosizearray['Cut'];
                        $res['size'] = $autosizearray['Size'];
                        $res['error'] = '[Auto Suggest]';
                    }
                    else {
                        $res['fit'] = '';
                        $res['cut'] = '';
                        $res['size'] = '';
                        $res['error'] = $ismavailable;
                    }
                   
                    $res['type'] = $res['code'];
                    $suggestdata[] = $res;
                }
            }
            //die ('test');
            $sheel->xlsx->size_xlsx_to_db($suggestdata, $custstaffs, $customer['customer_ref'], $_SESSION['sheeldata']['user']['userid'], 0);


        } 
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] =='cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_sizes
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/sizes/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/sizes/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] .'_SIZE'.'.'. pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader  = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn()!='E') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/'.$customer['customer_id'].'/sizes/?error=invalidff');
                    }
                    $sheetData=$spreadsheet->getSheet(0)->toArray();
                    $fileinfo = serialize($sheetData);
                    if (isset($sheel->GPC['containsheader']) and $sheel->GPC['containsheader'] == '1') {
                        unset($sheetData[0]);
                    }
                    $sheel->db->query("
                            INSERT INTO " . DB_PREFIX . "bulk_sessions
                            (id, user_id, dateupload, uploadtype, linescount, linesuploaded, picturesuploaded, haserrors, fileinfo)
                            VALUES (
                            NULL,
                            '" . $_SESSION['sheeldata']['user']['userid'] . "',
                            '" . $sheel->db->escape_string(DATETIME24H) . "',
                            'SIZE',
                            '".(intval($spreadsheet->getSheet(0)->getHighestDataRow())-1)."',
                            '0',
                            '0',
                            '0',
                            '" . $sheel->db->escape_string($fileinfo) . "')
                        ", 0, null, __FILE__, __LINE__);
                    $bulk_id = $sheel->db->insert_id();
                    unset($fileinfo);
                    $tempcuststaffs = array();
                    $custstaffs = array();

                    $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $tempcuststaffs = $apiResponse->getData();
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        die($sheel->template->parse_template_phrases('message'));
                    }
                    foreach ($tempcuststaffs as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            
                            if ($key1 == 'code') {
                                $code = $value1;
                            }
                            if ($key1 == 'positionCode') {
                                $name = $value1;
                            }
                            if ($key1 == 'departmentCode') {
                                $name = $name . '|' . $value1;
                            }
                        }
                        $custstaffs += [$code => $name];
                    }
                    $sheel->xlsx->size_xlsx_to_db($sheetData, $custstaffs, $customer['customer_ref'], $_SESSION['sheeldata']['user']['userid'], $bulk_id);
    
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                }
                $sql = $sheel->db->query("
                    SELECT id, staffcode, positioncode, departmentcode, fit, cut, size, type,  customerno
                        FROM " . DB_PREFIX . "bulk_tmp_sizes 
                    WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
                    ORDER BY staffcode
                ");
                $errorCount=0;
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    $sheel->dynamics->init_dynamics('erStaffSizes', $companycode);
                    $addResponse = $sheel->dynamics->insert(
                        array(
                            "customerNo" => $res['customerno'],
                            "staffCode" => $res['staffcode'],
                            "positionCode" => $res['positioncode'],
                            "departmentCode" => $res['departmentcode'],
                            "fitCode" => $res['fit'],
                            "cutCode" => $res['cut'],
                            "sizeCode" => $res['size'],
                            "sizeType" => $res['type'],
                        )
                    );
                    if ($addResponse->isSuccess()) {
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_sizes
                            SET errors = '[No Errors]',
                                uploaded = '1'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
                        
                    } else {
                        $errorCount++;
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_sizes
                            SET errors = '[". $sheel->db->escape_string($addResponse->getErrorMessage())."]'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
                                                    
                       
                    }
                }
                if ($errorCount>0) {
                    $sheel->GPC['note'] = 'adderror';
                    $vars['errorMessage'] = $errorCount . ' errors encountered during add';
                }
                else {
                    $sheel->GPC['note'] = 'addsuccess';
                }
            }
        } 

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->dynamics_activities->bulkdelete($ids, 'erStaffSizes', $customer['company_id']);
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
            } else if (isset($sheel->GPC['systemid']) and $sheel->GPC['systemid'] != '') {
                
                $sheel->dynamics->init_dynamics('erStaffSizes', $companycode);
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['systemid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Size deleted', 'A customer staff size has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Staff Sizes has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }
            } else {
                $sheel->template->templateregistry['message'] = '{_no_record_selected}';
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
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
            $sheel->dynamics->init_dynamics('erStaffSizes', $companycode);
            $staffdetails = explode('|',$sheel->GPC['staffs']);
            $addResponse = $sheel->dynamics->insert(
                array(
                    "staffCode" => $staffdetails[0],
                    "positionCode" => $staffdetails[1],
                    "departmentCode" => $staffdetails[2],
                    "customerNo" => $sheel->GPC['customer_ref'],
                    "fitCode" => $sheel->GPC['fits'],
                    "cutCode" => $sheel->GPC['cuts'],
                    "sizeCode" => $sheel->GPC['sizes'],
                    "sizeType" => $sheel->GPC['itemtypes']
                    
                )
            );

            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
            } else {
                $sheel->GPC['note'] = 'adderror';
                $vars['errorMessage'] = $addResponse->getErrorMessage();
            }
        }
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Sizes';
        $tempcuststaffs = array();
        $custstaffs = array();
        $tempsizes = array();
        $sizes = array();
        $tempsizecategories = array();
        $sizecategories = array();
        $tempitemtypes = array();
        $itemtypes = array();
        $tempfits = array();
        $fits = array();
        $tempcuts = array();
        $cuts = array();
        $tempuom = array();
        $uom = array();
        $searchfilters = array(
            'staff',
            'size',
            'position',
            'department',
            'type'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'size' => '{_size}',
            'position' => '{_position}',
            'department' => '{_department}',
            'type' => '{_type}'
            
        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);

        $staffsizes = array();
        
        $sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuststaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }
        foreach ($tempcuststaffs as $key => $value) {
            foreach ($value as $key1 => $value1) {
                
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'departmentCode') {
                    $code = $code . '|' . $value1;
                }
                if ($key1 == 'positionCode') {
                    $code = $code . '|' . $value1;
                }

                if ($key1 == 'name') {
                    $name = $value1;
                }
                if ($key1 == 'departmentName') {
                    $name = $name . ' > ' . $value1;
                }
                if ($key1 == 'positionName') {
                    $name = $name . ' > ' . $value1;
                }
            }
            $custstaffs += [$code => $name];
        }
        $sheel->dynamics->init_dynamics('erSizeCategories', $companycode);
        $searchcondition = '$orderby=sizeCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsizecategories = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempsizecategories as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $sizecategories += [$code => $code. ' > ' . $name];
        }
        $sheel->dynamics->init_dynamics('erSizes', $companycode);
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsizes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempsizes as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $sizes += [$code => $code. ' > ' . $name];
        }
        $sheel->dynamics->init_dynamics('erFits', $companycode);
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempfits = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempfits as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $fits += [$code => $code. ' > ' . $name];
        }
        $sheel->dynamics->init_dynamics('erCuts', $companycode);
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuts = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempcuts as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'code') {
                    $code = $value1;
                }
                if ($key1 == 'name') {
                    $name = $value1;
                }
            }
            $cuts += [$code => $code. ' > ' . $name];
        }
        $sheel->dynamics->init_dynamics('erItemTypes', $companycode);
        $searchcondition = '$orderby=name asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempitemtypes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        foreach ($tempitemtypes as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'name') {
                    $code = $value1;
                }
            }
            $itemtypes += [$code => $code];
        }

        $form['staff_pulldown'] = $sheel->construct_pulldown('staffs', 'staffs', $custstaffs, '', 'class="draw-select"');
        $form['size_pulldown'] = $sheel->construct_pulldown('sizes', 'sizes', $sizes, '', 'class="draw-select"');
        $form['fit_pulldown'] = $sheel->construct_pulldown('fits', 'fits', $fits, 'R', 'class="draw-select"');
        $form['cut_pulldown'] = $sheel->construct_pulldown('cuts', 'cuts', $cuts, 'T', 'class="draw-select"');
        $form['itemtype_pulldown'] = $sheel->construct_pulldown('itemtypes', 'itemtypes', $itemtypes, '', 'class="draw-select"');
        $form['value']='';


        $sheel->dynamics->init_dynamics('erStaffSizes', $companycode);
        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'size': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and sizeCode eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'position': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and positionName eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'department': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and departmentName eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
                case 'type': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and sizeType eq \'' . $sheel->db->escape_string($q) . '\'';
                    break;
                }
            }
        }
        else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }
        
        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition . $ordercondition);
        if ($apiResponse->isSuccess()) {
            $staffsizes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            die($sheel->template->parse_template_phrases('message'));
        }

        $uploadedsizes = array();
        $sqlupd = $sheel->db->query("
            SELECT id, staffcode, positioncode, departmentcode, fit, cut, size, type, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_sizes
            WHERE customerno = '" . $customer['customer_ref']. "' AND uploaded = '0'
            ORDER BY staffcode, type
        ");

        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {
           
            $uploadedsizes[] = $res;
        }

        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads']='1';
        }

        $sheel->template->fetch('main', 'staff-sizes.html', 1);
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
                'staffsizes' => $staffsizes,
                'uploadedsizes' => $uploadedsizes
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();

    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bcview' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
        $sheel->template->meta['jsinclude']['footer'][] = 'admin_bccustomers';
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
        $vars['areanav'] = $areanav;
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
        $currentarea = $customer['customer_ref'];
        $vars['currentarea'] = $currentarea;
        $departments = array();
        $sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode);
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=departmentCode asc';
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
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=positionCode asc';
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
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $staff = $apiResponse->getData();
            foreach ($staff as $keystaff => $valuestaff) {
                $tempsm = array();
                $sm = array();
                $sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode);
                $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $valuestaff['code'] . '\'';
                $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                    $tempsm = $apiResponse->getData();
                } else {
                    $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                    die($sheel->template->parse_template_phrases('message'));
                }
                foreach ($tempsm as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        if ($key1 == 'measurementCode') {
                            $code = $value1;
                        }
                        if ($key1 == 'value') {
                            $name = $value1;
                        }
                    }
                    $sm += [$code => $name];
                }

                $mandatorymeasurements = explode(',', $sheel->config['mandatorymeasurements']);
                foreach ($mandatorymeasurements as $value) {
                    if ($sm[$value] =='' OR $sm[$value] == '0') {
                        $staff[$keystaff]['meetmeasurements']='<span class="badge badge--critical">{_not_met}</span>';
                        break;
                    }
                    else {
                        $staff[$keystaff]['meetmeasurements']='<span class="badge badge--success">{_available}</span>';
                    }   
                }


                $tempss = array();
                $ss = array();
                $sheel->dynamics->init_dynamics('erStaffSizes', $companycode);
                $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $valuestaff['code'] . '\'';
                $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                    $tempss = $apiResponse->getData();
                } else {
                    $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                    die($sheel->template->parse_template_phrases('message'));
                }
                foreach ($tempss as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        if ($key1 == 'sizeType') {
                            $code = $value1;
                        }
                        if ($key1 == 'sizeCode') {
                            $name = $value1;
                        }
                    }
                    $ss += [$code => $name];
                }
                $sql = $sheel->db->query("
                    SELECT code
                    FROM " . DB_PREFIX . "size_types 
                    WHERE  (gender = '" . substr($valuestaff['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    if ($ss[$res['code']] == '') {
                        $staff[$keystaff]['meetsizes']='<span class="badge badge--critical">{_not_met}</span>';
                        break;
                    }
                    else {
                        $staff[$keystaff]['meetsizes']='<span class="badge badge--success">{_available}</span>';
                    }   
                }


            }
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