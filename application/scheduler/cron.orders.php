<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();

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
                        SELECT MAX(createdtime) AS max_eventtime
                        FROM " . DB_PREFIX . "events
                        WHERE companyid = '" . $rescompanies['company_id'] . "' AND (topic = 'Order' Or topic = 'Quote')
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
                $searchcondition = '$filter=(documentType eq \'Quote\' or documentType eq \'Order\') and systemModifiedAt gt ' . $maxEventTimeIso . '';
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $orders = $apiResponse->getData();
                        foreach ($orders as $order) {
                                if (isset($order['shipped']) && $order['shipped'] == 'true') {
                                        $order['status'] = 'Shipped';
                                        if (isset($order['shippedNotInvoiced']) && $order['shippedNotInvoiced'] != 'true') {
                                                $order['status'] = 'Completed';
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
                                                                SET isfinished = '1'
                                                                WHERE systemid = '" . $order['systemId'] . "'
                                                        ");
                                                }
                                        }
                                }
                                $sqlcustomer = $this->sheel->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "customers 
                                        WHERE status = 'active' 
                                        AND customer_ref = '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "'
                                        ");
                                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
                                        $rescustomer = $this->sheel->db->fetch_array($sqlcustomer, DB_ASSOC);
                                        $entityid = $rescustomer['company_id'];
                                        $sqlevent = $this->sheel->db->query("
                                                SELECT eventdata
                                                FROM " . DB_PREFIX . "events
                                                WHERE systemid = '" . $order['systemId'] . "'
                                                ORDER BY eventtime DESC, eventid DESC
                                                LIMIT 1
                                                ");
                                        if ($this->sheel->db->num_rows($sqlevent) > 0) {
                                                $resevent = $this->sheel->db->fetch_array($sqlevent, DB_ASSOC);
                                                $eventdata = json_decode($resevent['eventdata'], true);
                                                $differences = array_diff_assoc($order, $eventdata);
                                                if (!empty($differences)) {
                                                        $checkpoint = 0;
                                                        $updatestime = strtotime($order['systemModifiedAt']);
                                                        if (!isset($differences['status'])) {
                                                                $sqlcheckpoint = $this->sheel->db->query("
                                                                SELECT checkpointid
                                                                FROM " . DB_PREFIX . "checkpoints
                                                                WHERE type = '" . $order['documentType'] . "' AND triggeredon = 'Updated'
                                                                LIMIT 1
                                                                ");
                                                                if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                        $checkpoint = $rescheckpoint['checkpointid'];
                                                                }
                                                                $updatestime = strtotime($order['systemModifiedAt']) - 5;
                                                        } else {
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
                                                        }
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "events
                                                                (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                                VALUES(
                                                                '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                " . $updatestime . ",
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
                                                }
                                                if ($order['documentType'] == 'Order') {
                                                        $sqlanalysis = $this->sheel->db->query("
                                                                SELECT analysisid
                                                                FROM " . DB_PREFIX . "analysis
                                                                WHERE analysisreference = '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "'
                                                                LIMIT 1
                                                                ");    
                                                        if ($this->sheel->db->num_rows($sqlanalysis) == 0) {
                                                                $this->sheel->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "analysis
                                                                        (systemid, createdtime, modifiedtime, analysisfor, analysisidentifier, entityid, analysisreference, topic, isfinished, isarchived, companyid)
                                                                        VALUES(
                                                                        '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                        " . strtotime($order['systemCreatedAt']) . ",
                                                                        " . strtotime($order['systemModifiedAt']) . ",
                                                                        'customer',
                                                                        '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                                        '" . $entityid . "',
                                                                        '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "',
                                                                        '" . $order['documentType'] . "',
                                                                        '0',
                                                                        '0',
                                                                        '" . $rescompanies['company_id'] . "'
                                                                        )", 0, null, __FILE__, __LINE__);
                                                        }
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
                                                        (systemid, eventtime, createdtime, eventfor, eventidentifier, entityid, reference, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                        " . strtotime($order['systemModifiedAt']) . ",
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
                                                if ($order['documentType'] == 'Order') {
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "analysis
                                                                (systemid, createdtime, modifiedtime, analysisfor, analysisidentifier, entityid, analysisreference, topic, isfinished, isarchived, companyid)
                                                                VALUES(
                                                                '" . $this->sheel->db->escape_string($order['systemId']) . "',
                                                                " . strtotime($order['systemCreatedAt']) . ",
                                                                " . strtotime($order['systemModifiedAt']) . ",
                                                                'customer',
                                                                '" . ($order['icSourceNo'] != '' ? $order['icSourceNo'] : $order['sellToCustomerNo']) . "',
                                                                '" . $entityid . "',
                                                                '" . ($order['icCustomerSONo'] != '' ? $order['icCustomerSONo'] : $order['no']) . "',
                                                                '" . $order['documentType'] . "',
                                                                '0',
                                                                '0',
                                                                '" . $rescompanies['company_id'] . "'
                                                                )", 0, null, __FILE__, __LINE__);
                                                }

                                        }
                                        //$this->sheel->kafka->produce($order, 'Orders', $rescompanies['company_id'], $order['systemId'], ['companyName' => $rescompanies['name'], 'companyCode' => $rescompanies['bc_code']]);
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