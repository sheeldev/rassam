<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();

/* $query = $this->sheel->db->query("
SELECT eventid, systemid, eventdata
FROM " . DB_PREFIX . "events 
WHERE createdtime='0'
");

if ($this->sheel->db->num_rows($query) > 0) {
        while ($row = $this->sheel->db->fetch_array($query, DB_ASSOC)) {
                $order = json_decode($row['eventdata'], true);
                $this->sheel->db->query("
                            UPDATE " . DB_PREFIX . "events
                            SET createdtime = '" . $order['systemCreatedAt'] . "'
                            WHERE eventid = '" . $row['eventid'] . "'
                            ", 0, null, __FILE__, __LINE__);
        }
}
die ('test'); */
$sqlcompany = $this->sheel->db->query("
        SELECT *
        FROM " . DB_PREFIX . "companies
        WHERE status = 'active'
        ");
$searchcondition = '';
$orders = array();
if ($this->sheel->db->num_rows($sqlcompany) > 0) {
        while ($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {
                if (!$this->sheel->dynamics->init_dynamics('erSales', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescompanies['name'] . ', ';
                }
                $sqlEventTime = $this->sheel->db->query("
                        SELECT MIN(createdtime) AS min_eventtime
                        FROM " . DB_PREFIX . "events
                        WHERE companyid = '" . $rescompanies['company_id'] . "' AND (topic = 'Order')
                        ");
                $maxEventTime = '0';
                $resEventTime = $this->sheel->db->fetch_array($sqlEventTime, DB_ASSOC);
                if ($resEventTime['min_eventtime'] !== null) {
                        $maxEventTime = $resEventTime['min_eventtime'];
                } else {
                        $maxEventTime = $rescompanies['eventstart'];
                }
                $maxEventTimeIso = date('Y-m-d\TH:i:s.u\Z', $maxEventTime);
                $searchcondition = '$filter=systemModifiedAt gt ' . $maxEventTimeIso . '';
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $orders = $apiResponse->getData();
                        $systemIdsFromOrders = array_column($orders, 'systemId');
                        $query = "
                                SELECT systemid, eventdata, reference
                                FROM " . DB_PREFIX . "events
                                WHERE companyid = '" . $rescompanies['company_id'] . "' AND topic = 'Order' 
                                AND checkpointid in (SELECT checkpointid FROM " . DB_PREFIX . "checkpoints WHERE type = 'Order' AND (triggeredon = 'Open' or triggeredon = 'Released'))
                                GROUP BY systemid
                                ORDER BY createdtime DESC
                        ";
                        $result = $this->sheel->db->query($query);
                        while ($row = $this->sheel->db->fetch_array($result, DB_ASSOC)) {
                                $order = json_decode($row['eventdata'], true);
                                $sqlcustomer = $this->sheel->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "customers 
                                        WHERE status = 'active' 
                                        AND customer_ref = '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "'
                                        ");
                                $rescustomer = $this->sheel->db->fetch_array($sqlcustomer, DB_ASSOC);
                                $entityid = $rescustomer['company_id'];
                                if (!in_array($row['systemid'], $systemIdsFromOrders)) {
                                        $checkpoint = 0;
                                        $sqlcheckpoint = $this->sheel->db->query("
                                                SELECT checkpointid
                                                FROM " . DB_PREFIX . "checkpoints
                                                WHERE type = '" . $order['documentType'] . "' AND triggeredon = 'Archived'
                                                LIMIT 1
                                                ");
                                        if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                $checkpoint = $rescheckpoint['checkpointid'];
                                        }
                                        $checkQuery = "
                                                SELECT systemid
                                                FROM " . DB_PREFIX . "events
                                                WHERE checkpointid = '" . $checkpoint . "' AND systemid = '" . $this->sheel->db->escape_string($row['systemid']) . "'
                                                LIMIT 1
                                                ";
                                        $checkResult = $this->sheel->db->query($checkQuery);
                                        if ($this->sheel->db->num_rows($checkResult) == 0) {
                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "events
                                                        (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $row['systemid'] . "',
                                                        " . TIMESTAMPNOW . ",
                                                        " . strtotime($order['systemCreatedAt']) . ",
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
                                                $sqlanalysis = $this->sheel->db->query("
                                                        SELECT analysisid
                                                        FROM " . DB_PREFIX . "analysis
                                                        WHERE systemid = '" . $order['systemId'] . "'
                                                        AND analysisreference = '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "'
                                                        LIMIT 1
                                                        ");
                                                if ($this->sheel->db->num_rows($sqlanalysis) == 0) {
                                                        $this->sheel->db->query("
                                                        UPDATE " . DB_PREFIX . "analysis
                                                        SET isarchived = '1'
                                                        WHERE systemid = '" . $order['systemId'] . "'
                                                        ");
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
$this->log_cron_action('cron.orders_compare.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>