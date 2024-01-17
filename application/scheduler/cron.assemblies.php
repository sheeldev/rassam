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
$orders = array();
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
                $searchcondition = '$filter=systemModifiedAt gt ' . $maxEventTimeIso . '';
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $assemblies = $apiResponse->getData();
                        foreach ($assemblies as $assembly) {
                                if (isset($assembly['scanType']) && isset($assembly['sequenceNo'])) {
                                        $assembly['status'] = $assembly['sequenceNo'] . '-' . ($assembly['scanType'] == 'Scan In' ? 'In' : 'Out');
                                }
                                $assembly['documentType'] = 'Assembly';
                                $sqlcustomer = $this->sheel->db->query("
                                SELECT customer_ref
                                FROM " . DB_PREFIX . "customers 
                                WHERE status = 'active' 
                                AND customer_ref = '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sellToCustomerNo']) . "'
                                ");
                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
                                        $sqlevent = $this->sheel->db->query("
                                                SELECT eventid
                                                FROM " . DB_PREFIX . "events
                                                WHERE systemid = '" . $assembly['systemId'] . "'
                                                ORDER BY eventtime DESC
                                                LIMIT 1
                                                ");
                                                
                                        if ($this->sheel->db->num_rows($sqlevent) == 0) {
                                                $checkpoint = 0;
                                                $sqlactive = $this->sheel->db->query("
                                                        SELECT COUNT(eventid) as event_count
                                                        FROM " . DB_PREFIX . "events
                                                        WHERE reference = '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sourceNo']) . "' AND topic = 'Assembly'
                                                        ");
                                                $resactive = $this->sheel->db->fetch_array($sqlactive, DB_ASSOC);
                                                if ($resactive['event_count'] == 0) {
                                                        if (!$this->sheel->dynamics->init_dynamics('erSales', $rescompanies['bc_code'])) {
                                                                $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescompanies['name'] . ', ';
                                                        }
                                                        $searchcondition = '$filter=no eq \'' . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sourceNo']) . '\'';
                                                        $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                                                        if ($apiResponse->isSuccess()) {
                                                                $orders = $apiResponse->getData();
                                                        }
                                                        else {
                                                                $cronlog .= $apiResponse->getErrorMessage() . ', ';
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
                                                                }
                                                        $order = $orders[0];
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "events
                                                                (systemid, eventtime, eventfor, eventidentifier, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                                VALUES(
                                                                '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                " . (strtotime($assembly['systemModifiedAt'])) . ",
                                                                'customer',
                                                                '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                                '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "',
                                                                '" . $this->sheel->db->escape_string(json_encode($order)) . "',
                                                                '" . $order['documentType'] . "',
                                                                '0',
                                                                '" . $checkpoint . "',
                                                                '" . $rescompanies['company_id'] . "'
                                                                )", 0, null, __FILE__, __LINE__);
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
                                                }
                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "events
                                                        (systemid, eventtime, eventfor, eventidentifier, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $this->sheel->db->escape_string($assembly['systemId']) . "',
                                                        " . strtotime($assembly['systemModifiedAt']) . ",
                                                        'customer',
                                                        '" . ($assembly['icCustomerSONo'] != '' ? $assembly['icCustomerSONo'] : $assembly['sellToCustomerNo']) . "',
                                                        '" . ($assembly['icSourceNo'] != '' ? $assembly['icSourceNo'] : $assembly['sourceNo']) . "',
                                                        '" . $this->sheel->db->escape_string(json_encode($assembly)) . "',
                                                        '" . $assembly['documentType'] . "',
                                                        '0',
                                                        '" . $checkpoint . "',
                                                        '" . $rescompanies['company_id'] . "'
                                                        )", 0, null, __FILE__, __LINE__);
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