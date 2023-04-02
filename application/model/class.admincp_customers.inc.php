<?php

class admincp_customers extends admincp
{
    function construct_new_ref()
    {
        $ref = rand(1, 9) . mb_substr(time(), -7, 10);
        $sql = $this->sheel->db->query("
			SELECT customer_ref
			FROM " . DB_PREFIX . "customers
			WHERE customer_ref = '" . intval($ref) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $ref = rand(1, 9) . mb_substr(time(), -6, 10);
            $sql = $this->sheel->db->query("
				SELECT customer_ref
				FROM " . DB_PREFIX . "customers
				WHERE customer_ref = '" . intval($ref) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
            if ($this->sheel->db->num_rows($sql) > 0) {
                $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                $sql = $this->sheel->db->query("
					SELECT customer_ref
					FROM " . DB_PREFIX . "customers
					WHERE customer_ref = '" . intval($ref) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                    $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                    return $ref;
                } else {
                    return $ref;
                }
            } else {
                return $ref;
            }
        } else {
            return $ref;
        }
    }

    function changestatus($ids = array(), $status)
    {
        $allerrors = $successids = $failedids = $action = $display = '';
        $count = 0;
        if ($status == 'deleted') {
            $action = '{_deleted}';
            $display = '{_deleted}';

        } else if ($status == 'suspended') {
            $action = '{_suspended}';
            $display = '{_suspended}';

        } else if ($status == 'canceled') {
            $action = '{_canceled}';
            $display = '{_canceled}';

        } else if ($status == 'banned') {
            $action = '{_banned}';
            $display = '{_banned}';

        } else if ($status == 'active') {
            $action = '{_active}';
            $display = '{_active}';

        }
        foreach ($ids as $inc => $customerid) {
            $response = $this->dostatuschange($customerid, $action, true, $status);
            if ($response === true) {
                $successids .= "$customerid~";
                $count++;
            } else {
                $failedids .= "$customerid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = $display;
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_customer}' : '{_customers}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'customers Accepted successfully' : 'Failure accepting customers'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array(
            'success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''),
            'errors' => $allerrors,
            'successids' => $successids,
            'failedids' => $failedids
        );
    }

    function dostatuschange($customerid = '', $action = '', $sendemail = true, $status)
    {
        $sql = $this->sheel->db->query("
			SELECT customer_id, customername
			FROM " . DB_PREFIX . "customers
				WHERE customer_id = '" . $customerid . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);

            $this->sheel->db->query("
					UPDATE " . DB_PREFIX . "customers
					SET status = '" . $status . "'
					WHERE customer_id = '" . $this->sheel->db->escape_string($customerid) . "'
				");
            $sql2 = $this->sheel->db->query("
					SELECT u.user_id, u.email, u.username
					FROM " . DB_PREFIX . "users u
						WHERE u.customerid = '" . $this->sheel->db->escape_string($res['customer_id']) . "'
				");

            if ($sendemail and $this->sheel->db->num_rows($sql2) > 0) {
                while ($customer = $this->sheel->db->fetch_array($sql2, DB_ASSOC)) {
                    $existing = array(
                        '{{customer}}' => $customer['username'],
                        '{{oid}}' => $res['customername'],
                        '{{reason}}' => $action
                    );
                    $this->sheel->email->mail = $customer['email'];
                    $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($customer['user_id']);
                    $this->sheel->email->get('customer_status_change');
                    $this->sheel->email->set($existing);
                    $this->sheel->email->send();
                }
            }
            return true;
        }
        return "Customer #$customerid is not in active status";
    }
    function construct_new_customer($payload)
    {
        $this->sheel->db->query("INSERT INTO " . DB_PREFIX . "customers
        (customer_id,customer_ref,customername,subscriptionid,customername2,customerabout,customerdescription,date_added,status,account_number,available_balance,total_balance,currencyid,timezone,vatnumber,regnumber, autopayment, requestdeletion, logo, company_id)
        VALUES (
        NULL,
        '" . $this->sheel->db->escape_string($payload['customerref']) . "',
        '" . $this->sheel->db->escape_string($payload['customername']) . "',
        '" . $this->sheel->db->escape_string($payload['subscriptionid']) . "',
        '" . $this->sheel->db->escape_string($payload['customername2']) . "',
        '" . $this->sheel->db->escape_string($payload['customerabout']) . "',
        '" . $this->sheel->db->escape_string($payload['customerdescription']) . "',
        '" . $this->sheel->db->escape_string($payload['date_added']) . "',
        '" . $this->sheel->db->escape_string($payload['status']) . "',
        '" . $this->sheel->db->escape_string($payload['accountnumber']) . "',
        '" . $this->sheel->db->escape_string($payload['available_balance']) . "',
        '" . $this->sheel->db->escape_string($payload['total_balance']) . "',
        '" . $this->sheel->db->escape_string($payload['currencyid']) . "',
        '" . $this->sheel->db->escape_string($payload['timezone']) . "',
        '" . $this->sheel->db->escape_string($payload['vatnumber']) . "',
        '" . $this->sheel->db->escape_string($payload['regnumber']) . "',
        '" . $this->sheel->db->escape_string($payload['autopayment']) . "',
        '" . $this->sheel->db->escape_string($payload['requestdeletion']) . "',
        '" . $this->sheel->db->escape_string($payload['logo']) . "',
        '" . $this->sheel->db->escape_string($this->get_company_id($payload['companycode'])) . "')
        ");
        $customer_id = $this->sheel->db->insert_id();

        $this->sheel->db->query("
        INSERT INTO " . DB_PREFIX . "customer_profiles
        (id, customer_id, address, address2, phone, mobile, contact, email, city, state, zipcode, country, dateadded, type,billing_type, status, isdefault)
        VALUES(
        NULL,
        '" . $customer_id . "',
        '" . $this->sheel->db->escape_string($payload['address']) . "',
        '" . $this->sheel->db->escape_string($payload['address2']) . "',
        '" . $this->sheel->db->escape_string($payload['phone']) . "',
        '" . $this->sheel->db->escape_string($payload['mobile']) . "',
        '" . $this->sheel->db->escape_string($payload['contact']) . "',
        '" . $this->sheel->db->escape_string($payload['email']) . "',
        '" . $this->sheel->db->escape_string($payload['city']) . "',
        '" . $this->sheel->db->escape_string($payload['state']) . "',
        '" . $this->sheel->db->escape_string($payload['zipcode']) . "',
        '" . $this->sheel->db->escape_string($this->sheel->common_location->print_country_name_bycode($payload['country'], $_SESSION['sheeldata']['user']['slng'])) . "',
        '" . DATETIME24H . "',
        'shipping',
        'business',
        '1',
        '1')
        ", 0, null, __FILE__, __LINE__);
        $this->build_customer_subscription($customer_id, $payload['subscriptionid'], 'account');
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), 'customer created successfully', "A new customer With ID: '$customer_id' was created successfully.");
        return $customer_id;
    }

    function refresh_customer($payload)
    {
        $refreshed = false;
        $customerid = intval($payload['customer_id']);
        $this->sheel->db->query("UPDATE " . DB_PREFIX . "customers
        SET customer_ref = '" . $this->sheel->db->escape_string($payload['customerref']) . "',
            customername = '" . $this->sheel->db->escape_string($payload['customername']) . "',
            customername2 = '" . $this->sheel->db->escape_string($payload['customername2']) . "',
            customerabout = '" . $this->sheel->db->escape_string($payload['customerabout']) . "',
            customerdescription = '" . $this->sheel->db->escape_string($payload['customerdescription']) . "',
            account_number = '" . $this->sheel->db->escape_string($payload['accountnumber']) . "',
            vatnumber = '" . $this->sheel->db->escape_string($payload['vatnumber']) . "',
            regnumber = '" . $this->sheel->db->escape_string($payload['regnumber']) . "'
        WHERE customer_id = '" . $customerid . "'
        ");

        $this->sheel->db->query("UPDATE " . DB_PREFIX . "customer_profiles
        SET address = '" . $this->sheel->db->escape_string($payload['address']) . "',
            address2 = '" . $this->sheel->db->escape_string($payload['address2']) . "',
            phone = '" . $this->sheel->db->escape_string($payload['phone']) . "',
            mobile = '" . $this->sheel->db->escape_string($payload['mobile']) . "',
            contact = '" . $this->sheel->db->escape_string($payload['contact']) . "',
            email = '" . $this->sheel->db->escape_string($payload['email']) . "',
            city = '" . $this->sheel->db->escape_string($payload['city']) . "',
            state = '" . $this->sheel->db->escape_string($payload['state']) . "',
            zipcode = '" . $this->sheel->db->escape_string($payload['zipcode']) . "',
            country = '" . $this->sheel->common_location->print_country_name_bycode($payload['country'], $_SESSION['sheeldata']['user']['slng']) . "'
        WHERE customer_id = '" . $customerid . "'
        ");
        $refreshed = true;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), 'customer refreshed successfully', "customer With ID: '$customerid' was refreshed successfully.");
        return $refreshed;
    }

    /**
     * Function for creating a new customer subscription plan.
     *
     * @param       integer      customer id
     * @param       integer      subscription id
     * @param       string       payment method (account, creditcard, ipn, bank, check)
     * @param       integer      subscription role id
     *
     */
    private function build_customer_subscription($customerid = 0, $subscriptionid = 0, $paymethod = 'account')
    {
        $subscription_plan_result = array();
        $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, description_" . $_SESSION['sheeldata']['user']['slng'] . " AS description, cost, length, units,  active, canremove, visible_registration, visible_upgrade, icon
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                AND type = 'product'
                ", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $subscription_plan_result = $this->sheel->db->fetch_array($sql, DB_ASSOC);


            $subscription_plan_cost = sprintf('%01.2f', $subscription_plan_result['cost']);
            $subscription_length = $this->sheel->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
            $subscription_renew_date = $this->sheel->subscription->print_subscription_renewal_datetime($subscription_length);
            $sql_check = $this->sheel->db->query("
                                SELECT c.id
                                FROM " . DB_PREFIX . "subscription_customer c
                                LEFT JOIN " . DB_PREFIX . "subscription s ON c.subscriptionid = s.subscriptionid
                                WHERE c.customerid = '" . intval($customerid) . "'
                                        AND s.type = 'product'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
            if ($this->sheel->db->num_rows($sql_check) == 0) {
                if (empty($paymethod)) {
                    $paymethod = 'account';
                }
                // build membership for user and set to unpaid / not active
                $this->sheel->db->query("
                                        INSERT INTO " . DB_PREFIX . "subscription_customer
                                        (id, subscriptionid, customerid, paymethod, startdate, renewdate, autopayment, active)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($customerid) . "',
                                        '" . $this->sheel->db->escape_string($paymethod) . "',
                                        '" . DATETIME24H . "',
                                        '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                        '1',
                                        'no')
                                ", 0, null, __FILE__, __LINE__);
                if ($subscription_plan_result['cost'] <= 0) {
                    $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_customer
                                                SET active = 'yes',
                                                autopayment = '1'
                                                WHERE customerid = '" . intval($customerid) . "'
                                                        AND subscriptionid = '" . intval($subscriptionid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                }
            }
        }
    }


    function get_user_id($rid)
    {
        $uid = 0;
        $sql = $this->sheel->db->query("
				SELECT customer_id
				FROM " . DB_PREFIX . "customers
				WHERE customer_id = '" . $rid . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $uid = $res['customer_id'];
        } else {
            $uid = 0;
        }
        return $uid;
    }
    function get_company_id($code)
    {
        $cid = 0;
        $sql = $this->sheel->db->query("
				SELECT company_id
				FROM " . DB_PREFIX . "companies
				WHERE bc_code = '" . $code . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $cid = $res['company_id'];
        } else {
            $cid = 0;
        }
        return $cid;
    }
    function get_company_name($companyid, $code = true)
    {
        $returnedc = '';
        $sql = $this->sheel->db->query("
				SELECT name, bc_code
				FROM " . DB_PREFIX . "companies
				WHERE company_id = '" . $companyid . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            if ($code) {
                $returnedc = $res['bc_code'];
            } else {
                $returnedc = $res['name'];
            }
        } else {

        }
        return $returnedc;
    }


}
?>