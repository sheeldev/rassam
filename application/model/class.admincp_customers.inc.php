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
        if ($this->sheel->db->num_rows($sql) > 0)
        {
            $ref = rand(1, 9) . mb_substr(time(), -6, 10);
            $sql = $this->sheel->db->query("
				SELECT customer_ref
				FROM " . DB_PREFIX . "customers
				WHERE customer_ref = '" . intval($ref) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
            if ($this->sheel->db->num_rows($sql) > 0)
            {
                $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                $sql = $this->sheel->db->query("
					SELECT customer_ref
					FROM " . DB_PREFIX . "customers
					WHERE customer_ref = '" . intval($ref) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
                    $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                    return $ref;
                }
                else
                {
                    return $ref;
                }
            }
            else
            {
                return $ref;
            }
        }
        else
        {
            return $ref;
        }
    }

    function changestatus($ids = array(), $status)
    {
        $allerrors = $successids = $failedids = $action = $display =  '';
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
					SET status = '". $status ."'
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
        (customer_id,customer_ref,customername,subscriptionid,customername2,customerabout,customerdescription,date_added,status,account_number,available_balance,total_balance,currencyid,timezone,vatnumber,regnumber, autopayment, requestdeletion, logo)
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
        '" . $this->sheel->db->escape_string($payload['logo']) . "')
        ");

        $customer_id = $this->sheel->db->insert_id();

        $this->sheel->db->query("
        INSERT INTO " . DB_PREFIX . "customer_profiles
        (id, customer_id, address, address2, phone, email, city, state, zipcode, country, dateadded, type,billing_type, status, isdefault)
        VALUES(
        NULL,
        '" . $customer_id . "',
        '" . $this->sheel->db->escape_string($payload['address']) . "',
        '" . $this->sheel->db->escape_string($payload['address2']) . "',
        '" . $this->sheel->db->escape_string($payload['phone']) . "',
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
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), 'customer created successfully', "A new customer With ID: '$customer_id' was created successfully.");
        return $customer_id;
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
}
?>