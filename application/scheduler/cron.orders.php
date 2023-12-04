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
$searchcondition = '$filter=documentType eq \'Quote\'';
$customers = array();


if ($this->sheel->db->num_rows($sqlcompany) > 0) {
        while ($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {
                $sqlcustomer = $this->sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "customers 
                WHERE status = 'active' AND company_id = '" . $rescompanies['company_id'] . "'
                ");
                if ($this->sheel->db->num_rows($sqlcustomer) > 0) {
                        while ($rescustomers = $this->sheel->db->fetch_array($sqlcustomer, DB_ASSOC)) {
                                $customers[] = $rescustomers;
                        }
                }
                if (!$this->sheel->dynamics->init_dynamics('erSales', $rescompanies['bc_code'])) {
                        $cronlog .= 'Inactive Dynamics API erSales for company ' . $rescompanies['name'] . ', ';
                }
                $apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
                if ($apiResponse->isSuccess()) {
                        $orders = $apiResponse->getData();


                        foreach ($orders as $order) {
                                foreach ($customers as $customer) {
                                        if ($order['sellToCustomerNo'] == $customer['customer_ref']) {
                                                $this->sheel->kafka->produce($order, 'Orders', $rescompanies['company_id'], $order['systemId'], ['companyName' => $rescompanies['name'], 'companyCode' => $rescompanies['bc_code']]);
                                                echo $order['status'] . "\n";
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