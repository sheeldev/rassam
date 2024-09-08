<?php
define('LOCATION', 'admin');
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
    'vendor' => array(
        'font-awesome',
        'glyphicons',
        'chartist',
        'growl',
        'balloon'
    )
);
$sheel->template->meta['areatitle'] = 'Admin CP | Orders';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Orders';
if (($sidenav = $sheel->cache->fetch("sidenav_customers")) === false) {
    $sidenav = $sheel->admincp_nav->print('customers');
    $sheel->cache->store("sidenav_customers", $sidenav);
}

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $q = ((isset($sheel->GPC['q'])) ? o($sheel->GPC['q']) : '');
    $sheel->GPC['page'] = (!isset($sheel->GPC['page']) or isset($sheel->GPC['page']) and $sheel->GPC['page'] <= 0) ? 1 : intval($sheel->GPC['page']);
    $sheel->GPC['pp'] = (!isset($sheel->GPC['pp']) or isset($sheel->GPC['pp']) and $sheel->GPC['pp'] <= 0) ? $sheel->config['globalfilters_maxrowsdisplay'] : intval($sheel->GPC['pp']);
    $vars = array(
        'sidenav' => $sidenav,
        'q' => $q,
    );
    $searchfilters = array(
        'number',
        'account',
        'name'
    );
    $searchcondition = '';
    if (isset($sheel->GPC['filter']) and !empty($sheel->GPC['filter']) and in_array($sheel->GPC['filter'], $searchfilters) and !empty($q)) {
        switch ($sheel->GPC['filter']) {
            case 'number': {
                $searchcondition = " AND e.reference = '" . $sheel->db->escape_string($q) . "'";
                break;
            }
            case 'account': {
                $searchcondition = " AND e.eventidentifier = '" . $sheel->db->escape_string($q) . "'";
                break;
            }
            case 'name': {
                $searchcondition = " AND e.eventidentifier in (select customer_ref from " . DB_PREFIX . "customers where customername like '%" . trim($sheel->db->escape_string($q)) . "%')";
                break;
            }
        }
    }
    if ($_SESSION['sheeldata']['user']['entityid'] != '0') {
        $searchcondition .= " AND e.entityid = '" . $_SESSION['sheeldata']['user']['entityid'] . "'";
    }
    if ($sheel->GPC['no'] == '0') {
        $currentarea = 'Orders';
        $sqlEvents = $sheel->db->query("
                SELECT e.eventid, e.eventidentifier, e.entityid, e.eventtime as max_eventtime, e.createdtime as createdtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                INNER JOIN (
                    SELECT reference, MAX(eventtime) as max_eventtime
                    FROM " . DB_PREFIX . "events
                    WHERE eventfor = 'customer' and topic='Order' 
                    GROUP BY reference
                ) r ON e.reference = r.reference AND e.eventtime = r.max_eventtime
                WHERE e.eventfor = 'customer' and e.topic='Order' $searchcondition
                GROUP BY e.reference
                ORDER BY createdtime DESC
                LIMIT " . (($sheel->GPC['page'] - 1) * $sheel->GPC['pp']) . "," . $sheel->GPC['pp']);
        $sqlEventsCount = $sheel->db->query("
                SELECT e.eventid, e.eventidentifier, e.entityid, e.eventtime as max_eventtime, e.createdtime as createdtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                INNER JOIN (
                    SELECT reference, MAX(eventtime) as max_eventtime
                    FROM " . DB_PREFIX . "events
                    WHERE eventfor = 'customer' and topic='Order' 
                    GROUP BY reference
                ) r ON e.reference = r.reference AND e.eventtime = r.max_eventtime
                WHERE e.eventfor = 'customer' and e.topic='Order' $searchcondition
                GROUP BY e.reference
                ORDER BY createdtime DESC");
    } else {
        $sqlEvents = $sheel->db->query("
                SELECT e.eventid, e.eventidentifier, e.entityid, e.eventtime as max_eventtime, e.createdtime as createdtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                INNER JOIN (
                    SELECT reference, MAX(eventtime) as max_eventtime
                    FROM " . DB_PREFIX . "events
                    WHERE eventfor = 'customer' and topic='Order'
                    GROUP BY reference
                ) r ON e.reference = r.reference AND e.eventtime = r.max_eventtime
                WHERE e.eventfor = 'customer' and e.topic='Order' AND e.eventidentifier = '" . $sheel->GPC['no'] . "' $searchcondition
                GROUP BY e.reference
                ORDER BY createdtime DESC
                ");
    }
    $events = array();
    if ($sheel->GPC['no'] != '0') {
        $customerid = '0';
        $sql = $sheel->db->query("
            SELECT customer_id
                FROM " . DB_PREFIX . "customers 
            WHERE customer_ref = '" . $sheel->GPC['no'] . "'
            LIMIT 1
            ");

        if ($sheel->db->num_rows($sql) > 0) {
            $customer = $sheel->db->fetch_array($sql, DB_ASSOC);
            $customerid = $customer['customer_id'];
        }
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'customers/view/' . $customerid . '/">' . $sheel->GPC['no'] . '</a> / </span> Orders';
    }
    while ($resEvent = $sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
        $sqlEventLastCheckpoint = $sheel->db->query("
                SELECT e.eventid, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, COALESCE(cs.sequence,'0') as sequence
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.eventfor = 'customer' and e.topic='Order' and e.reference = '" . $resEvent['reference'] . "'
                ORDER BY eventtime DESC, eventid DESC");
        $resEventLastCheckpoint = $sheel->db->fetch_array($sqlEventLastCheckpoint, DB_ASSOC);
        $resEvent['lastcheckpoint'] = $resEventLastCheckpoint['checkpointcode'];
        $resEvent['lastcheckpointmessage'] = $resEventLastCheckpoint['checkpointmessage'];
        $resEvent['color'] = $resEventLastCheckpoint['color'];
        $sqlAssemblies = $sheel->db->query("
                    SELECT eventdata
                    FROM " . DB_PREFIX . "events e
                    WHERE eventfor = 'customer' AND eventidentifier = '" . $resEvent['eventidentifier'] . "' AND reference = '" . $resEvent['reference'] . "' and topic='Assembly'
                    ORDER BY eventtime DESC
                ");
        $assemblycount = 0;
        $qty = 0;
        $previousassembly = '';
        if ($sheel->db->num_rows($sqlAssemblies) > 0) {
            while ($resAssemblies = $sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
                $resAssemblyData = json_decode($resAssemblies['eventdata'], true);
                static $processedAssemblies = array();

                if (!isset($processedAssemblies[$resAssemblyData['assemblyNo']])) {
                    $assemblycount++;
                    $qty = $qty + intval($resAssemblyData['quantity']); //to be used at a later stage
                    $processedAssemblies[$resAssemblyData['assemblyNo']] = true;
                    $assemblies[] = $resAssemblies;
                }
            }
        }
        $sqlcheckpointend = $sheel->db->query("
            SELECT cp.checkpointid
            FROM " . DB_PREFIX . "checkpoints cp
            INNER JOIN " . DB_PREFIX . "checkpoints_sequence cs ON cp.checkpointid = cs.checkpointid
            WHERE cp.type='Assembly' and cs.isend = 1
            LIMIT 1
        ");
        $resCheckpointend = $sheel->db->fetch_array($sqlcheckpointend, DB_ASSOC);
        $sqlFinishedAssemblies = $sheel->db->query("
            SELECT eventid
            FROM " . DB_PREFIX . "events
            WHERE eventfor = 'customer' AND eventidentifier = '" . $resEvent['eventidentifier'] . "' AND reference = '" . $resEvent['reference'] . "' and topic='Assembly' and checkpointid = '" . $resCheckpointend['checkpointid'] . "'
        ");

        $finishedqty = $sheel->db->num_rows($sqlFinishedAssemblies);
        if ($assemblycount == 0 && $finishedqty == 0) {
            $progresspercent = 0;
        } else if ($finishedqty > $assemblycount && $assemblycount > 0 && $finishedqty > 0) {
            $progresspercent = 100;
        } else {
            $progresspercent = round(($finishedqty / $assemblycount) * 100);
        }

        $color = '';

        if ($progresspercent >= 0 && $progresspercent < 25) {
            $color = 'darkred';
        } else if ($progresspercent >= 25 && $progresspercent < 50) {
            $color = 'sheelColor';
        } else if ($progresspercent >= 50 && $progresspercent < 75) {
            $color = 'amber';
        } else if ($progresspercent >= 75 && $progresspercent < 100) {
            $color = 'lightgreen';
        } else if ($progresspercent == 100) {
            $color = 'green';
        }
        $resEvent['assembly'] = '<span class="draw-status__badge draw-status__badge--adjacent-chevron" ' . ($assemblycount > 0 ? 'onclick="showAssemblyDetails(\'' . $resEvent['reference'] . '\', \'' . $resEvent['eventidentifier'] . '\')" style="cursor: pointer;"' : '') . '><span class="draw-status__badge-content">' . $assemblycount . '</span></span>';
        $resEvent['progress'] = '<span class="draw-status__badge ' . $color . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $progresspercent . '%</span></span>';
        
        $resEventData = json_decode($resEvent['eventdata'], true);
        $qty = (isset($resEventData['totalQuantity']) && $resEventData['totalQuantity'] > 0 && $qty == 0) ?  $resEventData['totalQuantity'] : $qty;
        $resEvent['qty'] = $qty;
        $resEvent['customername'] = $resEventData['sellToCustomerName'];
        $resEvent['createdby'] = $resEventData['createdUser'];
        $resEvent['promisedDeliveryDate'] = $resEventData['promisedDeliveryDate'] == '0001-01-01' ? $resEventData['requestedDeliveryDate'] : $resEventData['promisedDeliveryDate'];
        $resEvent['createdat'] = $sheel->common->print_date($resEvent['createdtime'], 'Y-m-d H:i:s', 0, 0, '');
        $resEvent['eventtime'] = $sheel->common->print_date($resEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');

        $days = intval($resEvent['promisedDeliveryDate'] == '0001-01-01' ? '0' : $sheel->common->getBusinessDays(date('Y-m-d'), $resEvent['promisedDeliveryDate']));
        //echo $days.'<br>';
        if ($days <= 0) {
            if ($resEvent['promisedDeliveryDate'] != '0001-01-01') {
                $sqlcheckpointid = $sheel->db->query("
                    SELECT id
                    FROM " . DB_PREFIX . "checkpoints_sequence
                    WHERE checkpointid ='" . $resEvent['checkpointid'] . "' and fromid = '" . $resEvent['entityid'] . "' and isend = 1
                    LIMIT 1
                ");
                if ($sheel->db->num_rows($sqlcheckpointid) > 0) {
                    $resEvent['days'] = '<span class="draw-status__badge darkred draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $sheel->common->getBusinessDays(date('Y-m-d', $resEvent['max_eventtime']), $resEvent['promisedDeliveryDate']) . '</span></span>';
                } else {
                    $resEvent['days'] = '<span class="draw-status__badge darkred draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $days . '</span></span>';
                }
            } else {
                $resEvent['days'] = '<span class="draw-status__badge darkred draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $days . '</span></span>';
            }
        } else if ($days > 0 && $days <= 10) {
            $resEvent['days'] = '<span class="draw-status__badge amber draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $days . '</span></span>';
        } else if ($days > 10) {
            $resEvent['days'] = '<span class="draw-status__badge green draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $days . '</span></span>';
        }
        $events[] = $resEvent;
    }
    //die ();
    usort($events, function ($a, $b) {
        return strtotime($b['createdat']) - strtotime($a['createdat']);
    });
    $number = (int) $sheel->db->num_rows($sqlEventsCount);
    $form['number'] = number_format($number);

    $pageurl = PAGEURL;
    $prevnext = $sheel->admincp->pagination($number, $sheel->GPC['pp'], $sheel->GPC['page'], $pageurl);
    $filter_options = array(
        '' => '{_select_filter} &ndash;',
        'account' => '{_account}',
        'number' => '{_number}',
        'name' => '{_name}'
    );

    $form['filter_pulldown'] = $sheel->construct_pulldown('filter', 'filter', $filter_options, (isset($sheel->GPC['filter']) ? $sheel->GPC['filter'] : ''), 'class="draw-select"');
    $form['q'] = (isset($sheel->GPC['q']) ? $sheel->GPC['q'] : '');
    unset($filter_options);

    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['prevnext'] = (isset($prevnext) ? $prevnext : '');
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