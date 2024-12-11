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

                $sqlEventTime = $this->sheel->db->query("
                        SELECT MAX(createdtime) AS max_eventtime
                        FROM " . DB_PREFIX . "events
                        WHERE companyid = '" . $rescompanies['company_id'] . "' and topic = 'Assembly'
                        ");
                $maxEventTime = '0';
                $resEventTime = $this->sheel->db->fetch_array($sqlEventTime, DB_ASSOC);
                if ($resEventTime['max_eventtime'] !== null) {
                        $maxEventTime = $resEventTime['max_eventtime'];
                        $maxEventTimeIso = (new DateTime('today'))->format('Y-m-d\TH:i:s.u\Z');
                } else {
                        $maxEventTime = $rescompanies['eventstart'];
                        $maxEventTimeIso = date('Y-m-d\TH:i:s.u\Z', $maxEventTime);
                }
                $searchcondition = '$filter=sourceType eq \'Order\' and systemModifiedAt gt ' . $maxEventTimeIso;
                //$searchcondition = '$filter=sourceNo eq \'SO-AVR24-01612\'';

                if (!$this->sheel->dynamics->init_dynamics('erAssembliesAll', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erAssembliesAll for company ' . $rescompanies['name'] . ', ';
                }
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $assemblies = $apiResponse->getData();
                        foreach ($assemblies as $assembly) {
                                $assembly['status'] = '0-In';
                                $sqlcustomer = $this->sheel->db->query("
                                        SELECT c.*, cp.bc_code, cp.name as bc_name
                                        FROM " . DB_PREFIX . "customers c
                                        LEFT JOIN " . DB_PREFIX . "companies cp ON c.company_id = cp.company_id
                                        WHERE c.status = 'active' 
                                        AND c.customer_ref = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "'
                                        LIMIT 1");

                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
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
                                                $sqlcheckpoint = $this->sheel->db->query("
                                                        SELECT checkpointid
                                                        FROM " . DB_PREFIX . "checkpoints
                                                        WHERE type = '" . $assembly['documentType'] . "' AND triggeredon = '" . $assembly['status'] . "'
                                                        LIMIT 1
                                                        ");
                                                if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                        $checkpoint = $rescheckpoint['checkpointid'];
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
                } else {
                        $cronlog .= $apiResponse->getErrorMessage() . ', ';
                }

                if (!$this->sheel->dynamics->init_dynamics('erAssemblies', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erAssemblies for company ' . $rescompanies['name'] . ', ';
                }
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $assemblies = $apiResponse->getData();
                        foreach ($assemblies as $assembly) {
                                if (isset($assembly['scanType']) && isset($assembly['sequenceNo'])) {
                                        $assembly['status'] = $assembly['sequenceNo'] . '-' . ($assembly['scanType'] == 'Scan In' ? 'In' : 'Out');
                                }
                                $sqlcustomer = $this->sheel->db->query("
                                        SELECT c.*, cp.bc_code, cp.name as bc_name
                                        FROM " . DB_PREFIX . "customers c
                                        LEFT JOIN " . DB_PREFIX . "companies cp ON c.company_id = cp.company_id
                                        WHERE c.status = 'active' 
                                        AND c.customer_ref = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "'
                                        LIMIT 1");

                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
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
                                                        WHERE reference = '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "' AND topic = 'Assembly' AND checkpointid not in (SELECT checkpointid FROM " . DB_PREFIX . "checkpoints WHERE type = 'Assembly' AND triggeredon = '0-In')
                                                        ");
                                                $resactive = $this->sheel->db->fetch_array($sqlactive, DB_ASSOC);
                                                $sqlOrders = $this->sheel->db->query("
                                                        SELECT e.eventid, e.checkpointid, c.occuronce as occuronce
                                                        FROM " . DB_PREFIX . "events e
                                                        LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                                                        WHERE e.eventfor = 'customer' AND e.eventidentifier = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sellToCustomerNo']) . "' AND e.reference = '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sourceNo']) . "' AND e.topic='Order'
                                                        ORDER BY eventtime DESC, eventid DESC
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
                                                        if (!$this->sheel->dynamics->init_dynamics('erSales', $rescustomer['bc_code'])) {
                                                                $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescustomer['bc_name'] . ', ';
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