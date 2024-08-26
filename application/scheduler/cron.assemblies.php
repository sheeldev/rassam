<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();


$sqlcompany = $this->sheel->db->query("
        SELECT *
        FROM " . DB_PREFIX . "companies
        WHERE status = 'active' and isfactory ='1'
        ");
$searchcondition = '';
$assemblies = array();
if ($this->sheel->db->num_rows($sqlcompany) > 0) {
        while ($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {
                if (!$this->sheel->dynamics->init_dynamics('erAssemblies', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erAssemblies for company ' . $rescompanies['name'] . ', ';
                }
                $sqlEventTime = $this->sheel->db->query("
                        SELECT MAX(eventtime) AS max_eventtime
                        FROM " . DB_PREFIX . "events
                        WHERE companyid = '" . $rescompanies['company_id'] . "' and topic = 'Assembly'
                        ");
                $maxEventTime = '0';
                $resEventTime = $this->sheel->db->fetch_array($sqlEventTime, DB_ASSOC);
                if ($resEventTime['max_eventtime'] !== null) {
                        $maxEventTime = $resEventTime['max_eventtime'] + 1;
                } else {
                        $maxEventTime = $rescompanies['eventstart'];
                }
                $maxEventTimeIso = date('Y-m-d\TH:i:s.u\Z', $maxEventTime);
                $searchcondition = '$filter=systemModifiedAt gt ' . $maxEventTimeIso;
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $assemblies = $apiResponse->getData();
                        foreach ($assemblies as $assembly) {
                                if (isset($assembly['scanType']) && isset($assembly['sequenceNo'])) {
                                        $assembly['status'] = $assembly['sequenceNo'] . '-' . ($assembly['scanType'] == 'Scan In' ? 'In' : 'Out');
                                }
                                $sqlcustomer = $this->sheel->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "customers 
                                        WHERE status = 'active' 
                                        AND customer_ref = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "'
                                        ");
                                if ($this->sheel->db->num_rows($sqlcustomers) > 0) {
                                        $rescustomer = $this->sheel->db->fetch_array($sqlcustomer, DB_ASSOC);
                                        $entityid = $rescustomer['company_id'];
                                        $assembly['documentType'] = 'Assembly';
                                        $sqlevent = $this->sheel->db->query("
                                                SELECT eventid
                                                FROM " . DB_PREFIX . "events
                                                WHERE systemid = '" . $assembly['systemId'] . "'
                                                ORDER BY eventtime DESC
                                                LIMIT 1
                                                ");
                                        if ($this->sheel->db->num_rows($sqlevent) == 0) {
                                                $checkpoint = 0;
                                                $canreoccur = 0;
                                                $sqlactive = $this->sheel->db->query("
                                                        SELECT COUNT(eventid) as event_count
                                                        FROM " . DB_PREFIX . "events
                                                        WHERE reference = '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "' AND topic = 'Assembly'
                                                        ");
                                                $resactive = $this->sheel->db->fetch_array($sqlactive, DB_ASSOC);
                                                $sqlOrders = $this->sheel->db->query("
                                                        SELECT e.eventid, e.checkpointid, c.occuronce as occuronce
                                                        FROM " . DB_PREFIX . "events e
                                                        LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                                                        WHERE e.eventfor = 'customer' AND e.eventidentifier = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "' AND e.reference = '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "' AND e.topic='Order'
                                                        ORDER BY eventtime DESC
                                                        LIMIT 1
                                                ");

                                                if ($this->sheel->db->num_rows($sqlOrders) > 0) {
                                                        $resorders = $this->sheel->db->fetch_array($sqlOrders, DB_ASSOC);
                                                        if ($resorders['occuronce'] == 1) {
                                                                $canreoccur = 0;
                                                        } else {
                                                                $canreoccur = 1;
                                                        }
                                                }
                                                if ($resactive['event_count'] == 0 and $canreoccur == 1) {
                                                        if (!$this->sheel->dynamics->init_dynamics('erSales', $rescustomers['bc_code'])) {
                                                                $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescustomers['name'] . ', ';
                                                        }
                                                        $orders = array();
                                                        $searchsales = '$filter=no eq \'' . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . '\'';
                                                        $apiResponseSales = $this->sheel->dynamics->select('?' . $searchsales);
                                                        if ($apiResponseSales->isSuccess()) {
                                                                $orders = $apiResponseSales->getData();
                                                        } else {
                                                                $cronlog .= $apiResponseSales->getErrorMessage() . ', ';
                                                        }
                                                        $sqlcheckpoint = $this->sheel->db->query("
                                                                SELECT checkpointid
                                                                FROM " . DB_PREFIX . "checkpoints
                                                                WHERE type = 'Order' AND triggeredon = 'Active'
                                                                LIMIT 1
                                                                ");
                                                        if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                $checkpoint = $rescheckpoint['checkpointid'];
                                                                $order = $orders[0];
                                                                $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "events
                                                                (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                                VALUES(
                                                                '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                " . (strtotime($assembly['systemModifiedAt'])) . ",
                                                                " . (strtotime($assembly['systemCreatedAt'])) . ",
                                                                'customer',
                                                                '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                                '" . $entityid . "',
                                                                '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "',
                                                                '" . $this->sheel->db->escape_string(json_encode($order)) . "',
                                                                '" . $order['documentType'] . "',
                                                                '0',
                                                                '" . $checkpoint . "',
                                                                '" . $rescompanies['company_id'] . "'
                                                                )", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                                $sqlcheckpoint = $this->sheel->db->query("
                                                        SELECT checkpointid
                                                        FROM " . DB_PREFIX . "checkpoints
                                                        WHERE type = '" . $assembly['documentType'] . "' AND triggeredon = '" . $assembly['status'] . "'
                                                        LIMIT 1
                                                        ");
                                                if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                        $checkpoint = $rescheckpoint['checkpointid'];
                                                        if (!empty($assembly['icSourceNo'])) {
                                                                $icSourceNo = $this->sheel->db->escape_string($assembly['icSourceNo']);
                                                                $sqlcustomer = $this->sheel->db->query("
                                                                SELECT customer_ref
                                                                FROM " . DB_PREFIX . "customers
                                                                WHERE customer_ref = '" . $icSourceNo . "' AND status = 'active'
                                                                ");
                                                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
                                                                        $this->sheel->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "events
                                                                        (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                                        VALUES(
                                                                        '" . $this->sheel->db->escape_string($assembly['systemId']) . "',
                                                                        " . strtotime($assembly['systemModifiedAt']) . ",
                                                                        " . strtotime($assembly['systemCreatedAt']) . ",
                                                                        'customer',
                                                                        '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "',
                                                                        '" . $entityid . "',
                                                                        '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "',
                                                                        '" . $this->sheel->db->escape_string(json_encode($assembly)) . "',
                                                                        '" . $assembly['documentType'] . "',
                                                                        '0',
                                                                        '" . $checkpoint . "',
                                                                        '" . $rescompanies['company_id'] . "'
                                                                        )", 0, null, __FILE__, __LINE__);
                                                                }
                                                        } else {
                                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "events
                                                        (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $this->sheel->db->escape_string($assembly['systemId']) . "',
                                                        " . strtotime($assembly['systemModifiedAt']) . ",
                                                        " . strtotime($assembly['systemCreatedAt']) . ",
                                                        'customer',
                                                        '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "',
                                                        '" . $entityid . "',
                                                        '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "',
                                                        '" . $this->sheel->db->escape_string(json_encode($assembly)) . "',
                                                        '" . $assembly['documentType'] . "',
                                                        '0',
                                                        '" . $checkpoint . "',
                                                        '" . $rescompanies['company_id'] . "'
                                                        )", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                        }
                                }

                        }
                } else {
                        $cronlog .= $apiResponse->getErrorMessage() . ', ';
                }
        }
}
if (!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.assemblies.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>