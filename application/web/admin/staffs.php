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
        'admin_staffs',
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
$sheel->template->meta['areatitle'] = 'Admin CP | Staffs';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Staffs';

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

    if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'view' and isset($sheel->GPC['staffno']) and $sheel->GPC['staffno'] != '') {
        $areanav = 'customers_staffs';
        $vars['areanav'] = $areanav;
        $currentarea = $sheel->GPC['staffno'];
        $vars['currentarea'] = $currentarea;
        $sheel->template->fetch('main', 'staffs.html', 1);
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

        $staffs = array();
        $searchcondition = '\'&$orderby=code asc';
        $searchfilters = array(
            'name',
            'code',
            'customer'
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
        $sheel->dynamics->init_dynamics('erCustomerStaffs', (isset($sheel->GPC['company']) ? $sheel->GPC['company'] : $defaulcompany));
        $pagination = '&$skip=' . ($sheel->GPC['page'] - 1) * $sheel->config['globalfilters_maxrowsdisplay'] . '&$top=' . $sheel->config['globalfilters_maxrowsdisplay'];
        if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
            switch ($sheel->GPC['filter']) {
                case 'name': {
                        $searchcondition = '$filter=contains(name, \'' . $sheel->db->escape_string($q) . '\')&$orderby=code asc';
                        break;
                    }
                case 'code': {
                        $searchcondition = '$filter=number eq \'' . $sheel->db->escape_string($q) . '\'&$orderby=code asc';
                        break;
                    }
                case 'customer': {
                        $searchcondition = '$filter=customerNo eq \'' . $sheel->db->escape_string($q) . '\'&$orderby=code asc';
                        break;
                    }
            }
        }
        $apiResponse = $sheel->dynamics->select('?$count=true&' . $searchcondition . $pagination);
        $pageurl = PAGEURL;
        if ($apiResponse->isSuccess()) {
            $staffs = $apiResponse->getData();
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
            'code' => '{_code}',
            'name' => '{_name}',
            'customer' => '{_cutsomer}',
        );
        $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
        $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
        unset($filter_options);
        $areanav = 'customers_staffs';
        $vars['areanav'] = $areanav;
        $sheel->template->fetch('main', 'staffs.html', 1);
        $sheel->template->parse_loop(
            'main',
            array(
                'staffs' => $staffs
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
} else {
    refresh('signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>