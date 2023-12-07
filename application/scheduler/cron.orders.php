<?php
if(!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();


$sqlcompany = $this->sheel->db->query("
        SELECT *
        FROM ".DB_PREFIX."companies
        WHERE status = 'active'
        ");
$searchcondition = '';


if($this->sheel->db->num_rows($sqlcompany) > 0) {
        while($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {

                if(!$this->sheel->dynamics->init_dynamics('erSales', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erSales for company '.$rescompanies['name'].', ';
                }
                $searchcondition = '$filter=documentType eq \'Quote\'';
                $apiResponse = $this->sheel->dynamics->select('?'.$searchcondition);
                if($apiResponse->isSuccess()) {
                        $orders = $apiResponse->getData();
                        foreach($orders as $order) {
                                $sqlcustomer = $this->sheel->db->query("
                                SELECT *
                                FROM ".DB_PREFIX."customers 
                                WHERE status = 'active' 
                                AND company_id = '".$rescompanies['company_id']."'
                                AND customer_ref = '".$order['sellToCustomerNo']."'
                                ");
                                if($this->sheel->db->num_rows($sqlcustomer) > 0) {
                                        $sqlevent = $this->sheel->db->query("
                                        SELECT *
                                        FROM ".DB_PREFIX."events
                                        WHERE systemid = '".$order['systemId']."'
                                        LIMIT 1
                                        ");
                                        if($this->sheel->db->num_rows($sqlevent) > 0) {
                                                $resevent = $this->sheel->db->fetch_array($sqlevent, DB_ASSOC);
                                                $eventdata = json_decode($resevent['eventdata'], true);

                                                // Compare the eventdata and order arrays
                                                $differences = array_diff_assoc($order, $eventdata);

                                                if(!empty($differences)) {
                                                        $checkpoint = 0;
                                                        $sqlcheckpoint = $this->sheel->db->query("
                                                        SELECT checkpointid
                                                        FROM ".DB_PREFIX."checkpoints
                                                        WHERE type = '".$order['documentType']."' AND triggeredon = 'update'
                                                        LIMIT 1
                                                        ");
                                                        if($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                $checkpoint = $rescheckpoint['checkpointid'];
                                                        }
                                                        if($resevent['istriggered']) {
                                                                $this->sheel->db->query("
                                                                UPDATE ".DB_PREFIX."events
                                                                SET eventtime = ".strtotime($order['systemModifiedAt']).",
                                                                    eventpdata = '".$this->sheel->db->escape_string($resevent['eventdata'])."',
                                                                    eventdata = '".$this->sheel->db->escape_string(json_encode($order))."',
                                                                    istriggered = '0',
                                                                    checkpointid = '".$checkpoint."'
                                                                WHERE systemid = '".$resevent['systemid']."'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                        } else {
                                                $checkpoint = 0;
                                                $sqlcheckpoint = $this->sheel->db->query("
                                                        SELECT checkpointid
                                                        FROM ".DB_PREFIX."checkpoints
                                                        WHERE type = '".$order['documentType']."' AND triggeredon = 'add'
                                                        LIMIT 1
                                                        ");
                                                if($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                        $checkpoint = $rescheckpoint['checkpointid'];
                                                }
                                                $this->sheel->db->query("
                                                        INSERT INTO ".DB_PREFIX."events
                                                        (systemid, eventtime, eventpdata, eventdata, topic, istriggered, checkpointid, companyid)
                                                        VALUES(
                                                        '".$this->sheel->db->escape_string($order['systemId'])."',
                                                        ".strtotime($order['systemCreatedAt']).",
                                                        '',
                                                        '".$this->sheel->db->escape_string(json_encode($order))."',
                                                        'Orders',
                                                        '0',
                                                        '".$checkpoint."',
                                                        '".$rescompanies['company_id']."'
                                                        )", 0, null, __FILE__, __LINE__);
                                        }
                                        //$this->sheel->kafka->produce($order, 'Orders', $rescompanies['company_id'], $order['systemId'], ['companyName' => $rescompanies['name'], 'companyCode' => $rescompanies['bc_code']]);
                                        echo $order['documentType']."\n";
                                }
                        }
                } else {
                        $cronlog .= $apiResponse->getErrorMessage().', ';
                        //$this->sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                        //$this->sheel->admincp->print_action_failed($this->sheel->template->parse_template_phrases('message'), $this->sheel->GPC['returnurl']);
                }
                $systemIdsFromOrders = array_column($orders, 'systemId');
                $query = "SELECT systemid, istriggered, eventdata FROM ".DB_PREFIX."events where companyid = '".$rescompanies['company_id']."' and topic = 'Orders'";
                $result = $this->sheel->db->query($query);

                while($row = $this->sheel->db->fetch_array($result, DB_ASSOC)) {
                        if(!in_array($row['systemid'], $systemIdsFromOrders)) {
                                $checkpoint = 0;
                                if($row['istriggered']) {
                                        if(!$this->sheel->dynamics->init_dynamics('erSalesArchive', $rescompanies['bc_code'])) {
                                                $cronlog .= 'Inactive Dynamics API erSalesArchive for company '.$rescompanies['name'].', ';
                                        }
                                        $searchcondition = '$filter=systemId eq \''.$row['systemid'].'\'';
                                        $apiResponse = $this->sheel->dynamics->select('?'.$searchcondition);
                                        if($apiResponse->isSuccess()) {
                                                if(!empty($archiveorders)) {
                                                        $sqlcheckpoint = $this->sheel->db->query("
                                                                SELECT checkpointid
                                                                FROM ".DB_PREFIX."checkpoints
                                                                WHERE type = '".$order['documentType']."' AND triggeredon = 'archive'
                                                                LIMIT 1
                                                                ");
                                                        if($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                $checkpoint = $rescheckpoint['checkpointid'];
                                                        }
                                                        $archiveorder = $archiveorders[0];
                                                        $this->sheel->db->query("
                                                                UPDATE ".DB_PREFIX."events
                                                                SET eventtime = ".strtotime($archiveorder['systemModifiedAt']).",
                                                                eventpdata = '".$this->sheel->db->escape_string($resevent['eventdata'])."',
                                                                eventdata = '".$this->sheel->db->escape_string(json_encode($archiveorder))."',
                                                                istriggered = '0',
                                                                checkpointid = '".$checkpoint."',
                                                                islast='1'
                                                                WHERE systemid = '".$row['systemid']."'
                                                                ", 0, null, __FILE__, __LINE__);
                                                } else {
                                                        $sqlcheckpoint = $this->sheel->db->query("
                                                                SELECT checkpointid
                                                                FROM ".DB_PREFIX."checkpoints
                                                                WHERE type = '".$order['documentType']."' AND triggeredon = 'delete'
                                                                LIMIT 1
                                                                ");
                                                        if($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
                                                                $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
                                                                $checkpoint = $rescheckpoint['checkpointid'];
                                                        }
                                                        $this->sheel->db->query("
                                                                UPDATE ".DB_PREFIX."events
                                                                SET eventtime = ".TIMESTAMPNOW.",
                                                                eventpdata = '".$this->sheel->db->escape_string($row['eventdata'])."',
                                                                eventdata = '',
                                                                istriggered = '0',
                                                                checkpointid = '".$checkpoint."',
                                                                islast='1'
                                                                WHERE systemid = '".$row['systemid']."'
                                                                ", 0, null, __FILE__, __LINE__);
                                                }
                                        } else {
                                                $cronlog .= $apiResponse->getErrorMessage().', ';
                                                //$this->sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
                                                //$this->sheel->admincp->print_action_failed($this->sheel->template->parse_template_phrases('message'), $this->sheel->GPC['returnurl']);
                                        }

                                }
                        }
                }
        }
}
if(!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.orders.php: '.$cronlog, $nextitem, $this->sheel->timer->get());
?>