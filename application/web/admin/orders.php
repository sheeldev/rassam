<?php
define('LOCATION', 'admin');
require_once(DIR_CLASSES . '/vendor/office/autoload.php');
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
    'header' => array(
        'functions',
        'admin',
        'admin_orders',
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
    'slidein',
    'slider',
    'vendor' => array(
        'font-awesome',
        'glyphicons',
        'chartist',
        'growl',
        'inputtags',
        'balloon',
        'bootstrap'
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
    if ($sheel->GPC['no'] == '0') {
        $currentarea = 'Orders';
        $sql = $sheel->db->query("
        SELECT customer_ref
            FROM " . DB_PREFIX . "customers
        WHERE status='active'
        ");
    } else {
        $sql = $sheel->db->query("
        SELECT customer_ref
            FROM " . DB_PREFIX . "customers
        WHERE customer_id = '" . $sheel->GPC['no'] . "' and status='active'
        LIMIT 1
        ");
    }
    $events = array();
    if ($sheel->db->num_rows($sql) > 0) {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            if ($sheel->GPC['no'] != '0') {
                $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'customers/view/' . $sheel->GPC['no'] . '/">' . $res['customer_ref'] . '</a> / </span> Orders';
            }
            $sqlEvents = $sheel->db->query("
                SELECT e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                INNER JOIN (
                    SELECT reference, MAX(eventtime) as max_eventtime
                    FROM " . DB_PREFIX . "events
                    WHERE eventfor = 'customer' AND eventidentifier = '" . $res['customer_ref'] . "' and topic='Order'
                    GROUP BY reference
                ) r ON e.reference = r.reference AND e.eventtime = r.max_eventtime
                WHERE e.eventfor = 'customer' AND e.eventidentifier = '" . $res['customer_ref'] . "' and e.topic='Order'
                ORDER BY max_eventtime DESC, reference ASC
            ");

            while ($resEvent = $sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
                $sqlAssemblies = $sheel->db->query("
                    SELECT eventdata
                    FROM " . DB_PREFIX . "events e
                    WHERE eventfor = 'customer' AND eventidentifier = '" . $res['customer_ref'] . "' AND reference = '" . $resEvent['reference'] . "' and topic='Assembly'
                    ORDER BY eventtime DESC
                ");
                $assemblycount = 0;
                $previousassembly = '';
                if ($sheel->db->num_rows($sqlAssemblies) > 0) {
                    while ($resAssemblies = $sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
                        $resAssemblyData = json_decode($resAssemblies['eventdata'], true);
                        static $processedAssemblies = array();

                        if (!isset($processedAssemblies[$resAssemblyData['assemblyNo']])) {
                            $assemblycount++;
                            $processedAssemblies[$resAssemblyData['assemblyNo']] = true;
                            $assemblies[] = $resAssemblies;
                        }
                    }
                }
                $resEvent['assembly'] = '<span class="draw-status__badge draw-status__badge--adjacent-chevron" ' . ($assemblycount > 0 ? 'onclick="showAssemblyDetails(\'' . $resEvent['reference'] . '\', \'' . $resEvent['eventidentifier'] . '\')" style="cursor: pointer;"' : '') . '><span class="draw-status__badge-content">' . $assemblycount . '</span></span>';
                $resEventData = json_decode($resEvent['eventdata'], true);
                $resEvent['customername'] = $resEventData['sellToCustomerName'];
                $resEvent['createdby'] = $resEventData['createdUser'];
                $resEvent['createdat'] = $sheel->common->print_date($resEventData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
                $resEvent['eventtime'] = $sheel->common->print_date($resEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
                $events[] = $resEvent;
            }
        }
    }
    $filter_options = array(
        '' => '{_select_filter} &ndash;',
        'account' => '{_account}',
        'number' => '{_number}',
        'date' => '{_created_date} (YYYY-MM-DD)'
    );
    $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
    $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
    unset($filter_options);

    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $sheel->template->fetch('main', 'customer-orders.html', 1);
    $sheel->template->parse_loop(
        'main',
        array(
            'events' => $events
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
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>