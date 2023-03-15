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
    /**
     * Function to delete single or multiple customers 
     *
     * @param
     *            array array with customers ids
     *
     * @return string Returns array with success and error responses where applicable
     */
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
        (customer_id,customer_ref,customername,subscriptionid,usecustomername,customerabout,customerdescription,date_added,status,account_number,available_balance,total_balance,currencyid,timezone,vatnumber,regnumber, autopayment, requestdeletion, logo)
        VALUES (
        NULL,
        '" . $this->sheel->db->escape_string($payload['customerref']) . "',
        '" . $this->sheel->db->escape_string($payload['customername']) . "',
        '" . $this->sheel->db->escape_string($payload['subscriptionid']) . "',
        '" . $this->sheel->db->escape_string($payload['customername']) . "',
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

    private function validatelisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
            SELECT user_id, cid, currencyid, crypto_currentprice, currentprice, crypto_startprice, startprice, project_details, project_title, date_starts, date_end, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds, status, visible, reserve, filtered_auctiontype, bids
            FROM " . DB_PREFIX . "projects
            WHERE project_id = '" . intval($itemid) . "'
            ORDER BY user_id ASC
        ");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $res['iscrypto'] = ((isset($this->sheel->currency->currencies[$res['currencyid']]['iscrypto']) and $this->sheel->currency->currencies[$res['currencyid']]['iscrypto']) ? true : false);
            if ($res['visible']) {
                return 'Listing "' . o($res['project_title']) . ' (#' . $itemid . ')" has already been approved.';
            }
            $secondspast = $res['seconds'];
            $sqltime = $this->sheel->db->query("
                SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
            ");
            $restime = $this->sheel->db->fetch_array($sqltime, DB_ASSOC);
            $new_date_end = $restime['new_date_end'];
            $datenow = DATETIME24H;
            if ($res['project_details'] == 'realtime') {
                if ($datenow > $res['date_starts']) {
                    $new_date_start = $datenow;
                } else {
                    $new_date_start = $res['date_starts'];
                }
            } else {
                $new_date_start = DATETIME24H;
            }
            // add seconds that have past back to the listings date_end
            $this->sheel->db->query("
                UPDATE " . DB_PREFIX . "projects
                SET date_starts = '" . $this->sheel->db->escape_string($new_date_start) . "',
                date_end = '" . $this->sheel->db->escape_string($new_date_end) . "',
                close_date = '0000-00-00 00:00:00',
                status='open',
                visible = '1'
                WHERE project_id = '" . intval($itemid) . "'
                LIMIT 1
            ");

            return true;
        }
        return 'Could not find or validate listing id #' . $itemid;
    }

    public function validate($ids = array())
    {
        $this->sellers = array();
        $allerrors = $successids = $failedids = '';
        $count = 0;
        foreach ($ids as $inc => $itemid) {
            $response = $this->validatelisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_validated_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings validated successfully' : 'Failure validating orders'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }


    private function endearlylisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'closed',
				close_date = '" . DATETIME24H . "'
				WHERE project_id = '" . intval($itemid) . "'
				LIMIT 1
			");
            if ($res['status'] == 'open') {
                $this->sheel->categories->build_category_count($res['cid'], 'subtract', "admin closing multiple listings from admincp: subtracting increment count category id $res[cid]");
            }
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not end early listing id #' . $itemid;
    }

    public function endearly($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->endearlylisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_ended_early_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings ended early successfully' : 'Failure ending listings early'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function markstaffpick($itemid = 0)
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET isstaffpick = '1'
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        return true;
    }

    public function mark_staffpick($ids = array())
    {
        $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->markstaffpick($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = 'marked as staff pick for';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings marked as staff picks successfully' : 'Failure marking listings as staff picks'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }
    private function unmarkstaffpick($itemid = 0)
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET isstaffpick = '0'
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        return true;
    }
    public function unmark_staffpick($ids = array())
    {
        $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->unmarkstaffpick($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = 'unmarked as staff pick for';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings unmarked as staff picks successfully' : 'Failure unmarking listings as staff picks'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function deletelisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $this->sheel->common_listing->physically_remove_listing(intval($itemid));
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not delete listing id #' . $itemid;
    }

    public function delete($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->deletelisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_deleted_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings deleted successfully' : 'Failure deleting listings'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function delistlisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title, status
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            if ($res['status'] == 'open') {
                $this->sheel->categories->build_category_count($res['cid'], 'subtract', "admin delisting listings from admincp: subtracting increment count category id $res[cid]");
            }
            $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'delisted',
				close_date = '" . DATETIME24H . "'
				WHERE project_id = '" . intval($itemid) . "'
			");
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not delist listing id #' . $itemid;
    }

    public function delist($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->delistlisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_delisted_lower}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings delisted successfully' : 'Failure delisting listings'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }
}
?>