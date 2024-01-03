<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();


$sqlcompany = $this->sheel->db->query("
        SELECT *
        FROM " . DB_PREFIX . "companies
        WHERE status = 'active' and name ='Aver'
        ");
$searchcondition = '';
$orders = array();
if ($this->sheel->db->num_rows($sqlcompany) > 0) {
        while ($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {
                if (!$this->sheel->dynamics->init_dynamics('erSales', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescompanies['name'] . ', ';
                }
                $searchcondition = '$filter=documentType eq \'Quote\' and systemModifiedAt gt 2024-01-03T09:11:31.247Z';
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $orders = $apiResponse->getData();
                        foreach ($orders as $order) {
                                /*if ($rescompanies['name'] == 'Aver' && $order['icSourceNo']!='') {

                                        echo $order['documentType'] .':'.$order['icSourceNo'].':'.$order['sellToCustomerNo']. "\n";
                                } */
                                echo $order['no'] . "\n";
                                $sqlcustomer = $this->sheel->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "customers 
                                WHERE status = 'active' 
                                AND customer_ref = '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "'
                                ");
                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
                                        $sqlevent = $this->sheel->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "events
                                                WHERE systemid = '" . $order['systemId'] . "'
                                                ORDER BY eventtime DESC
                                                LIMIT 1
                                                ");
                                        if ($this->sheel->db->num_rows($sqlevent) > 0) {
                                                $resevent = $this->sheel->db->fetch_array($sqlevent, DB_ASSOC);
                                                $eventdata = json_decode($resevent['eventdata'], true);
                                                $differences = array_diff_assoc($order, $eventdata);
                                                if (!empty($differences) && ($differences['status'] != $resevent['status'])) {
                                                        $checkpoint = 0;
                                                        $sqlcheckpoint = $this->sheel->db->query("
                                                                SELECT checkpointid
                                                                FROM " . DB_PREFIX . "checkpoints
                                                                WHERE type = '" . $order['documentType'] . "' AND triggeredon = '" . $order['status'] . "'
                                                                LIMIT 1
                                                                ");
                                                        if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                $checkpoint = $rescheckpoint['checkpointid'];
                                                        }
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "events
                                                                (systemid, eventtime, eventfor, eventidentifier, eventdata, topic, istriggered, checkpointid, companyid)
                                                                VALUES(
                                                                '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                " . strtotime($order['systemModifiedAt']) . ",
                                                                'customer',
                                                                '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                                '" . $this->sheel->db->escape_string(json_encode($order)) . "',
                                                                'Orders',
                                                                '0',
                                                                '" . $checkpoint . "',
                                                                '" . $rescompanies['company_id'] . "'
                                                                )", 0, null, __FILE__, __LINE__);
                                                }
                                        } else {
                                                $checkpoint = 0;
                                                $sqlcheckpoint = $this->sheel->db->query("
                                                        SELECT checkpointid
                                                        FROM " . DB_PREFIX . "checkpoints
                                                        WHERE type = '" . $order['documentType'] . "' AND triggeredon = '" . $order['status'] . "'
                                                        LIMIT 1
                                                        ");
                                                if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                        $checkpoint = $rescheckpoint['checkpointid'];
                                                }
                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "events
                                                        (systemid, eventtime, eventfor, eventidentifier, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                        " . strtotime($order['systemCreatedAt']) . ",
                                                        'customer',
                                                        '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                        '" . $this->sheel->db->escape_string(json_encode($order)) . "',
                                                        'Orders',
                                                        '0',
                                                        '" . $checkpoint . "',
                                                        '" . $rescompanies['company_id'] . "'
                                                        )", 0, null, __FILE__, __LINE__);
                                        }
                                        //$this->sheel->kafka->produce($order, 'Orders', $rescompanies['company_id'], $order['systemId'], ['companyName' => $rescompanies['name'], 'companyCode' => $rescompanies['bc_code']]);
                                }
                        }
                        $systemIdsFromOrders = array_column($orders, 'systemId');
                        $query = "
                                SELECT e1.systemid, e1.eventdata
                                FROM " . DB_PREFIX . "events e1
                                INNER JOIN (
                                        SELECT systemid, MAX(eventtime) AS max_eventtime
                                        FROM " . DB_PREFIX . "events
                                        WHERE companyid = '" . $rescompanies['company_id'] . "' AND topic = 'Orders'
                                        GROUP BY systemid
                                ) e2 ON e1.systemid = e2.systemid AND e1.eventtime = e2.max_eventtime
                        ";
                        $result = $this->sheel->db->query($query);

                        while ($row = $this->sheel->db->fetch_array($result, DB_ASSOC)) {

                                $order = json_decode($row['eventdata'], true);
                                if (!in_array($row['systemid'], $systemIdsFromOrders)) {
                                        $checkpoint = 0;
                                        $sqlcheckpoint = $this->sheel->db->query("
                                                SELECT checkpointid
                                                FROM " . DB_PREFIX . "checkpoints
                                                WHERE type = '" . $order['documentType'] . "' AND triggeredon = 'Deleted'
                                                LIMIT 1
                                                ");
                                        if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                $checkpoint = $rescheckpoint['checkpointid'];
                                        }
                                        $checkQuery = "
                                                SELECT 1
                                                FROM " . DB_PREFIX . "events
                                                WHERE checkpointid = '" . $checkpoint . "' AND systemid = '" . $this->sheel->db->escape_string($row['systemid']) . "'
                                                LIMIT 1
                                        ";
                                        $checkResult = $this->sheel->db->query($checkQuery);
                                        if ($this->sheel->db->num_rows($checkResult) == 0) {
                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "events
                                                        (systemid, eventtime, eventfor, eventidentifier, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $this->sheel->db->escape_string($row['systemid']) . "',
                                                        " . TIMESTAMPNOW . ",
                                                        'customer',
                                                        '" . $order['sellToCustomerNo'] . "',
                                                        '" . $this->sheel->db->escape_string(json_encode($order)) . "',
                                                        'Orders',
                                                        '0',
                                                        '" . $checkpoint . "',
                                                        '" . $rescompanies['company_id'] . "'
                                                        )", 0, null, __FILE__, __LINE__);
                                        }
                                }
                        }
                } else {
                        $cronlog .= $apiResponse->getErrorMessage() . ', ';
                        //$this->sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        //$this->sheel->admincp->print_action_failed($this->sheel->template->parse_template_phrases('message'), $this->sheel->GPC['returnurl']);
                }

        }
}
if (!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.orders.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>