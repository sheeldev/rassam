<?php
define('LOCATION', 'admin');
require_once DIR_CLASSES . '/vendor/office/autoload.php';
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
    'spinner',
    'addition',
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
    $disabled = '';
    if ($_SESSION['sheeldata']['user']['entityid'] != '0') {
        $sqldefault = $sheel->db->query("
                SELECT bc_code
                FROM " . DB_PREFIX . "companies 
                WHERE company_id = '" . $_SESSION['sheeldata']['user']['entityid'] . "' 
                LIMIT 1");
        if ($sheel->db->num_rows($sqldefault) > 0) {
            while ($res = $sheel->db->fetch_array($sqldefault, DB_ASSOC)) {
                $defaulcompany = $res['bc_code'];
            }
        }
        $form['company'] = $defaulcompany;

        if (isset($sheel->GPC['company']) && $sheel->GPC['company'] != $defaulcompany) {
            $sheel->admincp->print_action_failed('{_no_entity_access}', $sheel->GPC['returnurl']);
            exit();
        } else {
            $disabled = 'disabled';
        }
        if (isset($sheel->GPC['no'])) {
            $sql = $sheel->db->query("
            SELECT customer_id, customer_ref, company_id
                FROM " . DB_PREFIX . "customers 
            WHERE customer_id = '" . $sheel->GPC['no'] . "'
            LIMIT 1
            ");

            if ($sheel->db->num_rows($sql) > 0) {
                $customer = $sheel->db->fetch_array($sql, DB_ASSOC);
                if ($customer['company_id'] != $_SESSION['sheeldata']['user']['entityid']) {
                    $sheel->admincp->print_action_failed('{_no_entity_access}', $sheel->GPC['returnurl']);
                    exit();
                } else {
                    $customerno = $customer['customer_id'];
                }
            }
        }

    } else {
        if (isset($sheel->GPC['no'])) {
            $customerno = $sheel->GPC['no'];
        }
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
        $form['company'] = (isset($sheel->GPC['company']) ? $sheel->GPC['company'] : $defaulcompany);
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
        $form['company_pulldown'] = $sheel->construct_pulldown('company', 'company', $companies, $form['company'], $disabled . ' class="draw-select" onchange="this.form.submit()"');

        if (!$sheel->dynamics->init_dynamics('erCustomerList', $form['company'])) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
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
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }

        foreach ($customers as &$customer) {
            if (isset($customer['blocked']) && $customer['blocked'] == '_x0020_') {
                $customer['blocked'] = '<span class="label label-danger">{_no}</span>';
            } else {
                $customer['blocked'] = '<span class="label label-success">{_yes} - ' . $customer['blocked'] . '</span>';
            }
        }
        unset($customer);
        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);
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
                'slpage' => $sheel->slpage,
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
                if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
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
            if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $addResponse = $sheel->dynamics->insert(
                array(
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
        if (!$sheel->dynamics->init_dynamics('erDepartments', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $tempdep = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
        if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $departments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $sheel->template->fetch('main', 'customer-departments.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
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
                if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
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
            if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $addResponse = $sheel->dynamics->insert(
                array(
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

        if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustdepartments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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



        if (!$sheel->dynamics->init_dynamics('erPositions', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }

        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $temppos = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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

        if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $custpositions = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $sheel->template->fetch('main', 'customer-positions.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
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
        $sheel->template->meta['jsinclude']['footer'][] = 'admin_staffs';
        $sheel->template->meta['jsinclude']['footer'][] = 'admin_prompts';
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
            $positions = $departments = array();
            $positionsuccess = $departmentsuccess = false;
            if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=positionCode asc';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $positions = $apiResponse->getData();
                $positionsuccess = true;
            }
            if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=departmentCode asc';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);

            if ($apiResponse->isSuccess()) {
                $departments = $apiResponse->getData();
                $departmentsuccess = true;
            }
            if ($positionsuccess and $departmentsuccess) {
                $reader = new Xlsx();
                $spreadsheet = $reader->load(DIR_OTHER . 'staffsample.xlsx');
                $sheet = $spreadsheet->getSheet('1');
                $last_row = 1;
                foreach ($positions as $key => $value) {
                    $sheet->setCellValue('A' . $last_row, $value['positionCode'] . '>' . $value['positionName']);
                    $sheet->setCellValue('B' . $last_row, $value['positionName']);
                    $sheet->setCellValue('C' . $last_row, $value['departmentCode']);
                    $last_row++;
                }
                $sheet = $spreadsheet->getSheet('2');
                $last_row = 1;
                foreach ($departments as $key => $value) {
                    $sheet->setCellValue('A' . $last_row, $value['departmentCode'] . '>' . $value['departmentName']);
                    $sheet->setCellValue('B' . $last_row, $value['departmentName']);
                    $last_row++;
                }
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
                $writer->save(DIR_TMP . 'xlsx/staffsample-' . $customer['customer_ref'] . '.xlsx');
                $samplefile = file_get_contents(DIR_TMP . 'xlsx/staffsample-' . $customer['customer_ref'] . '.xlsx');
                $sheel->common->download_file($samplefile, "staffsample-" . $customer['customer_ref'] . ".xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            } else {
                $samplefile = file_get_contents(DIR_OTHER . 'staffsample.xlsx');
                $sheel->common->download_file($samplefile, "staffsample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            }
            exit();
        }
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] == 'cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_staffs
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/staffs/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/staffs/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] . '_STAFF' . '.' . pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);

                    //die ($spreadsheet->getSheet(0)->getHighestDataColumn().'-------'.$spreadsheet->getSheet(0)->getHighestDataRow());
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn() != 'D') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/staffs/?error=invalidff');
                    }

                    $sheetData = $spreadsheet->getSheet('0')->toArray();
                    $sheetData = array_filter($sheetData, function ($row) {
                        return !empty($row[0]);
                    });
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
                            '" . (intval($spreadsheet->getSheet(0)->getHighestDataRow()) - 1) . "',
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
                    WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
                    ORDER BY code
                ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
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
                            SET errors = '[" . $sheel->db->escape_string($addResponse->getErrorMessage()) . "]'
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
                if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
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
            if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
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
            //die ($sheel->GPC['form']['staffcode'] . '-' . $sheel->GPC['positions'] . '-' . $sheel->GPC['departments'] . '-' . $sheel->GPC['customer_ref']);
            if ($addResponse->isSuccess()) {
                $sheel->GPC['note'] = 'addsuccess';
            } else {
                //die ($addResponse->getErrorMessage());
                $sheel->GPC['note'] = 'adderror';
            }
        }
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'deletemeasurement') {
                if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['xid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Measurememnt deleted', 'A customer staff measurement has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Staff Measurements has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'deletesize') {
                if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
                $deleteResponse = $sheel->dynamics->delete($sheel->GPC['xid']);
                $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Size deleted', 'A customer staff Size has been successfully deleted.');
                if ($deleteResponse->isSuccess()) {
                    $sheel->template->templateregistry['message'] = 'A Staff Size has been successfully deleted.';
                    die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));
                } else {
                    $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                    die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                }
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'deletesizes') {
                if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
                $xidarray = explode('|', $sheel->GPC['xid']);
                foreach ($xidarray as $xid) {
                    $deleteResponse = $sheel->dynamics->delete($xid);
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Staff Size deleted', 'A customer staff Size has been successfully deleted.');
                    if ($deleteResponse->isSuccess()) {
                    } else {
                        $sheel->template->templateregistry['message'] = $deleteResponse->getErrorMessage();
                        //die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
                    }
                }
                $sheel->template->templateregistry['message'] = 'Staff Sizes for the selected categories successfully deleted.';
                die(json_encode(array('response' => '1', 'message' => $sheel->template->parse_template_phrases('message'))));

            }
            if (($key = array_search('spinner', $sheel->template->meta['cssinclude'])) !== false) {
                unset($sheel->template->meta['cssinclude'][$key]);
            }
            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
                if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
                if ($sheel->GPC['validateaction'] == '1') {
                    $updateResponse = $sheel->dynamics->update(
                        $sheel->GPC['staffid'],
                        array(
                            "@odata.etag" => $sheel->GPC['staffetag'],
                            "name" => $sheel->GPC['staffname'],
                            "gender" => $sheel->GPC['form']['gender'],
                            "validated" => $sheel->GPC['isrevoke'] == '1' ? false : true,
                            "validatedBy" => $sheel->GPC['isrevoke'] == '1' ? '' : 'Portal: ' . $_SESSION['sheeldata']['user']['firstname'] . ' ' . $_SESSION['sheeldata']['user']['lastname']
                        )
                    );
                } else {
                    $updateResponse = $sheel->dynamics->update(
                        $sheel->GPC['staffid'],
                        array(
                            "@odata.etag" => $sheel->GPC['staffetag'],
                            "name" => $sheel->GPC['staffname'],
                            "gender" => $sheel->GPC['form']['gender'],
                            "positionCode" => $sheel->GPC['positions'],
                            "departmentCode" => $sheel->GPC['departments'],
                            "particularity1" => $sheel->GPC['particularity1'],
                            "particularity2" => $sheel->GPC['particularity2'],
                            "particularity3" => $sheel->GPC['particularity3']
                        )
                    );
                }

                if ($updateResponse->isSuccess()) {
                    $sheel->GPC['note'] = 'updatesuccess';
                } else {
                    $sheel->GPC['note'] = 'updateerror';
                    $vars['errorMessage'] = $updateResponse->getErrorMessage();
                }
            }
            $staff = array();
            $tempcustdepartments = array();
            $custdepartments = array();
            $tempcustpositions = array();
            $custpositions = array();

            if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=code eq \'' . $sheel->GPC['staffno'] . '\'';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $staff = $apiResponse->getData()[0];
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }

            $staff['customer_id'] = $sheel->admincp_customers->get_customer_id($staff['customerNo']);
            if ($staff['validated'] == '1') {
                $sheel->show['isvalid'] = true;
                $staff['validatedflag'] = '<span class="fr"><img src="/application/assets/images/v5/ico_dot_green.gif" border="0" alt="active" title="{_valid}" /></span>';
            } else {
                $sheel->show['isvalid'] = false;
                $staff['validatedflag'] = '<span class="fr"><img src="/application/assets/images/v5/ico_dot_red.gif" border="0" alt="active" title="{_invalid}" /></span>';
            }
            if ($staff['validatedBy'] == '') {
                $staff['validatedBy'] = '--';
            }
            $gender = array('Male' => '{_male}', 'Female' => '{_female}');
            $entities = [
                'erCustomerDepartments' => 'departments',
                'erCustomerPositions' => 'positions',
                'erUOM' => 'uom',
                'erSizes' => 'sizes',
                'erFits' => 'fits',
                'erCuts' => 'cuts',
                'erItemTypes' => 'itemtypes',
                'erMeasurementCategories' => 'mcategory',
                'erStaffParticularities' => 'particularities'
            ];
            $data = [];
            foreach ($entities as $entity => $key) {
                if (($sheel->cache->fetch($entity)) === false) {
                    if (!$sheel->dynamics->init_dynamics($entity, $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
                    if ($key == 'departments') {
                        $searchcondition = '$filter=customerNo eq \'' . $staff['customerNo'] . '\'&$orderby=departmentCode asc';
                    } elseif ($key == 'positions') {
                        $searchcondition = '$filter=customerNo eq \'' . $staff['customerNo'] . '\'&$orderby=positionCode asc';
                    } else if ($key == 'mcategory') {
                        $searchcondition = '$orderby=name asc';
                    } else if ($key == 'itemtypes') {
                        $searchcondition = '$orderby=name asc';
                    } else {
                        $searchcondition = '$orderby=code asc';
                    }
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $data[$key] = $apiResponse->getData();
                        $sheel->cache->store($entity, $data[$key], 10);
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                        exit();
                    }
                } else {
                    $data[$key] = $sheel->cache->fetch($entity);
                }

            }
            foreach ($data['departments'] as $value) {
                $custdepartments[$value['departmentCode']] = $value['departmentCode'] . ' > ' . $value['departmentName'];
            }

            foreach ($data['positions'] as $value) {
                $custpositions[$value['positionCode']] = $value['positionCode'] . ' > ' . $value['positionName'];
            }
            $uom = array_column($data['uom'], 'name', 'code');
            $particularities = array_column($data['particularities'], 'name', 'code');
            $sizes = array_column($data['sizes'], 'code', 'code');
            $fits = array_column($data['fits'], 'code', 'code');
            $cuts = array_column($data['cuts'], 'code', 'code');
            $itemtypes = [];
            $itemtypesdb = [];
            $sqltype1 = $sheel->db->query("
                SELECT code
                FROM " . DB_PREFIX . "size_types
                WHERE (gender = '" . substr($staff['gender'], 0, 1) . "'  or gender='U') AND needsize = '1'
            ");
            while ($rowtype1 = $sheel->db->fetch_array($sqltype1, DB_ASSOC)) {
                $itemtypesdb[] = $rowtype1['code'];
            }
            foreach ($data['itemtypes'] as $value) {
                foreach ($itemtypesdb as $rowtype1) {
                    if ($value['name'] == $rowtype1) {
                        $itemtypes[$value['name']] = $value['name'];
                    }
                }
            }
            $mcategory = [];
            foreach ($data['mcategory'] as $value) {
                $mcategory[$value['code']] = $value['code'] . ' > ' . $value['name'];
            }
            $form['department_pulldown'] = $sheel->construct_pulldown('departments', 'departments', $custdepartments, $staff['departmentCode'], 'class="draw-select"');
            $form['position_pulldown'] = $sheel->construct_pulldown('positions', 'positions', $custpositions, $staff['positionCode'], 'class="draw-select"');
            $form['gender'] = $sheel->construct_pulldown('gender', 'form[gender]', $gender, $staff['gender'], 'class="draw-select"');
            $form['company'] = $sheel->GPC['company_id'];
            $form['companycode'] = $companycode;
            $form['staffetag'] = htmlspecialchars($staff['@odata.etag']);
            $form['uom'] = $sheel->construct_pulldown('uoms', 'uoms', $uom, $sheel->common_sizingrule->get_default_uom(array_key_first($mcategory)), 'class="draw-select"');
            $form['mcategory'] = $sheel->construct_pulldown('mcategories', 'mcategories', $mcategory, '', 'onchange="update_measurement_uom(\'mcategories\')" class="draw-select"');
            $form['size'] = $sheel->construct_pulldown('sizes', 'sizes', $sizes, '', 'class="draw-select"');
            $form['fit'] = $sheel->construct_pulldown('fits', 'fits', $fits, '', 'class="draw-select"');
            $form['cut'] = $sheel->construct_pulldown('cuts', 'cuts', $cuts, '', 'class="draw-select"');
            $form['particularity1'] = $sheel->construct_pulldown_withempty('particularity1', 'particularity1', $particularities, $staff['particularity1'], 'class="draw-select"');
            $form['particularity2'] = $sheel->construct_pulldown_withempty('particularity2', 'particularity2', $particularities, $staff['particularity2'], 'class="draw-select"');
            $form['particularity3'] = $sheel->construct_pulldown_withempty('particularity3', 'particularity3', $particularities, $staff['particularity3'], 'class="draw-select"');

            $form['itemtype'] = $sheel->construct_pulldown('itemtypes', 'itemtypes', $itemtypes, '', 'class="draw-select"');
            $staffmeasurements = [];
            $staffsizes = [];
            if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=staffCode eq \'' . $staff['code'] . '\'';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $staffmeasurements = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }
            $mandatorymeasurements = explode(',', $sheel->config['mandatorymeasurements']);
            $allmeasurementsmet = true;
            foreach ($mandatorymeasurements as $mandatory) {
                $measurementfound = false;
                foreach ($staffmeasurements as $measurement) {
                    if ($measurement['measurementCode'] == $mandatory) {
                        $measurementfound = true;
                        break;
                    }
                }
                if (!$measurementfound) {
                    $allmeasurementsmet = false;
                    break;
                }
            }
            if ($allmeasurementsmet) {
                $staff['meetmeasurements'] = '<span class="badge badge--success">{_available}</span>';
            } else {
                $staff['meetmeasurements'] = '<span class="badge badge--critical">{_not_met}</span>';
            }

            foreach ($staffmeasurements as &$measurement) {
                $measurement['etag'] = htmlspecialchars($measurement['@odata.etag']);
                $extra = 'class="draw-select" onchange="update_staff_details(\'uom_' . $measurement['systemId'] . '\',\'uom\',\'' . $measurement['systemId'] . '\',\'' . $companycode . '\',\'0\')"';
                $measurement['uomCode'] = $sheel->construct_pulldown('uom_' . $measurement['systemId'], 'uom_' . $measurement['systemId'], $uom, $measurement['uomCode'], $extra);
            }
            if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=staffCode eq \'' . $staff['code'] . '\'';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $staffsizes = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }
            $temparray = [];
            $finalarray = [];
            $catarray = [];
            $sqlcat = $sheel->db->query("
                SELECT id, code, name
                FROM " . DB_PREFIX . "size_type_categories
                ORDER BY  sort ASC
                ");
            if ($sheel->db->num_rows($sqlcat) > 0) {
                while ($rowcat = $sheel->db->fetch_array($sqlcat, DB_ASSOC)) {
                    if (!isset($finalarray[$rowcat['id']])) {
                        $finalarray[$rowcat['id']] = [];
                        $catarray[] = $rowcat;
                    }
                }
            }
            $allsizesmet = true;
            $sql = $sheel->db->query("
                SELECT code
                FROM " . DB_PREFIX . "size_types 
                WHERE  (gender = '" . substr($staff['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                ");
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {

                $sizefound = false;
                foreach ($staffsizes as $staffsize) {
                    if ($staffsize['sizeType'] == $res['code']) {
                        $sizefound = true;
                        break;
                    }
                }
                if (!$sizefound) {
                    $allsizesmet = false;
                    break;
                }
            }
            if ($allsizesmet) {
                $staff['meetsizes'] = '<span class="badge badge--success">{_available}</span>';
            } else {
                $staff['meetsizes'] = '<span class="badge badge--critical">{_not_met}</span>';
            }
            $sheel->show['validatebutton'] = '0';
            if ($allmeasurementsmet and $allsizesmet) {
                $sheel->show['validatebutton'] = '1';
            }
            foreach ($staffsizes as &$staffsize) {
                $staffsize['etag'] = htmlspecialchars($staffsize['@odata.etag']);
                $sqltype = $sheel->db->query("
                        SELECT t.categoryid, t.isdefault, c.code as categorycode, c.name as categoryname
                        FROM " . DB_PREFIX . "size_types t
                        LEFT JOIN " . DB_PREFIX . "size_type_categories c ON t.categoryid = c.id
                        WHERE t.code = '" . $staffsize['sizeType'] . "'
                        order by t.categoryid, isdefault DESC
                        LIMIT 1
                        ");
                $rowtype = $sheel->db->fetch_array($sqltype, DB_ASSOC);
                $staffsize['categoryid'] = $rowtype['categoryid'];
                $staffsize['isdefault'] = $rowtype['isdefault'];
                $staffsize['isdefaulticon'] = $rowtype['isdefault'] == '1' ? '<span class="fr"><img src="/application/assets/images/v5/ico_checkmark.png" border="0" alt="active" title="{_default}" /></span>' : '';
                $staffsize['categorycode'] = $rowtype['categorycode'];
                $staffsize['categoryname'] = $rowtype['categoryname'];
                foreach ($staffsize as $key => $value) {
                    if ($key === 'sizeCode' || $key === 'fitCode' || $key === 'cutCode') {
                        if ($key === 'sizeCode') {
                            $temparray = $sizes;
                        } else if ($key === 'fitCode') {
                            $temparray = $fits;
                        } else if ($key === 'cutCode') {
                            $temparray = $cuts;
                        }
                        $extra = 'class="draw-select" onchange="update_staff_details(\'' . $key . '_' . $staffsize['systemId'] . '\',\'' . $key . '\',\'' . $staffsize['systemId'] . '\',\'' . $companycode . '\',\'0\')"';
                        $staffsize[$key . '_type'] = $sheel->construct_pulldown($key . '_' . $staffsize['systemId'], $key . '_' . $staffsize['systemId'], $temparray, $staffsize[$key], $extra);
                    }
                }
                $finalarray[$staffsize['categoryid']][] = $staffsize;
            }
            foreach ($catarray as &$cat) {
                if (isset($finalarray[$cat['id']]) && count($finalarray[$cat['id']]) > 0) {
                    $sheel->GPC['cat_' . $cat['id']] = 'Y';
                } else {
                    $sheel->GPC['cat_' . $cat['id']] = 'N';
                }
                $systemids = '';
                foreach ($staffsizes as &$staffsize) {
                    if ($staffsize['categoryid'] == $cat['id']) {
                        $systemids .= $staffsize['systemId'] . '|';
                    }
                    if ($staffsize['categoryid'] == $cat['id'] and $staffsize['isdefault'] == '1') {
                        $extra = 'class="draw-select" onchange="update_staff_sizes(\'catsizeCode_' . $cat['id'] . '\',\'' . $cat['code'] . '\', \'sizeCode\' , \'' . $companycode . '\')"';
                        $cat['catsizeCode'] = $sheel->construct_pulldown('catsizeCode_' . $cat['id'], 'catsizeCode_' . $cat['id'], $sizes, $staffsize['sizeCode'], $extra);
                        $extra = 'class="draw-select" onchange="update_staff_sizes(\'catfitCode_' . $cat['id'] . '\',\'' . $cat['code'] . '\' , \'fitCode\'  , \'' . $companycode . '\')"';
                        $cat['catfitCode'] = $sheel->construct_pulldown('catfitCode_' . $cat['id'], 'catfitCode_' . $cat['id'], $fits, $staffsize['fitCode'], $extra);
                        $extra = 'class="draw-select" onchange="update_staff_sizes(\'catcutCode_' . $cat['id'] . '\',\'' . $cat['code'] . '\', \'cutCode\' ,\'' . $companycode . '\')"';
                        $cat['catcutCode'] = $sheel->construct_pulldown('catcutCode_' . $cat['id'], 'catcutCode_' . $cat['id'], $cuts, $staffsize['cutCode'], $extra);
                    }
                }
                $cat['systemids'] = substr($systemids, 0, -1);
            }

            $image_file_name = DIR_TMP_MODEL . $staff['code'] . '_Model.png';
            if (file_exists($image_file_name)) {
                $staff['model'] = '/application/uploads/cache/staffmodels/' . $staff['code'] . '_Model.png';
            } else {
                if (!$sheel->smplx->init_smplx('generateModel')) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
                $processedMeasurements = [];
                $smplxMeasurementsConfig = json_decode($sheel->config['smplxmeasurements'], true);
                foreach ($staffmeasurements as &$measurement) {
                    $measurementValue = $sheel->common_sizingrule->get_smplx_measurement($measurement['measurementCode']);
                    if ($measurementValue != '') {
                        $processedMeasurements[$measurement['measurementCode']] = $measurementValue;
                        $requestData[$measurementValue] = $measurement['value'];
                    }
                }
                $requestData['gender'] = strtolower($staff['gender']);
                $requestData['pose_description'] = 'superman';
                $requestData['output_type'] = 'png';
                $apiResponse = $sheel->smplx->get($requestData);
                if ($apiResponse->isSuccess()) {
                    $apiResponse = $apiResponse->getData();
                    $imageData = base64_decode($apiResponse);
                    file_put_contents($image_file_name, $imageData);
                } else {
                    $sheel->show['noimage'] = true;
                    $vars['noimagemessage'] = $apiResponse->getErrorMessage() . ': ' . $apiResponse->getErrorDetails();
                }
            }
            $sortOrder = [
                'NECK' => 1,
                'CHST' => 2,
                'UWST' => 3,
                'LWST' => 4,
                'HIPS' => 5,
                'JKTL' => 6,
                'SLVL' => 7,
                'TRSL' => 8,
                'HEI' => 9,
                'WEI' => 10
            ];

            // Sort the staffmeasurements array based on the sort order
            usort($staffmeasurements, function ($a, $b) use ($sortOrder) {
                $aOrder = $sortOrder[$a['measurementCode']] ?? PHP_INT_MAX; // Default to max if not found
                $bOrder = $sortOrder[$b['measurementCode']] ?? PHP_INT_MAX; // Default to max if not found
                return $aOrder - $bOrder;
            });
            $parseArray = [
                'catarray' => $catarray,
                'staffmeasurements' => $staffmeasurements,
            ];
            foreach ($finalarray as $key => $value) {
                $parseArray[$key] = $value;
            }
            $areanav = 'customers_customers';
            $vars['areanav'] = $areanav;
            $currentarea = $sheel->GPC['staffno'];
            $vars['currentarea'] = $currentarea;
            $sheel->template->fetch('main', 'staff.html', 1);
            $sheel->template->parse_hash(
                'main',
                array(
                    'staff' => $staff,
                    'form' => $form
                )
            );
            $sheel->template->parse_loop(
                'main',
                $parseArray
            );
            $sheel->template->pprint('main', $vars);
            exit();
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
        if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=departmentCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustdepartments = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $custdepartments += [$code => $code . ' > ' . $name];
        }


        if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=positionCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcustpos = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $custpositions += [$code => $code . ' > ' . $name];
        }
        $form['department_pulldown'] = $sheel->construct_pulldown('departments', 'departments', $custdepartments, '', 'class="draw-select"');
        $form['position_pulldown'] = $sheel->construct_pulldown('positions', 'positions', $custpositions, '', 'class="draw-select"');
        $form['gender'] = $sheel->construct_pulldown('gender', 'form[gender]', $gender, '', 'class="draw-select"');
        $form['staffname'] = '';
        $form['staffcode'] = '';
        if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $custstaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
        $form['staffcode'] = max($ids) > 0 ? $customer['customer_ref'] . '-' . intval(max($ids) + 1) : $customer['customer_ref'] . '-1';
        $form['staffid'] = max($ids) > 0 ? '' . intval(max($ids) + 1) : '1';
        $uploadedstaffs = array();
        $sqlupd = $sheel->db->query("
            SELECT id, code, name, gender, positioncode, departmentcode, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_staffs 
            WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
            ORDER BY code
        ");
        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {

            $uploadedstaffs[] = $res;
        }
        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads'] = '1';
        }
        $tempsm = array();
        $sm = array();
        $sm1 = array();
        if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsm = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $currentStaff = '';
        $lastKey = array_key_last($tempsm);
        foreach ($tempsm as $key => $value) {
            if ($value['staffCode'] != $currentStaff and $currentStaff != '') {
                $sm += [$currentStaff => $sm1];
                $sm1 = array();
            }
            if ($key == $lastKey) {
                $sm1 += [$value['measurementCode'] => $value['value']];
                $sm += [$currentStaff => $sm1];
            }

            $sm1 += [$value['measurementCode'] => $value['value']];
            $currentStaff = $value['staffCode'];
        }
        $tempss = array();
        $ss = array();
        $ss1 = array();
        if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempss = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $particularities = [];
        $tempparticularities = [];
        if (!$sheel->dynamics->init_dynamics('erStaffParticularities', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempparticularities = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        foreach ($tempparticularities as $tempparticularity) {
            $particularities[$tempparticularity['code']] = $tempparticularity['name'];
        }
        $currentStaff = '';
        $lastKey = array_key_last($tempss);
        foreach ($tempss as $key => $value) {
            if ($value['staffCode'] != $currentStaff and $currentStaff != '') {
                $ss += [$currentStaff => $ss1];
                $ss1 = array();
            }
            if ($key == $lastKey) {
                $ss1 += [$value['sizeType'] => $value['sizeCode']];
                $ss += [$currentStaff => $ss1];
            }
            $ss1 += [$value['sizeType'] => $value['sizeCode']];
            $currentStaff = $value['staffCode'];
        }
        $mandatorymeasurements = explode(',', $sheel->config['mandatorymeasurements']);
        foreach ($custstaffs as $keystaff => $valuestaff) {
            if ($custstaffs[$keystaff]['validated'] == '1') {
                $custstaffs[$keystaff]['validated'] = '<span class="badge badge--success">{_yes}</span>';
            } else {
                $custstaffs[$keystaff]['validated'] = '<span class="badge badge--attention">{_no}</span>';
            }
            if ($custstaffs[$keystaff]['validatedBy'] == '') {
                $custstaffs[$keystaff]['validatedBy'] = '--';
            }
            if ($custstaffs[$keystaff]['particularity1'] != '') {
                $custstaffs[$keystaff]['particularity1'] = '<span class="badge badge--critical">' . $valuestaff['particularity1'] . '</span>';
                $custstaffs[$keystaff]['particularity1desc'] = $particularities[$valuestaff['particularity1']];
            } else {
                $custstaffs[$keystaff]['particularity1desc'] = '';
            }
            if ($custstaffs[$keystaff]['particularity2'] != '') {
                $custstaffs[$keystaff]['particularity2'] = '<span class="badge badge--critical">' . $valuestaff['particularity2'] . '</span>';
                $custstaffs[$keystaff]['particularity2desc'] = $particularities[$valuestaff['particularity2']];
            } else {
                $custstaffs[$keystaff]['particularity2desc'] = '';
            }
            if ($custstaffs[$keystaff]['particularity3'] != '') {
                $custstaffs[$keystaff]['particularity3'] = '<span class="badge badge--critical">' . $valuestaff['particularity3'] . '</span>';
                $custstaffs[$keystaff]['particularity3desc'] = $particularities[$valuestaff['particularity3']];
            } else {
                $custstaffs[$keystaff]['particularity3desc'] = '';
            }
            $staffmeasures = $sm[$valuestaff['code']];
            foreach ($mandatorymeasurements as $manvalues) {
                if ($staffmeasures[$manvalues] == '' or $staffmeasures[$manvalues] == '0') {
                    $custstaffs[$keystaff]['meetmeasurements'] = '<span class="badge badge--critical">{_not_met}</span>';
                    break;
                } else {
                    $custstaffs[$keystaff]['meetmeasurements'] = '<span class="badge badge--success">{_available}</span>';
                }
            }
            $sql = $sheel->db->query("
                    SELECT code
                    FROM " . DB_PREFIX . "size_types 
                    WHERE  (gender = '" . substr($valuestaff['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                    ");
            while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $staffsizes = $ss[$valuestaff['code']];
                if ($staffsizes[$res['code']] == '') {
                    $custstaffs[$keystaff]['meetsizes'] = '<span class="badge badge--critical">{_not_met}</span>';
                    break;
                } else {
                    $custstaffs[$keystaff]['meetsizes'] = '<span class="badge badge--success">{_available}</span>';
                }
            }
        }
        usort($custstaffs, function ($a, $b) {
            preg_match_all('!\d+!', $a['code'], $matchesA);
            preg_match_all('!\d+!', $b['code'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });


        $sheel->template->fetch('main', 'customer-staffs.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
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
            if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }

            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
            $staffmeasurements = array();
            $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $staffmeasurements = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }

            if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }

            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $custstaffs = $apiResponse->getData();
                $mandatorymeasurements = explode(',', $sheel->config['mandatorymeasurements']);
                $reader = new Xlsx();
                $reader->setLoadSheetsOnly(["Sheet1"]);
                $spreadsheet = $reader->load(DIR_OTHER . 'measurementsample.xlsx');
                $sheet = $spreadsheet->getActiveSheet();
                $last_row = (int) $sheet->getHighestRow() + 1;
                $measurementCategories = [];
                if ($sheel->dynamics->init_dynamics('erMeasurementCategories', $companycode)) {
                    foreach ($mandatorymeasurements as $value1) {
                        $searchcondition = '$filter=code eq \'' . $value1 . '\' ';
                        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                        if ($apiResponse->isSuccess()) {
                            $measurementCategories[$value1] = $apiResponse->getData()[0];
                        } else {
                            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                            exit();
                        }
                    }
                } else {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }

                usort($custstaffs, function ($a, $b) {
                    preg_match_all('!\d+!', $a['code'], $matchesA);
                    preg_match_all('!\d+!', $b['code'], $matchesB);
                    $numA = (int) end($matchesA[0]);
                    $numB = (int) end($matchesB[0]);
                    return $numA - $numB;
                });

                foreach ($custstaffs as $value) {
                    foreach ($mandatorymeasurements as $value1) {
                        $foundValue = '';
                        foreach ($staffmeasurements as $measurement) {
                            if (isset($measurement['staffCode']) and ($measurement['staffCode'] == $value['code']) and isset($measurement['measurementCode']) and ($measurement['measurementCode'] == $value1)) {
                                $foundValue = $measurement['value'];
                                break;
                            }
                        }
                        if ($foundValue == '') {
                            $sheet->setCellValue('A' . $last_row, $value['code']);
                            $sheet->setCellValue('B' . $last_row, $value['name']);
                            $sheet->setCellValue('C' . $last_row, $value['gender']);
                            $sheet->setCellValue('D' . $last_row, $value['departmentCode'] . '>' . $value['departmentName']);
                            $sheet->setCellValue('E' . $last_row, $value['positionCode'] . '>' . $value['positionName']);
                            $sheet->setCellValue('F' . $last_row, $value1 . '>' . $measurementCategories[$value1]['name']);
                            $sheet->setCellValue('G' . $last_row, $foundValue);
                            $sheet->setCellValue('H' . $last_row, $sheel->common_sizingrule->get_default_uom($value1));
                            $last_row++;
                        }
                    }
                }

                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
                $writer->save(DIR_TMP . 'xlsx/measurementsample-' . $customer['customer_ref'] . '.xlsx');
                $samplefile = file_get_contents(DIR_TMP . 'xlsx/measurementsample-' . $customer['customer_ref'] . '.xlsx');
                $sheel->common->download_file($samplefile, "measurementsample-" . $customer['customer_ref'] . ".xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            } else {
                $samplefile = file_get_contents(DIR_OTHER . 'measurementsample.xlsx');
                $sheel->common->download_file($samplefile, "measurementsample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            }

            exit();
        }

        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] == 'cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_measurements
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/measurements/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/measurements/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] . '_MEASUREMENT' . '.' . pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn() != 'H') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/measurements/?error=invalidff');
                    }
                    $sheetData = $spreadsheet->getSheet('0')->toArray();
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
                            '" . (intval($spreadsheet->getSheet(0)->getHighestDataRow()) - 1) . "',
                            '0',
                            '0',
                            '0',
                            '" . $sheel->db->escape_string($fileinfo) . "')
                        ", 0, null, __FILE__, __LINE__);
                    $bulk_id = $sheel->db->insert_id();
                    unset($fileinfo);
                    $tempcuststaffs = array();
                    $custstaffs = array();

                    if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $tempcuststaffs = $apiResponse->getData();
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                        exit();
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
                    WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
                    ORDER BY staffcode
                ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
                    $addResponse = $sheel->dynamics->insert(
                        array(
                            "customerNo" => $res['customerno'],
                            "staffCode" => $res['staffcode'],
                            "positionCode" => $res['positioncode'],
                            "departmentCode" => $res['departmentcode'],
                            "measurementCode" => $res['measurementcategory'],
                            "value" => floatval($res['mvalue']),
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
                            SET errors = '[" . $sheel->db->escape_string($addResponse->getErrorMessage()) . "]'
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

                if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
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
            if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $staffdetails = explode('|', $sheel->GPC['staffs']);

            $addResponse = $sheel->dynamics->insert(
                array(
                    "customerNo" => $sheel->GPC['customer_ref'],
                    "staffCode" => $staffdetails[0],
                    "positionCode" => $staffdetails[1],
                    "departmentCode" => $staffdetails[2],
                    "measurementCode" => $sheel->GPC['measurements'],
                    "value" => floatval($sheel->GPC['form']['value']),
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
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
            $companycode = $sheel->admincp_customers->get_company_name($sheel->GPC['company_id'], true);
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {

                if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }

                $updateResponse = $sheel->dynamics->update(
                    $sheel->GPC['systemId'],
                    array(
                        "@odata.etag" => $sheel->GPC['measurementetag'],
                        "value" => intval($sheel->GPC['measurementvalue']),
                        "uomCode" => $sheel->GPC['uoms']
                    )
                );
                if ($updateResponse->isSuccess()) {
                    $sheel->GPC['note'] = 'updatesuccess';
                } else {
                    $sheel->GPC['note'] = 'updateerror';
                    $form['error'] = $updateResponse->getErrorMessage();
                }
            }
            $measurement = array();
            $tempuom = array();
            $uom = array();
            if (!$sheel->dynamics->init_dynamics('erUOM', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $apiResponse = $sheel->dynamics->select('');
            if ($apiResponse->isSuccess()) {
                $tempuom = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
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
            if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=systemId eq ' . $sheel->GPC['systemId'];
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $measurement = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }
            $measurement = $measurement['0'];

            $form['company'] = $sheel->GPC['company_id'];
            $form['uom_pulldown'] = $sheel->construct_pulldown('uoms', 'uoms', $uom, $measurement['uomCode'], 'class="draw-select"');
            $form['measurementetag'] = htmlspecialchars($measurement['@odata.etag']);
            $measurement['customer_id'] = $sheel->admincp_customers->get_customer_id($measurement['customerNo']);
            $areanav = 'customers_customers';
            $vars['areanav'] = $areanav;
            $currentarea = $measurement['staffCode'] . '-' . $measurement['measurementCode'];
            $vars['currentarea'] = $currentarea;
            $sheel->template->fetch('main', 'measurement.html', 1);
            $sheel->template->parse_hash(
                'main',
                array(
                    'measurement' => $measurement,
                    'form' => $form
                )
            );
            $sheel->template->pprint('main', $vars);
            exit();
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
            'name',
            'measurement',
            'position',
            'department'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'name' => '{_name}',
            'measurement' => '{_measurement}',
            'position' => '{_position}',
            'department' => '{_department}'

        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);

        $staffmeasurements = array();

        if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuststaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
        if (!$sheel->dynamics->init_dynamics('erMeasurementCategories', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=name asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempmeasurements = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $measurements += [$code => $code . ' > ' . $name];
        }
        if (!$sheel->dynamics->init_dynamics('erUOM', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $apiResponse = $sheel->dynamics->select('');
        if ($apiResponse->isSuccess()) {
            $tempuom = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
        $form['measurement_pulldown'] = $sheel->construct_pulldown('measurements', 'measurements', $measurements, '', 'onchange="update_measurement_uom(\'measurements\')" class="draw-select"');
        $form['uom_pulldown'] = $sheel->construct_pulldown('uoms', 'uoms', $uom, $sheel->common_sizingrule->get_default_uom(array_key_first($measurements)), 'class="draw-select"');
        $form['value'] = '';


        if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        //$pagination = '&$skip=' . ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'] . '&$top=' . $sheel->config['globalfilters_maxrowsdisplay'];

        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffCode, \'' . $sheel->db->escape_string($q) . '\')';
                    break;
                }
                case 'name': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffName,  \'' . $sheel->db->escape_string($q) . '\')';
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
        } else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }

        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $ordercondition . $pagination);
        if ($apiResponse->isSuccess()) {
            $staffmeasurements = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        usort($staffmeasurements, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffCode'], $matchesA);
            preg_match_all('!\d+!', $b['staffCode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            if ($numA === $numB) {
                return strcmp($a['measurementCode'], $b['measurementCode']);
            }
            return $numA - $numB;
        });
        $totalItems = count($staffmeasurements);
        $totalPages = ceil($totalItems / $sheel->config['globalfilters_maxrowsdisplay']);
        $offset = ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'];
        $pagedstaffmeasurements = array_slice($staffmeasurements, $offset, $sheel->config['globalfilters_maxrowsdisplay']);
        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);

        $uploadedmeaasurements = array();
        $sqlupd = $sheel->db->query("
            SELECT id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_measurements 
            WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
            ORDER BY staffcode
        ");

        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {

            $uploadedmeaasurements[] = $res;
        }
        usort($uploadedmeaasurements, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffcode'], $matchesA);
            preg_match_all('!\d+!', $b['staffcode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });
        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads'] = '1';
        }
        $sheel->template->fetch('main', 'staff-measurements.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'staffmeasurements' => $pagedstaffmeasurements,
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
            if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
            $staffsizes = array();
            $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $staffsizes = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }
            $types = [];
            $sqldefault = $sheel->db->query("
                SELECT code
                FROM " . DB_PREFIX . "size_types		
                WHERE isdefault = '1'
                ");
            while ($resdefault = $sheel->db->fetch_array($sqldefault, DB_ASSOC)) {
                $types[] = $resdefault;
            }
            if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $custstaffs = $apiResponse->getData();
                $reader = new Xlsx();
                $reader->setLoadSheetsOnly(["Sheet1"]);
                $spreadsheet = $reader->load(DIR_OTHER . 'sizesample.xlsx');
                $sheet = $spreadsheet->getActiveSheet();
                $last_row = (int) $sheet->getHighestRow() + 1;
                usort($custstaffs, function ($a, $b) {
                    preg_match_all('!\d+!', $a['code'], $matchesA);
                    preg_match_all('!\d+!', $b['code'], $matchesB);
                    $numA = (int) end($matchesA[0]);
                    $numB = (int) end($matchesB[0]);
                    return $numA - $numB;
                });
                foreach ($custstaffs as $key => $value) {
                    foreach ($types as $key1 => $value1) {
                        $foundValue = '';
                        foreach ($staffsizes as $size) {
                            if (isset($size['staffCode']) and ($size['staffCode'] == $value['code']) and isset($size['sizeType']) and ($size['sizeType'] == $value1['code'])) {
                                $foundValue = $size['sizeType'];
                            } else {
                                $foundValue = '';
                            }
                        }
                        if ($foundValue != '') {
                            break;
                        }
                    }
                    $sql = $sheel->db->query("
                    SELECT code,name
                    FROM " . DB_PREFIX . "size_type_categories 
                    ");
                    while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                        $sheet->setCellValue('A' . $last_row, $value['code']);
                        $sheet->setCellValue('B' . $last_row, $value['name']);
                        $sheet->setCellValue('C' . $last_row, $value['gender']);
                        $sheet->setCellValue('G' . $last_row, $res['name']);
                        $last_row++;
                    }
                }
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
                $writer->save(DIR_TMP . 'xlsx/sizesample-' . $customer['customer_ref'] . '.xlsx');
                $samplefile = file_get_contents(DIR_TMP . 'xlsx/sizesample-' . $customer['customer_ref'] . '.xlsx');
                $sheel->common->download_file($samplefile, "sizesample-" . $customer['customer_ref'] . ".xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

            } else {
                $samplefile = file_get_contents(DIR_OTHER . 'sizesample.xlsx');
                $sheel->common->download_file($samplefile, "sizesample.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            }
            exit();
        }
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'suggest') {

            $custstaffs = array();
            if (!empty($_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']])) {
                $ids = explode("~", $_COOKIE[COOKIE_PREFIX . 'inline' . $sheel->GPC['checkboxid']]);
                $response = array();
                $response = $sheel->admincp_customers->get_customer_details_bc($ids, $companycode);
                $custstaffs = $response['staffs'];
                unset($ids);
                $sheel->template->templateregistry['success'] = $response['success'];
                $sheel->template->templateregistry['errors'] = $response['errors'];
                $suggestdata = array();
                foreach ($custstaffs as $keycust => $valuecust) {
                    $tempsm = array();
                    $sm = array();
                    if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and staffCode eq \'' . $valuecust['code'] . '\'';
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $tempsm = $apiResponse->getData();
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                        exit();
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
                        $res['staffcode'] = $valuecust['code'];
                        $res['staffname'] = $valuecust['name'];
                        $res['positioncode'] = $valuecust['positionCode'];
                        $res['departmentcode'] = $valuecust['departmentCode'];
                        $ismavailable = $sheel->admincp_customers->is_staff_measurements_available($companycode, $customer['customer_ref'], $res['staffcode'], $sm, $valuecust['gender'], $res['code']);
                        $autosizearray = array();

                        $autosizearray = $sheel->admincp_customers->calculate_staff_size($sm, $valuecust['gender'], $res['code']);
                        $res['fit'] = $autosizearray['Fit'];
                        $res['cut'] = $autosizearray['Cut'];
                        $res['size'] = $autosizearray['Size'];
                        if ($res['fit'] != '' and $res['cut'] != '' and $res['size'] != '') {
                            $res['error'] = '[Auto Suggest]';
                        }
                        if (count($ismavailable) != 0) {
                            foreach ($ismavailable as $element) {
                                if ($res['error'] == '') {
                                    $res['error'] .= "<br>";
                                }
                                $res['error'] .= print_r($element, true) . "<br>";
                            }
                        }
                        $res['type'] = $res['code'];
                        $suggestdata[] = $res;
                    }
                }
                $sheel->xlsx->size_xlsx_to_db($suggestdata, $custstaffs, $customer['customer_ref'], $_SESSION['sheeldata']['user']['userid'], 0);
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
                if (isset($sheel->GPC['specific']) and !empty($sheel->GPC['specific'])) {
                    $custstaffs = explode('|', $sheel->GPC['specific']);
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and code eq \'' . $custstaffs[0] . '\''; // and positionCode eq \'' . $custstaffs[1] . '\' and departmentCode eq \'' . $custstaffs[2] . '\'';
                } else {
                    $sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "bulk_tmp_sizes
                        WHERE customerno = '" . $customer['customer_ref'] . "'
                            AND uploaded = '0'
                        ", 0, null, __FILE__, __LINE__);
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
                }
                if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }

                $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                    $custstaffs = $apiResponse->getData();
                } else {
                    $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                    $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                    exit();
                }
            }
            $suggestdata = array();
            $tempsm = array();
            if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
            $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
            if ($apiResponse->isSuccess()) {
                $tempsm = $apiResponse->getData();
            } else {
                $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                exit();
            }
            $measurementsByStaff = [];
            foreach ($tempsm as $measurement) {
                $staffCode = $measurement['staffCode'];
                if (!isset($measurementsByStaff[$staffCode])) {
                    $measurementsByStaff[$staffCode] = [];
                }
                $measurementsByStaff[$staffCode][] = $measurement;
            }
            foreach ($custstaffs as $keycust => $valuecust) {
                $staffCode = $valuecust['code'];
                $sm = array();
                if (isset($measurementsByStaff[$staffCode])) {
                    foreach ($measurementsByStaff[$staffCode] as $measurement) {
                        $code = $measurement['measurementCode'];
                        $name = [
                            'value' => $measurement['value'],
                            'uomCode' => $measurement['uomCode']
                        ];
                        $sm[$code] = $name;
                    }
                }
                $uomconversions = json_decode($sheel->config['uomconversiontodefault'], true);
                foreach ($sm as $keysm => &$valuesm) {
                    if (isset($uomconversions[$valuesm['uomCode']])) {
                        list($conversion, $touom) = explode('|', $uomconversions[$valuesm['uomCode']]);
                        if ($valuesm['uomCode'] == 'FT') {
                            list($feet, $inches) = explode('.', $valuesm['value']);
                            $totalInches = ($feet * 12) + $inches;
                            $valuesm['value'] = $totalInches * 2.54;
                            $valuesm['uomCode'] = $touom;
                        } else {
                            $valuesm['value'] = $valuesm['value'] * $conversion;
                            $valuesm['uomCode'] = $touom;
                        }
                    }
                }

                $sqlupd = $sheel->db->query("
                    SELECT id, code, gender
                        FROM " . DB_PREFIX . "size_types
                    WHERE  (gender = '" . substr($valuecust['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                    ORDER BY code
                ");
                while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {
                    $res['staffcode'] = $valuecust['code'];
                    $res['staffname'] = $valuecust['name'];
                    $res['positioncode'] = $valuecust['positionCode'];
                    $res['departmentcode'] = $valuecust['departmentCode'];
                    $ismavailable = $sheel->admincp_customers->is_staff_measurements_available($companycode, $customer['customer_ref'], $res['staffcode'], $sm, $valuecust['gender'], $res['code']);
                    $autosizearray = array();

                    $autosizearray = $sheel->admincp_customers->calculate_staff_size($sm, $valuecust['gender'], $res['code']);
                    $res['fit'] = $autosizearray['Fit'];
                    $res['cut'] = $autosizearray['Cut'];
                    $res['size'] = $autosizearray['Size'];
                    if ($res['fit'] != '' and $res['cut'] != '' and $res['size'] != '') {
                        $res['error'] = '[Auto Suggest]';
                    }
                    if (count($ismavailable) != 0) {
                        foreach ($ismavailable as $element) {
                            if ($res['error'] != '') {
                                $res['error'] .= "<br>";
                            }
                            $res['error'] .= print_r($element, true) . "<br>";
                        }
                    }

                    $res['type'] = $res['code'];
                    $suggestdata[] = $res;
                }
            }
            $sheel->xlsx->size_xlsx_to_db($suggestdata, $custstaffs, $customer['customer_ref'], $_SESSION['sheeldata']['user']['userid'], 0);
            $urlarray = explode("?", PAGEURL);
            refresh($urlarray[0]);
        }
        if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'bulkupload') {
            if (isset($sheel->GPC['cancel']) and $sheel->GPC['cancel'] == 'cancel') { // user cancelled bulk upload
                $sheel->db->query("
                    DELETE FROM " . DB_PREFIX . "bulk_tmp_sizes
                    WHERE customerno = '" . $customer['customer_ref'] . "'
                        AND uploaded = '0'
                    ", 0, null, __FILE__, __LINE__);
            }
            if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'assign') {
                if (isset($sheel->GPC['mode']) and $sheel->GPC['mode'] == 'bulk') {
                    if (empty($_FILES['xlsx_file']['name'])) {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/sizes/?error=invalidf');
                    } else {
                        $extension = mb_strtolower(mb_strrchr($_FILES['xlsx_file']['name'], '.'));
                        if ($extension != '.xlsx') {
                            refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/sizes/?error=invalidfe');
                        }
                    }
                    $tmp_name = $_FILES['xlsx_file']['tmp_name'];
                    $file_name = DIR_TMP_XLSX . $customer['customer_id'] . '_SIZE' . '.' . pathinfo($_FILES['xlsx_file']['name'], PATHINFO_EXTENSION);
                    if (file_exists($file_name)) {
                        unlink($file_name);
                    }
                    move_uploaded_file($tmp_name, $file_name);
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $reader->setLoadSheetsOnly(["Sheet1"]);
                    $spreadsheet = $reader->load($file_name);
                    if ($spreadsheet->getSheet(0)->getHighestDataColumn() != 'G') {
                        refresh(HTTPS_SERVER_ADMIN . 'customers/org/' . $customer['customer_id'] . '/sizes/?error=invalidff');
                    }
                    $sheetData = $spreadsheet->getSheet('0')->toArray();
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
                            '" . (intval($spreadsheet->getSheet(0)->getHighestDataRow()) - 1) . "',
                            '0',
                            '0',
                            '0',
                            '" . $sheel->db->escape_string($fileinfo) . "')
                        ", 0, null, __FILE__, __LINE__);
                    $bulk_id = $sheel->db->insert_id();
                    unset($fileinfo);
                    $tempcuststaffs = array();
                    $custstaffs = array();
                    if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
                        $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                        exit();
                    }
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
                    $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
                    if ($apiResponse->isSuccess()) {
                        $tempcuststaffs = $apiResponse->getData();
                    } else {
                        $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
                        exit();
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
                    WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
                    ORDER BY staffcode
                ");
                $errorCount = 0;
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    if ($res['fit'] != '' and $res['cut'] != '' and $res['size'] != '') {
                        if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                            exit();
                        }
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
                                SET errors = '[" . $sheel->db->escape_string($addResponse->getErrorMessage()) . "]'
                                WHERE id = '" . $res['id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
                    } else {
                        $errorCount++;
                        $sheel->db->query("
                            UPDATE " . DB_PREFIX . "bulk_tmp_sizes
                            SET errors = '[" . $sheel->db->escape_string('Size/Fit/Cut cannot be empty') . "]'
                            WHERE id = '" . $res['id'] . "'
                            ", 0, null, __FILE__, __LINE__);
                    }

                }
                if ($errorCount > 0) {
                    $sheel->GPC['note'] = 'adderror';
                    $vars['errorMessage'] = $errorCount . ' errors encountered during add';
                } else {
                    $sheel->GPC['note'] = 'addsuccess';
                }
            }
            $urlarray = explode("?", PAGEURL);
            refresh($urlarray[0]);
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

                if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                    $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                    exit();
                }
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
            if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
                $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
                exit();
            }

            $staffdetails = explode('|', $sheel->GPC['staffs']);
            $sqlallrec = $sheel->db->query("
					SELECT code
					FROM " . DB_PREFIX . "size_types		
					WHERE categoryid = '" . $sheel->GPC['typecategories'] . "' AND (gender = '" . $staffdetails[1] . "' Or gender='U')
					");
            while ($resallrec = $sheel->db->fetch_array($sqlallrec, DB_ASSOC)) {
                $addResponse = $sheel->dynamics->insert(
                    array(
                        "staffCode" => $staffdetails[0],
                        "positionCode" => $staffdetails[2],
                        "departmentCode" => $staffdetails[3],
                        "customerNo" => $sheel->GPC['customer_ref'],
                        "fitCode" => $sheel->GPC['fits'],
                        "cutCode" => $sheel->GPC['cuts'],
                        "sizeCode" => $sheel->GPC['sizes'],
                        "sizeType" => $resallrec['code']
                    )
                );
                if ($addResponse->isSuccess()) {
                    $sheel->GPC['note'] = 'addsuccess';
                } else {
                    $sheel->GPC['note'] = 'adderror';
                    $vars['errorMessage'] = $addResponse->getErrorMessage();
                }
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
            'name',
            'size',
            'position',
            'department',
            'type'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'name' => '{_name}',
            'size' => '{_size}',
            'position' => '{_position}',
            'department' => '{_department}',
            'type' => '{_type}'

        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
        $staffsizes = array();

        if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuststaffs = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
                if ($key1 == 'gender') {
                    if ($value1 == 'Male') {
                        $value1 = 'M';
                    }
                    if ($value1 == 'Female') {
                        $value1 = 'F';
                    }
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
        if (!$sheel->dynamics->init_dynamics('erSizeCategories', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=sizeCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsizecategories = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $sizecategories += [$code => $code . ' > ' . $name];
        }
        if (!$sheel->dynamics->init_dynamics('erSizes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsizes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $sizes += [$code => $code . ' > ' . $name];
        }
        if (!$sheel->dynamics->init_dynamics('erFits', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempfits = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $fits += [$code => $code . ' > ' . $name];
        }
        if (!$sheel->dynamics->init_dynamics('erCuts', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempcuts = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
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
            $cuts += [$code => $code . ' > ' . $name];
        }
        if (!$sheel->dynamics->init_dynamics('erItemTypes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$orderby=name asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempitemtypes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }

        foreach ($tempitemtypes as $key => $value) {

            foreach ($value as $key1 => $value1) {
                if ($key1 == 'name') {
                    $code = $value1;
                }
            }
            $itemtypes += [$code => $code];
        }
        $typecategories = [];
        $sqlcat = $sheel->db->query("
                SELECT id, code, name
                FROM " . DB_PREFIX . "size_type_categories
                ORDER BY  code ASC
                ");
        if ($sheel->db->num_rows($sqlcat) > 0) {
            while ($rowcat = $sheel->db->fetch_array($sqlcat, DB_ASSOC)) {
                $typecategories += [$rowcat['id'] => $rowcat['code'] . ' > ' . $rowcat['name']];
            }
        }
        $form['staff_pulldown'] = $sheel->construct_pulldown('staffs', 'staffs', $custstaffs, '', 'class="draw-select"');
        $form['size_pulldown'] = $sheel->construct_pulldown('sizes', 'sizes', $sizes, '', 'class="draw-select"');
        $form['fit_pulldown'] = $sheel->construct_pulldown('fits', 'fits', $fits, 'R', 'class="draw-select"');
        $form['cut_pulldown'] = $sheel->construct_pulldown('cuts', 'cuts', $cuts, 'T', 'class="draw-select"');
        $form['itemtype_pulldown'] = $sheel->construct_pulldown('itemtypes', 'itemtypes', $itemtypes, '', 'class="draw-select"');
        $form['typecategory_pulldown'] = $sheel->construct_pulldown('typecategories', 'typecategories', $typecategories, '', 'class="draw-select"');
        $form['value'] = '';


        if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        //$pagination = '&$skip=' . ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'] . '&$top=' . $sheel->config['globalfilters_maxrowsdisplay'];

        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffCode, \'' . $sheel->db->escape_string($q) . '\')';
                    break;
                }
                case 'name': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffName,  \'' . $sheel->db->escape_string($q) . '\')';
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
        } else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }

        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $ordercondition . $pagination);
        if ($apiResponse->isSuccess()) {
            $staffsizes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        usort($staffsizes, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffCode'], $matchesA);
            preg_match_all('!\d+!', $b['staffCode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });
        $totalItems = count($staffsizes);
        $totalPages = ceil($totalItems / $sheel->config['globalfilters_maxrowsdisplay']);
        $offset = ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'];
        $pagedstaffsizes = array_slice($staffsizes, $offset, $sheel->config['globalfilters_maxrowsdisplay']);
        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);

        $uploadedsizes = array();
        $sqlupd = $sheel->db->query("
            SELECT id, staffcode, staffname, positioncode, departmentcode, fit, cut, size, type, customerno, errors
                FROM " . DB_PREFIX . "bulk_tmp_sizes
            WHERE customerno = '" . $customer['customer_ref'] . "' AND uploaded = '0'
            ORDER BY staffcode, type
        ");

        while ($res = $sheel->db->fetch_array($sqlupd, DB_ASSOC)) {

            $uploadedsizes[] = $res;
        }
        if ($sheel->db->num_rows($sqlupd) > 0) {
            $sheel->GPC['haspendinguploads'] = '1';
        }

        $sheel->template->fetch('main', 'staff-sizes.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'staffsizes' => $pagedstaffsizes,
                'uploadedsizes' => $uploadedsizes
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();

    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'bcview' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
        $sheel->template->meta['jsinclude']['footer'][] = 'admin_bccustomers';
        $customer = array();
        if (!$sheel->dynamics->init_dynamics('erCustomer', $sheel->GPC['company'])) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=number eq \'' . $sheel->GPC['no'] . '\'';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $customer = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }

        $customer = $customer['0'];


        $sheel->GPC['activated'] = '0';
        $sheel->GPC['blocked'] = '0';
        if (isset($customer['blocked']) && $customer['blocked'] == '_x0020_') {
            $sheel->GPC['blocked'] = '0';
        } else {
            $sheel->GPC['blocked'] = '1';
            $sheel->GPC['note'] = 'blocked';
        }
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
                'slpage' => $sheel->slpage,
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
                    $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'failure' . "\n" . $sheel->array2string($sheel->GPC), 'Error saving customer logo file', 'customer logo file could not be uploaded (check folder permission)');
                    die($_FILES["imagename"]["error"]);
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
        if (!$sheel->dynamics->init_dynamics('erCustomerDepartments', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=departmentCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $departments = $apiResponse->getData();
            foreach ($departments as $keydepartment => $valuedepartment) {
                $sqlorderanalysis = $sheel->db->query("
                        SELECT a.analysisidentifier, a.analysisreference, a.isfinished, a.isarchived, al.itemno
                        FROM " . DB_PREFIX . "analysis a
                        LEFT JOIN " . DB_PREFIX . "analysis_lines al ON a.analysisreference = al.lineidentifier
                        WHERE a.analysisidentifier = '" . $valuedepartment['customerNo'] . "' AND al.allocationtype = 'Department' AND al.allocationcode = '" . $valuedepartment['departmentCode'] . "'
                    ");
                if ($sheel->db->num_rows($sqlorderanalysis) == 0) {
                    $departments[$keydepartment]['orders'] = '<span class="badge badge--attention">{_no_orders}</span>';
                } else {
                    while ($resorderanalysis = $sheel->db->fetch_array($sqlorderanalysis, DB_ASSOC)) {
                        if ($resorderanalysis['isfinished'] == '0' and $resorderanalysis['isarchived'] == '0') {
                            $departments[$keydepartment]['orders'] = '<span class="badge badge--success">{_active}</span>';
                            break;
                        } else {
                            $departments[$keydepartment]['orders'] = '<span class="badge badge--warning">{_completed}</span>';
                        }
                    }
                }
            }
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }

        $positions = array();
        if (!$sheel->dynamics->init_dynamics('erCustomerPositions', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=positionCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $positions = $apiResponse->getData();
            foreach ($positions as $keyposition => $valueposition) {
                $sqlorderanalysis = $sheel->db->query("
                        SELECT a.analysisidentifier, a.analysisreference, a.isfinished, a.isarchived, al.itemno, al.allocationtype, al.allocationcode
                        FROM " . DB_PREFIX . "analysis a
                        LEFT JOIN " . DB_PREFIX . "analysis_lines al ON a.analysisreference = al.lineidentifier
                        WHERE a.analysisidentifier = '" . $valueposition['customerNo'] . "' AND al.allocationtype = 'Position' AND al.allocationcode = '" . $valueposition['positionCode'] . "'
                    ");
                if ($sheel->db->num_rows($sqlorderanalysis) == 0) {
                    $positions[$keyposition]['orders'] = '<span class="badge badge--attention">{_no_orders}</span>';
                } else {
                    while ($resorderanalysis = $sheel->db->fetch_array($sqlorderanalysis, DB_ASSOC)) {
                        if ($resorderanalysis['isfinished'] == '0' and $resorderanalysis['isarchived'] == '0') {
                            $positions[$keyposition]['orders'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=allocations&customer=' . $resorderanalysis['analysisidentifier'] . '&allocationtype=' . $resorderanalysis['allocationtype'] . '&allocationcode=' . $resorderanalysis['allocationcode'] . '" title="' . $resorderanalysis['allocationcode'] . '"><span class="badge badge--success" >{_active}</span></a>';
                            break;
                        } else {
                            $positions[$keyposition]['orders'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/orders/-1/?completed=1&analysis=allocations&customer=' . $resorderanalysis['analysisidentifier'] . '&allocationtype=' . $resorderanalysis['allocationtype'] . '&allocationcode=' . $resorderanalysis['allocationcode'] . '" title="' . $resorderanalysis['allocationcode'] . '"><span class="badge badge--warning">{_completed}</span></a>';
                        }
                    }
                }
            }
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $tempsm = array();
        $sm = array();
        $sm1 = array();
        if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempsm = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $currentStaff = '';
        $lastKey = array_key_last($tempsm);
        foreach ($tempsm as $key => $value) {
            if ($value['staffCode'] != $currentStaff and $currentStaff != '') {
                $sm += [$currentStaff => $sm1];
                $sm1 = array();
            }
            if ($key == $lastKey) {
                $sm1 += [$value['measurementCode'] => $value['value']];
                $sm += [$currentStaff => $sm1];
            }

            $sm1 += [$value['measurementCode'] => $value['value']];
            $currentStaff = $value['staffCode'];
        }
        $tempss = array();
        $ss = array();
        $ss1 = array();
        if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempss = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $currentStaff = '';
        $lastKey = array_key_last($tempss);
        foreach ($tempss as $key => $value) {
            if ($value['staffCode'] != $currentStaff and $currentStaff != '') {
                $ss += [$currentStaff => $ss1];
                $ss1 = array();
            }
            if ($key == $lastKey) {
                $ss1 += [$value['sizeType'] => $value['sizeCode']];
                $ss += [$currentStaff => $ss1];
            }
            $ss1 += [$value['sizeType'] => $value['sizeCode']];
            $currentStaff = $value['staffCode'];
        }
        $particularities = [];
        $tempparticularities = [];
        if (!$sheel->dynamics->init_dynamics('erStaffParticularities', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $tempparticularities = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        foreach ($tempparticularities as $tempparticularity) {
            $particularities[$tempparticularity['code']] = $tempparticularity['name'];
        }
        $staff = array();
        $mandatorymeasurements = explode(',', $sheel->config['mandatorymeasurements']);
        if (!$sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'&$orderby=code asc';
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $staff = $apiResponse->getData();
            foreach ($staff as $keystaff => $valuestaff) {
                $sqlorderanalysis = $sheel->db->query("
                        SELECT a.analysisidentifier, a.analysisreference, a.isfinished, a.isarchived, al.itemno, al.allocationtype, al.allocationcode
                        FROM " . DB_PREFIX . "analysis a
                        LEFT JOIN " . DB_PREFIX . "analysis_lines al ON a.analysisreference = al.lineidentifier
                        WHERE a.analysisidentifier = '" . $valuestaff['customerNo'] . "' AND al.allocationtype = 'Staff' AND al.allocationcode = '" . $valuestaff['code'] . "'
                    ");
                if ($staff[$keystaff]['validated'] == '1') {
                    $staff[$keystaff]['validated'] = '<span class="badge badge--success">{_yes}</span>';
                } else {
                    $staff[$keystaff]['validated'] = '<span class="badge badge--attention">{_no}</span>';
                }
                if ($staff[$keystaff]['validatedBy'] == '') {
                    $staff[$keystaff]['validatedBy'] = '--';
                }
                if ($staff[$keystaff]['particularity1'] != '') {
                    $staff[$keystaff]['particularity1'] = '<span class="badge badge--critical">' . $valuestaff['particularity1'] . '</span>';
                    $staff[$keystaff]['particularity1desc'] = $particularities[$valuestaff['particularity1']];
                } else {
                    $staff[$keystaff]['particularity1desc'] = '';
                }
                if ($staff[$keystaff]['particularity2'] != '') {
                    $staff[$keystaff]['particularity2'] = '<span class="badge badge--critical">' . $valuestaff['particularity2'] . '</span>';
                    $staff[$keystaff]['particularity2desc'] = $particularities[$valuestaff['particularity2']];
                } else {
                    $staff[$keystaff]['particularity2desc'] = '';
                }
                if ($staff[$keystaff]['particularity3'] != '') {
                    $staff[$keystaff]['particularity3'] = '<span class="badge badge--critical">' . $valuestaff['particularity3'] . '</span>';
                    $staff[$keystaff]['particularity3desc'] = $particularities[$valuestaff['particularity3']];
                } else {
                    $staff[$keystaff]['particularity3desc'] = '';
                }



                if ($sheel->db->num_rows($sqlorderanalysis) == 0) {
                    $staff[$keystaff]['orders'] = '<span class="badge badge--attention">{_no_orders}</span>';
                } else {
                    while ($resorderanalysis = $sheel->db->fetch_array($sqlorderanalysis, DB_ASSOC)) {
                        if ($resorderanalysis['isfinished'] == '0' and $resorderanalysis['isarchived'] == '0') {

                            $staff[$keystaff]['orders'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=allocations&customer=' . $resorderanalysis['analysisidentifier'] . '&allocationtype=' . $resorderanalysis['allocationtype'] . '&allocationcode=' . $resorderanalysis['allocationcode'] . '" title="' . $resorderanalysis['allocationcode'] . '"><span class="badge badge--success" >{_active}</span></a>';
                            break;
                        } else {
                            $staff[$keystaff]['orders'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/orders/-1/?completed=1&analysis=allocations&customer=' . $resorderanalysis['analysisidentifier'] . '&allocationtype=' . $resorderanalysis['allocationtype'] . '&allocationcode=' . $resorderanalysis['allocationcode'] . '" title="' . $resorderanalysis['allocationcode'] . '"><span class="badge badge--warning">{_completed}</span></a>';
                        }
                    }
                }
                $staffmeasures = $sm[$valuestaff['code']];
                foreach ($mandatorymeasurements as $manvalues) {
                    if ($staffmeasures[$manvalues] == '' or $staffmeasures[$manvalues] == '0') {
                        $staff[$keystaff]['meetmeasurements'] = '<span class="badge badge--critical">{_not_met}</span>';
                        break;
                    } else {
                        $staff[$keystaff]['meetmeasurements'] = '<span class="badge badge--success">{_available}</span>';
                    }
                }
                $sql = $sheel->db->query("
                    SELECT code
                    FROM " . DB_PREFIX . "size_types 
                    WHERE  (gender = '" . substr($valuestaff['gender'], 0, 1) . "' OR gender='U') AND needsize = '1'
                    ");
                while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                    $staffsizes = $ss[$valuestaff['code']];
                    if ($staffsizes[$res['code']] == '') {
                        $staff[$keystaff]['meetsizes'] = '<span class="badge badge--critical">{_not_met}</span>';
                        break;
                    } else {
                        $staff[$keystaff]['meetsizes'] = '<span class="badge badge--success">{_available}</span>';
                    }
                }
            }

        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        usort($staff, function ($a, $b) {
            preg_match_all('!\d+!', $a['code'], $matchesA);
            preg_match_all('!\d+!', $b['code'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });
        if (!$sheel->access->has_access($_SESSION['sheeldata']['user']['userid'], 'admin_customers_org')) {
            $sheel->show['hasorgaccess'] = false;
        } else {
            $sheel->show['hasorgaccess'] = true;
        }
        $sheel->template->fetch('main', 'customers.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
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
        $customertemp = array();
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
        if (!$sheel->dynamics->init_dynamics('erCustomer', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        $apiResponse = $sheel->dynamics->select('?' . $searchcondition);
        if ($apiResponse->isSuccess()) {
            $customertemp = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        $customer = $customertemp['0'];
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


    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'measurementsview' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
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
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Measurements';
        $searchfilters = array(
            'staff',
            'name',
            'measurement',
            'position',
            'department'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'name' => '{_name}',
            'measurement' => '{_measurement}',
            'position' => '{_position}',
            'department' => '{_department}'

        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
        $staffmeasurements = array();
        if (!$sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffCode, \'' . $sheel->db->escape_string($q) . '\')';
                    break;
                }
                case 'name': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffName,  \'' . $sheel->db->escape_string($q) . '\')';
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
        } else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }

        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $ordercondition . $pagination);
        if ($apiResponse->isSuccess()) {
            $staffmeasurements = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        usort($staffmeasurements, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffCode'], $matchesA);
            preg_match_all('!\d+!', $b['staffCode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            if ($numA === $numB) {
                return strcmp($a['measurementCode'], $b['measurementCode']);
            }
            return $numA - $numB;
        });
        $totalItems = count($staffmeasurements);
        $totalPages = ceil($totalItems / $sheel->config['globalfilters_maxrowsdisplay']);
        $offset = ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'];
        $pagedstaffmeasurements = array_slice($staffmeasurements, $offset, $sheel->config['globalfilters_maxrowsdisplay']);
        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);
        usort($uploadedmeaasurements, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffcode'], $matchesA);
            preg_match_all('!\d+!', $b['staffcode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });
        $sheel->template->fetch('main', 'staff-measurements-view.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'staffmeasurements' => $pagedstaffmeasurements,
                'uploadedmeaasurements' => $uploadedmeaasurements
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'sizesview' and isset($sheel->GPC['no']) and $sheel->GPC['no'] != '') {
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
        $areanav = 'customers_bc';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = 'Sizes';
        $searchfilters = array(
            'staff',
            'name',
            'size',
            'position',
            'department',
            'type'
        );
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'staff' => '{_staff}',
            'name' => '{_name}',
            'size' => '{_size}',
            'position' => '{_position}',
            'department' => '{_department}',
            'type' => '{_type}'

        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
        $staffsizes = array();
        if (!$sheel->dynamics->init_dynamics('erStaffSizes', $companycode)) {
            $sheel->admincp->print_action_failed('{_inactive_external_api}', $sheel->GPC['returnurl']);
            exit();
        }
        //$pagination = '&$skip=' . ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'] . '&$top=' . $sheel->config['globalfilters_maxrowsdisplay'];

        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'staff': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffCode, \'' . $sheel->db->escape_string($q) . '\')';
                    break;
                }
                case 'name': {
                    $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\' and contains( staffName,  \'' . $sheel->db->escape_string($q) . '\')';
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
        } else {
            $searchcondition = '$filter=customerNo eq \'' . $customer['customer_ref'] . '\'';
        }

        $ordercondition = '&$orderby=staffCode asc';
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $ordercondition . $pagination);
        if ($apiResponse->isSuccess()) {
            $staffsizes = $apiResponse->getData();
        } else {
            $sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
            $sheel->admincp->print_action_failed($sheel->template->parse_template_phrases('message'), $sheel->GPC['returnurl']);
            exit();
        }
        usort($staffsizes, function ($a, $b) {
            preg_match_all('!\d+!', $a['staffCode'], $matchesA);
            preg_match_all('!\d+!', $b['staffCode'], $matchesB);
            $numA = (int) end($matchesA[0]);
            $numB = (int) end($matchesB[0]);
            return $numA - $numB;
        });
        $totalItems = count($staffsizes);
        $totalPages = ceil($totalItems / $sheel->config['globalfilters_maxrowsdisplay']);
        $offset = ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'];
        $pagedstaffsizes = array_slice($staffsizes, $offset, $sheel->config['globalfilters_maxrowsdisplay']);
        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($apiResponse->getRecordCount(), $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);
        $sheel->template->fetch('main', 'staff-sizes-view.html', 1);
        $sheel->template->parse_hash(
            'main',
            array(
                'slpage' => $sheel->slpage,
                'customercard' => $customer,
                'form' => $form
            )
        );
        $sheel->template->parse_loop(
            'main',
            array(
                'staffsizes' => $pagedstaffsizes
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else {
        if ($_SESSION['sheeldata']['user']['entityid'] != '0') {
            $entitycriteria = " AND company_id = '" . $_SESSION['sheeldata']['user']['entityid'] . "'";
        }

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
                    $searchcondition = "AND (c.customername Like '%" . $sheel->db->escape_string($q) . "%' OR c.customername Like '%" . $sheel->db->escape_string($q) . "%')";
                    break;
                }
                case 'account': {
                    $searchcondition = "AND (c.customer_ref LIKE '%" . $sheel->db->escape_string($q) . "%' OR c.account_number LIKE '%" . $sheel->db->escape_string($q) . "%')";
                    break;
                }

                case 'date': {
                    $searchcondition = "AND (DATE(c.date_added) = '" . $sheel->db->escape_string($q) . "')";
                    break;
                }
            }
        }
        $vars['prevnext'] = '';
        $sql = $sheel->db->query("
            SELECT c.*, sc.paymethod, sc.startdate, sc.renewdate, sc.active, s.title_eng,s.description_eng
            FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "subscription_customer sc ON c.customer_id = sc.customerid
            LEFT JOIN " . DB_PREFIX . "subscription s ON sc.subscriptionid = s.subscriptionid
            $searchview			
            $searchcondition
            $entitycriteria	
			ORDER BY c.customer_ref ASC
			LIMIT " . (($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay']) . "," . $sheel->config['globalfilters_maxrowsdisplay'] . "
		");

        $sql2 = $sheel->db->query("
            SELECT c.*, sc.paymethod, sc.startdate, sc.renewdate, sc.active, s.title_eng,s.description_eng
            FROM " . DB_PREFIX . "customers c
            LEFT JOIN " . DB_PREFIX . "subscription_customer sc ON c.customer_id = sc.customerid
            LEFT JOIN " . DB_PREFIX . "subscription s ON sc.subscriptionid = s.subscriptionid
            $searchview			
            $searchcondition
            $entitycriteria	
            ORDER BY c.customer_ref ASC
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

                $sqlOrderCount = $sheel->db->query("
                    SELECT COUNT(DISTINCT(reference)) AS ordercount
                    FROM " . DB_PREFIX . "events
                    WHERE topic = 'Order' AND eventidentifier = '" . $res['customer_ref'] . "' AND eventfor = 'customer'
                ");
                $resOrderCount = $sheel->db->fetch_array($sqlOrderCount, DB_ASSOC);
                $res['ordercount'] = $resOrderCount['ordercount'];
                $customers[] = $res;
            }
        }

        $pageurl = PAGEURL;
        $vars['prevnext'] = $sheel->admincp->pagination($number, $sheel->config['globalfilters_maxrowsdisplay'], $sheel->GPC['page'], $pageurl, '', 1);

        $form['view'] = (isset($sheel->GPC['view']) ? $sheel->GPC['view'] : '');
        $filter_options = array(
            '' => '{_select_filter} &ndash;',
            'account' => '{_account}',
            'name' => '{_name}',
            'date' => '{_date_added}'
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
            'slpage' => $sheel->slpage,
            'form' => $form
        )
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>