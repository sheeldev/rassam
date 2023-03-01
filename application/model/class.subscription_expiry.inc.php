<?php
if (!class_exists('subscription'))
{
	exit;
}

/**
* Subscription expiry class to perform the majority of subscription expiration functionality in sheel.
*
* @package      sheel\Membership\Expiry
* @version      1.0.0.0
* @author       sheel
*/
class subscription_expiry extends subscription
{
        /**
        * Function to expire user membership plans as required (parsed from cron.1minute.php)
        */
        function user_subscription_plans()
        {
                $notice = $failedrenewalusernames = $noautopayrenewalusernames = $paidrenewalusernames = $freerenewalusernames = $cancelledusernames = $recurringusernames = '';
                $failedrenewalcount = $noautopayrenewalcount = $paidrenewalcount = $freerenewalcount = $cancelledplans = $recurringplans = $all = 0;
                $slng = $this->sheel->language->fetch_site_slng();
                $subscriptioncheck = $this->sheel->db->query("
                        SELECT u.user_id, u.username, u.first_name, u.last_name, u.usecompanyname, u.companyname, u.email, s.cost, s.title_" . $slng . " AS title, s.description_" . $slng . " AS description, s.length, s.units, s.subscriptiongroupid, s.roleid, su.id, su.subscriptionid, su.paymethod, su.autopayment AS subscription_autopayment, su.active, su.migrateto, su.migratelogic, su.autorenewal, su.recurring, su.cancelled
                        FROM " . DB_PREFIX . "subscription_user AS su
			LEFT JOIN " . DB_PREFIX . "users u ON (su.user_id = u.user_id)
			LEFT JOIN " . DB_PREFIX . "subscription s ON (su.subscriptionid = s.subscriptionid)
                        WHERE su.renewdate <= '" . DATETODAY . " " . TIMENOW . "'
				AND u.status = 'active'
				AND su.active = 'yes'
				AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($subscriptioncheck) > 0)
                {
                        while ($res_subscription_check = $this->sheel->db->fetch_array($subscriptioncheck, DB_ASSOC))
                        { // we have active plans that need to EXPIRE
                                if ($res_subscription_check['cancelled'] == '1')
				{ // users who have cancelled and don't want to be rebilled
					$this->sheel->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id'], false, true);
					$cancelledusernames .= $res_subscription_check['username'] . ', ';
					$cancelledplans++;
					$all++;
				}
				else if ($res_subscription_check['recurring'] == '1' OR $res_subscription_check['paymethod'] != 'account')
				{ // users who are on a recurring billing via credit card
					// todo: should be run in cron.daily.php -> callouts()..
					$this->sheel->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id'], false, false);
					$recurringusernames .= $res_subscription_check['username'] . ', ';
					$recurringplans++;
					$all++;
				}
				else if ($res_subscription_check['migrateto'] > 0)
                                { // auto migration of plan for this user
					$slng = $this->sheel->language->fetch_user_slng($res_subscription_check['user_id']);
					switch ($res_subscription_check['migratelogic'])
					{
						case 'none':
						{ // no transaction will be created
							$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
							$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
							$this->sheel->db->query("
								UPDATE " . DB_PREFIX . "subscription_user
								SET active = 'yes',
								startdate = '" . DATETIME24H . "',
								renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
								subscriptionid = '" . $res_subscription_check['subscriptionid'] . "',
								migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
								migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
								invoiceid = '0'
								WHERE id = '" . $res_subscription_check['id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// unset frozen membership status for items in customers cart
							$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
							$this->handle_no_stores_permission($res_subscription_check['user_id']);
							$freerenewalusernames .= $res_subscription_check['username'] . ', ';
							$freerenewalcount++;
							$all++;
							break;
						}
						case 'waived':
						{ // insert waived transaction & activate new plan
							$renewed_invoice_id = $this->sheel->accounting->insert_transaction(array(
								'subscriptionid' => intval($res_subscription_check['subscriptionid']),
								'user_id' => intval($res_subscription_check['user_id']),
								'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
								'amount' => 0,
								'paid' => 0,
								'status' => 'paid',
								'invoicetype' => 'subscription',
								'paymethod' => $res_subscription_check['paymethod'],
								'createdate' => DATETIME24H,
								'duedate' => DATEINVOICEDUE,
								'paiddate' => DATETIME24H,
								'custommessage' => '{_subscription_plan_migrated_to} ' . $res_subscription_check['title'],
								'returnid' => 1
							));
							$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
							$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
							$this->sheel->db->query("
								UPDATE " . DB_PREFIX . "subscription_user
								SET active = 'yes',
								renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
								startdate = '" . DATETIME24H . "',
								subscriptionid = '" . $res_subscription_check['subscriptionid'] . "',
								migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
								migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
								invoiceid = '" . $renewed_invoice_id . "'
								WHERE id = '" . $res_subscription_check['id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// unset frozen membership status for items in customers cart
							$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
							$this->handle_no_stores_permission($res_subscription_check['user_id']);
							$freerenewalusernames .= $res_subscription_check['username'] . ', ';
							$freerenewalcount++;
							$all++;
							break;
						}
						case 'unpaid':
						{ // insert unpaid transaction & deactivate new plan
							if ($res_subscription_check['active'] == 'yes')
							{ // customer can log-in and make payment
								$renewed_invoice_id = $this->sheel->accounting->insert_transaction(array(
									'subscriptionid' => $res_subscription_check['subscriptionid'],
									'user_id' => $res_subscription_check['user_id'],
									'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
									'amount' => sprintf("%01.2f", $res_subscription_check['cost']),
									'status' => 'scheduled',
									'invoicetype' => 'subscription',
									'paymethod' => $res_subscription_check['paymethod'],
									'createdate' => DATETIME24H,
									'duedate' => DATEINVOICEDUE,
									'custommessage' => '{_subscription_plan_migrated_to} ' . $res_subscription_check['title'],
									'returnid' => 1
								));
								// update subscription table
								$this->sheel->db->query("
									UPDATE " . DB_PREFIX . "subscription_user
									SET active = 'no',
									subscriptionid = '" . $res_subscription_check['subscriptionid'] . "',
									migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
									migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
									invoiceid = '" . $renewed_invoice_id . "'
									WHERE id = '" . $res_subscription_check['id'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								// log email for today so we do not resend
								$this->sheel->db->query("
									INSERT INTO " . DB_PREFIX . "subscriptionlog
									(subscriptionlogid, user_id, date_sent)
									VALUES(
									NULL,
									'" . $res_subscription_check['user_id'] . "',
									'" . DATETODAY . "')
								", 0, null, __FILE__, __LINE__);
								// insert subscription invoice reminder so we don't resend again today
								$dateremind = $this->sheel->datetimes->fetch_date_fromnow($this->sheel->config['invoicesystem_daysafterfirstreminder']);
								$this->sheel->db->query("
									INSERT INTO " . DB_PREFIX . "invoicelog
									(invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
									VALUES(
									NULL,
									'" . $res_subscription_check['user_id'] . "',
									'" . $renewed_invoice_id . "',
									'subscription',
									'" . DATETODAY . "',
									'" . $this->sheel->db->escape_string($dateremind) . "')
								", 0, null, __FILE__, __LINE__);
								// set frozen membership status for items in customers cart
								$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '1');
								$this->handle_no_stores_permission($res_subscription_check['user_id']);
							}
							$failedrenewalusernames .= $res_subscription_check['username'] . ', ';
							$failedrenewalcount++;
							$all++;
							break;
						}
						case 'paid':
						{ // create paid transaction
							$renewed_invoice_id = $this->sheel->accounting->insert_transaction(array(
								'subscriptionid' => intval($res_subscription_check['subscriptionid']),
								'user_id' => intval($res_subscription_check['user_id']),
								'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
								'amount' => sprintf("%01.2f", $res_subscription_check['cost']),
								'paid' => sprintf("%01.2f", $res_subscription_check['cost']),
								'status' => 'paid',
								'invoicetype' => 'subscription',
								'paymethod' => $res_subscription_check['paymethod'],
								'createdate' => DATETIME24H,
								'duedate' => DATEINVOICEDUE,
								'paiddate' => DATETIME24H,
								'custommessage' => '{_subscription_plan_migrated_to} ' . $res_subscription_check['title'],
								'returnid' => 1
							));
							$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
							$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
							$this->sheel->db->query("
								UPDATE " . DB_PREFIX . "subscription_user
								SET active = 'yes',
								renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
								startdate = '" . DATETIME24H . "',
								subscriptionid = '" . $res_subscription_check['subscriptionid'] . "',
								migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
								migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
								invoiceid = '" . $renewed_invoice_id . "'
								WHERE id = '" . $res_subscription_check['id'] . "'
							", 0, null, __FILE__, __LINE__);
							// unset frozen membership status for items in customers cart
							$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
							$this->handle_no_stores_permission($res_subscription_check['user_id']);
							$paidrenewalusernames .= $res_subscription_check['username'] . ', ';
							$paidrenewalcount++;
							$all++;
							break;
						}
					}
					if ($res_subscription_check['migratelogic'] != 'none' AND $res_subscription_check['active'] == 'yes')
					{ // obtain any unpaid membership migration invoice
						$sql_new_invoice = $this->sheel->db->query("
							SELECT totalamount AS amount, invoiceid, description, transactionid
							FROM " . DB_PREFIX . "invoices
							WHERE invoiceid = '" . intval($renewed_invoice_id) . "'
								AND (status = 'unpaid' OR status = 'scheduled')
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($this->sheel->db->num_rows($sql_new_invoice) > 0)
						{
							$res_new_invoice = $this->sheel->db->fetch_array($sql_new_invoice, DB_ASSOC);
							if ($res_subscription_check['subscription_autopayment'] == '1')
							{ // membership log > did we already sent an email to this customer?
								$senttoday = $this->sheel->db->query("
									SELECT subscriptionlogid
									FROM " . DB_PREFIX . "subscriptionlog
									WHERE user_id = '" . $res_subscription_check['user_id'] . "'
										AND date_sent = '" . DATETODAY . "'
								", 0, null, __FILE__, __LINE__);
								if ($this->sheel->db->num_rows($senttoday) == 0)
								{ // membership log for today and send email to customer
									$this->sheel->db->query("
										INSERT INTO " . DB_PREFIX . "subscriptionlog
										(subscriptionlogid, user_id, date_sent)
										VALUES(
										NULL,
										'" . $res_subscription_check['user_id'] . "',
										'" . DATETODAY . "')
									", 0, null, __FILE__, __LINE__);
									// subscription renewal via online account balance
									$sq1_account_balance = $this->sheel->db->query("
										SELECT available_balance, total_balance
										FROM " . DB_PREFIX . "users
										WHERE user_id = '" . $res_subscription_check['user_id'] . "'
									", 0, null, __FILE__, __LINE__);
									if ($this->sheel->db->num_rows($sq1_account_balance) > 0)
									{
										$get_account_array = $this->sheel->db->fetch_array($sq1_account_balance, DB_ASSOC);
										$tmp = $this->sheel->common->fetch_payment_profiles($res_subscription_check['user_id']); // payment_profile, payment_profile_backup
										if ($tmp['payment_profile'] == 'account' AND $get_account_array['available_balance'] >= $res_new_invoice['amount'])
										{ // account balance
											$now_total = $get_account_array['total_balance'];
											$now_avail = $get_account_array['available_balance'];
											$new_total = ($now_total - $res_new_invoice['amount']);
											$new_avail = ($now_avail - $res_new_invoice['amount']);
											// re-adjust customers online account balance (minus subscription fee amount)
											$this->sheel->db->query("
												UPDATE " . DB_PREFIX . "users
												SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
												total_balance = '" . sprintf("%01.2f", $new_total) . "'
												WHERE user_id = '" . $res_subscription_check['user_id'] . "'
											", 0, null, __FILE__, __LINE__);
											// record transaction ledger
											$array = array(
												'userid' => $res_subscription_check['user_id'],
												'credit' => 0,
												'debit' => sprintf("%01.2f", $res_new_invoice['amount']),
												'description' => $res_subscription_check['subscriptionid'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
												'invoiceid' => $res_new_invoice['invoiceid'],
												'transactionid' => $res_new_invoice['transactionid'],
												'custom' => '',
												'staffnotes' => '',
												'technical' => 'Account balance debit based on [user_subscription_plans()] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
											);
											$this->sheel->accounting->account_balance($array);
											unset($array);
											// pay existing membership plan invoice via online account balance
											$this->sheel->db->query("
												UPDATE " . DB_PREFIX . "invoices
												SET status = 'paid',
												paid = '" . sprintf("%01.2f", $res_new_invoice['amount']) . "',
												paiddate = '" . DATETIME24H . "',
												paymethod = 'account'
												WHERE user_id = '" . $res_subscription_check['user_id'] . "'
													AND invoiceid = '" . $res_new_invoice['invoiceid'] . "'
											", 0, null, __FILE__, __LINE__);
											$this->sheel->accounting_payment->insert_income_reported($res_subscription_check['user_id'], sprintf("%01.2f", $res_new_invoice['amount']), 'credit');
											$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
											$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
											$this->sheel->db->query("
												UPDATE " . DB_PREFIX . "subscription_user
												SET active = 'yes',
												startdate = '" . DATETIME24H . "',
												renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
												subscriptionid = '" . $res_subscription_check['subscriptionid'] . "',
												migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
												migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
												invoiceid = '" . $res_new_invoice['invoiceid'] . "',
												paymethod = 'account'
												WHERE id = '" . $res_subscription_check['id'] . "'
												LIMIT 1
											", 0, null, __FILE__, __LINE__);
											// unset frozen membership status for items in customers cart
											$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
											$this->handle_no_stores_permission($res_subscription_check['user_id']);
											$this->sheel->email->mail = $res_subscription_check['email'];
											$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res_subscription_check['user_id']);
											$this->sheel->email->get('subscription_payment_renewed');
											$this->sheel->email->set(array(
												'{{customer}}' => $res_subscription_check['username'],
												'{{amount}}' => $this->sheel->currency->format($res_new_invoice['amount']),
												'{{description}}' => $res_new_invoice['description'],
												'{{paymethod}}' => $res_subscription_check['paymethod']
											));
											$this->sheel->email->send();
											$paidrenewalusernames .= $res_subscription_check['username'] . ', ';
											$paidrenewalcount++;
											$all++;
										}
										else if (is_numeric($tmp['payment_profile']) OR is_numeric($tmp['payment_profile_backup']))
										{ // TODO!!! charge primary or backup credit card for membership payment
											/*
											$usecard = $this->sheel->accounting_creditcard->fetch_default_creditcard_profile($res_subscription_check['user_id']);
											// charge credit card on file
											$this->sheel->paymentgateway->userid = intval($userid);
											$this->sheel->paymentgateway->set_customer($this->sheel->fetch_user('username', $this->sheel->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "user_id")), $this->sheel->fetch_user('phone', $res_subscription_check['user_id']), $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
											$this->sheel->paymentgateway->set_ccard($sql_cc_arr['name_on_card'], $sql_cc_arr['creditcard_type'], $decrypted_card_no, mb_substr($sql_cc_arr['creditcard_expiry'], 0, 2), mb_substr($sql_cc_arr['creditcard_expiry'], -4), $sql_cc_arr['cvv2']);
											$this->sheel->paymentgateway->set_order($amount1, $transa1, '{_credit_card_authentication_amount_one}', $this->sheel->config['authentication_capture'], '', '', '', $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['code'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left']);
											if ($this->sheel->paymentgateway->process())
											{
												$gatewaytxn = $this->sheel->paymentgateway->get_transaction_code();
												// payment from cc for xyz into account balance
												// debit from account balance for xyz..
											}
											else
											{
												$this->sheel->email->mail = SITE_CONTACT;
												$this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
												$this->sheel->email->get('creditcard_processing_error');
												$this->sheel->email->set(array(
													'{{gatewayresponse}}' => $this->sheel->paymentgateway->get_answer(),
													'{{gatewaymessage}}' => $this->sheel->paymentgateway->get_response_message(),
													'{{gatewayerrorcode}}' => $this->sheel->paymentgateway->error['number'],
													'{{itemname}}' => $res_subscription_check['title'] . ' {_subscription} (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
													'{{ipaddress}}' => IPADDRESS,
													'{{location}}' => LOCATION,
													'{{scripturi}}' => SCRIPT_URI,
													'{{gateway}}' => '{_' . $this->sheel->config['use_internal_gateway'] . '}',
													'{{member}}' => $res_subscription_check['username'],
													'{{memberemail}}' => $res_subscription_check['email'],
													'{{title}}' => '',
													'{{amount}}' => ''
												));
												$this->sheel->email->send();
											}*/
										}
									}
								}
							}
						}
					}
                                }
                                else
                                { // no membership migration
					$this->sheel->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id'], false, false);
					if ($res_subscription_check['cost'] > 0)
					{ // this plan has a cost
						if ($res_subscription_check['autorenewal'] > 0)
						{ // and auto membership renewals are on for the user..
							$senttoday = $this->sheel->db->query("
								SELECT user_id
								FROM " . DB_PREFIX . "subscriptionlog
								WHERE user_id = '" . $res_subscription_check['user_id'] . "'
									AND date_sent = '" . DATETODAY . "'
							", 0, null, __FILE__, __LINE__);
							if ($this->sheel->db->num_rows($senttoday) == 0)
							{ // log membership email for today and send email to user
								$this->sheel->db->query("
									INSERT INTO " . DB_PREFIX . "subscriptionlog
									(subscriptionlogid, user_id, date_sent)
									VALUES(
									NULL,
									'" . $res_subscription_check['user_id'] . "',
									'" . DATETODAY . "')
								", 0, null, __FILE__, __LINE__);
								// do we already have a pending scheduled unpaid invoice for this customer?
								$sqlpaidchk = $this->sheel->db->query("
									SELECT invoiceid
									FROM " . DB_PREFIX . "invoices
									WHERE user_id = '" . $res_subscription_check['user_id'] . "'
										AND subscriptionid = '" . $res_subscription_check['subscriptionid'] . "'
										AND (status = 'scheduled' OR status = 'unpaid')
										AND invoicetype = 'subscription'
										AND (paid <= '0' OR paid = '')
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($this->sheel->db->num_rows($sqlpaidchk) > 0)
								{ // user has pending membership transaction associated so use this invoice id instead
									$respaid = $this->sheel->db->fetch_array($sqlpaidchk, DB_ASSOC);
									$renewed_invoice_id = $respaid['invoiceid'];
								}
								else
								{ // create new pending membership for user and collect invoice id
									$renewed_invoice_id = $this->sheel->accounting->insert_transaction(array(
										'subscriptionid' => intval($res_subscription_check['subscriptionid']),
										'user_id' => intval($res_subscription_check['user_id']),
										'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
										'amount' => sprintf("%01.2f", $res_subscription_check['cost']),
										'paid' => 0,
										'status' => 'scheduled',
										'invoicetype' => 'subscription',
										'paymethod' => $res_subscription_check['paymethod'],
										'createdate' => DATETIME24H,
										'duedate' => DATEINVOICEDUE,
										'returnid' => 1
									));
								}
								// insert membership reminder row so we don't resend again today
								$dateremind = $this->sheel->datetimes->fetch_date_fromnow($this->sheel->config['invoicesystem_daysafterfirstreminder']);
								$this->sheel->db->query("
									INSERT INTO " . DB_PREFIX . "invoicelog
									(invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
									VALUES(
									NULL,
									'" . $res_subscription_check['user_id'] . "',
									'" . intval($renewed_invoice_id) . "',
									'subscription',
									'" . DATETODAY . "',
									'" . $dateremind . "')
								", 0, null, __FILE__, __LINE__);
								// obtain invoice information once again so we have most accurate cost
								$sql_new_invoice = $this->sheel->db->query("
									SELECT totalamount, invoiceid, amount, description, transactionid
									FROM " . DB_PREFIX . "invoices
									WHERE invoiceid = '" . intval($renewed_invoice_id) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($this->sheel->db->num_rows($sql_new_invoice) > 0)
								{
									$res_new_invoice = $this->sheel->db->fetch_array($sql_new_invoice, DB_ASSOC);
									// auto-payments checkup (user sets this option via membership > settings menu)
									if ($res_subscription_check['subscription_autopayment'] == '1' AND $res_subscription_check['recurring'] == '0')
									{ // membership renewal via [default payment source, if fails, try backup payment source]
										$sq1_account_balance = $this->sheel->db->query("
											SELECT available_balance, total_balance
											FROM " . DB_PREFIX . "users
											WHERE user_id = '" . $res_subscription_check['user_id'] . "'
										", 0, null, __FILE__, __LINE__);
										if ($this->sheel->db->num_rows($sq1_account_balance) > 0)
										{
											$get_account_array = $this->sheel->db->fetch_array($sq1_account_balance, DB_ASSOC);
											$tmp = $this->sheel->common->fetch_payment_profiles($res_subscription_check['user_id']); // payment_profile, payment_profile_backup
											if ($tmp['payment_profile'] == 'account' AND $get_account_array['available_balance'] >= $res_new_invoice['totalamount'])
											{ // account balance
												$now_total = $get_account_array['total_balance'];
												$now_avail = $get_account_array['available_balance'];
												$new_total = ($now_total - $res_new_invoice['totalamount']);
												$new_avail = ($now_avail - $res_new_invoice['totalamount']);
												// adjust account balance (minus membership cost)
												$this->sheel->db->query("
													UPDATE " . DB_PREFIX . "users
													SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
													total_balance = '" . sprintf("%01.2f", $new_total) . "'
													WHERE user_id = '" . $res_subscription_check['user_id'] . "'
												", 0, null, __FILE__, __LINE__);
												// record transaction ledger
												// Account Debit for Buyer & Seller Membership (1M) from Account Balance
												$array = array(
													'userid' => $res_subscription_check['user_id'],
													'credit' => 0,
													'debit' => $res_new_invoice['totalamount'],
													'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
													'invoiceid' => $res_new_invoice['invoiceid'],
													'transactionid' => $res_new_invoice['transactionid'],
													'custom' => '',
													'staffnotes' => '',
													'technical' => 'Account balance debit based on [user_subscription_plans()] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
												);
												$this->sheel->accounting->account_balance($array);
												unset($array);
												// pay invoice via account balance
												$this->sheel->db->query("
													UPDATE " . DB_PREFIX . "invoices
													SET status = 'paid',
													paid = '" . sprintf("%01.2f", $res_new_invoice['totalamount']) . "',
													paiddate = '" . DATETIME24H . "',
													paymethod = '" . $this->sheel->db->escape_string($res_subscription_check['paymethod']) . "'
													WHERE user_id = '" . $res_subscription_check['user_id'] . "'
														AND invoiceid = '" . $res_new_invoice['invoiceid'] . "'
												", 0, null, __FILE__, __LINE__);
												// record spending habits for this user
												$this->sheel->accounting_payment->insert_income_spent($res_subscription_check['user_id'], sprintf("%01.2f", $res_new_invoice['totalamount']), 'credit');
												$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
												$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
												// update customer membership table with new information
												$this->sheel->db->query("
													UPDATE " . DB_PREFIX . "subscription_user
													SET active = 'yes',
													renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
													startdate = '" . DATETIME24H . "',
													migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
													migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
													invoiceid = '" . $res_new_invoice['invoiceid'] . "',
													paymethod = '" . $this->sheel->db->escape_string($res_subscription_check['paymethod']) . "'
													WHERE user_id = '" . $res_subscription_check['user_id'] . "'
														AND subscriptionid = '" . $res_subscription_check['subscriptionid'] . "'
												", 0, null, __FILE__, __LINE__);
												// unset frozen membership status for items in customers cart
												$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
												$this->handle_no_stores_permission($res_subscription_check['user_id']);
												$this->sheel->email->mail = $res_subscription_check['email'];
												$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res_subscription_check['user_id']);
												$this->sheel->email->get('subscription_payment_renewed');
												$this->sheel->email->set(array(
													'{{customer}}' => $res_subscription_check['username'],
													'{{amount}}' => $this->sheel->currency->format($res_new_invoice['amount']),
													'{{description}}' => $res_new_invoice['description'],
													'{{paymethod}}' => $res_subscription_check['paymethod']
												));
												$this->sheel->email->send();
												$paidrenewalusernames .= $res_subscription_check['username'] . ', ';
												$paidrenewalcount++;
												$all++;
											}
											else if (is_numeric($tmp['payment_profile']) OR is_numeric($tmp['payment_profile_backup']))
											{ // TODO!! charge primary or backup credit card on file
												/*
												$usecard = $this->sheel->accounting_creditcard->fetch_default_creditcard_profile($res_subscription_check['user_id']);
												// charge credit card on file
												$this->sheel->paymentgateway->userid = intval($userid);
												$this->sheel->paymentgateway->set_customer($this->sheel->fetch_user('username', $this->sheel->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "user_id")), $this->sheel->fetch_user('phone', $res_subscription_check['user_id']), $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
												$this->sheel->paymentgateway->set_ccard($sql_cc_arr['name_on_card'], $sql_cc_arr['creditcard_type'], $decrypted_card_no, mb_substr($sql_cc_arr['creditcard_expiry'], 0, 2), mb_substr($sql_cc_arr['creditcard_expiry'], -4), $sql_cc_arr['cvv2']);
												$this->sheel->paymentgateway->set_order($amount1, $transa1, '{_credit_card_authentication_amount_one}', $this->sheel->config['authentication_capture'], '', '', '', $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['code'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left']);
												if ($this->sheel->paymentgateway->process())
												{
													$gatewaytxn = $this->sheel->paymentgateway->get_transaction_code();
													// payment from cc for xyz into account balance
													// debit from account balance for xyz..
												}
												else
												{
													$this->sheel->email->mail = SITE_CONTACT;
													$this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
													$this->sheel->email->get('creditcard_processing_error');
													$this->sheel->email->set(array(
														'{{gatewayresponse}}' => $this->sheel->paymentgateway->get_answer(),
														'{{gatewaymessage}}' => $this->sheel->paymentgateway->get_response_message(),
														'{{gatewayerrorcode}}' => $this->sheel->paymentgateway->error['number'],
														'{{itemname}}' => $res_subscription_check['title'] . ' {_subscription} (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
														'{{ipaddress}}' => IPADDRESS,
														'{{location}}' => LOCATION,
														'{{scripturi}}' => SCRIPT_URI,
														'{{gateway}}' => '{_' . $this->sheel->config['use_internal_gateway'] . '}',
														'{{member}}' => $res_subscription_check['username'],
														'{{memberemail}}' => $res_subscription_check['email'],
														'{{title}}' => '',
														'{{amount}}' => ''
													));
													$this->sheel->email->send();
												}
												*/
											}
										}
									}
								}
							}
						}
					}
					else
					{ // activate free membership and create associated transaction
						$renewed_invoice_id = $this->sheel->accounting->insert_transaction(array(
							'subscriptionid' => intval($res_subscription_check['subscriptionid']),
							'user_id' => intval($res_subscription_check['user_id']),
							'description' => $res_subscription_check['title'] . ' (' . $res_subscription_check['length'] . ' ' . $this->print_unit($res_subscription_check['units']) . ')',
							'amount' => 0,
							'paid' => 0,
							'status' => 'paid',
							'invoicetype' => 'subscription',
							'paymethod' => 'account',
							'createdate' => DATETIME24H,
							'duedate' => DATEINVOICEDUE,
							'paiddate' => DATETIME24H,
							'custommessage' => '{_subscription_plan_was_renewed}',
							'returnid' => 1
						));
						$subscription_length = $this->subscription_length($res_subscription_check['units'], $res_subscription_check['length']);
						$subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
						$this->sheel->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
							subscriptionid = '" . $this->sheel->db->escape_string($res_subscription_check['subscriptionid']) . "',
							migrateto = '" . $this->sheel->db->escape_string($res_subscription_check['migrateto']) . "',
							migratelogic = '" . $this->sheel->db->escape_string($res_subscription_check['migratelogic']) . "',
							invoiceid = '" . $this->sheel->db->escape_string($renewed_invoice_id) . "',
							cancelled = '0'
							WHERE id = '" . $res_subscription_check['id'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// unset frozen membership status for items in customers cart
						$this->sheel->auction->set_frozen_flag($res_subscription_check['user_id'], '0');
						$this->handle_no_stores_permission($res_subscription_check['user_id']);
						$freerenewalusernames .= $res_subscription_check['username'] . ', ';
						$freerenewalcount++;
						$all++;
					}
                                }
                        }
			$notice = 'subscription_expiry->user_subscription_plans(), ';
                }
                return $notice;
        }
        /**
        * Function to expire user membership plan exemptions (parsed from cron.1minute.php)
        */
        function user_subscription_exemptions()
        {
                $cronlog = '';
                $expiredexemptions = 0;
                $exemptionscheck = $this->sheel->db->query("
                        SELECT exemptid, user_id, accessname, value, exemptfrom, exemptto, comments, invoiceid, active
                        FROM " . DB_PREFIX . "subscription_user_exempt
                        WHERE exemptto <= '" . DATETODAY . " " . TIMENOW . "'
				AND active = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($exemptionscheck) > 0)
                {
                        while ($exemptions = $this->sheel->db->fetch_array($exemptionscheck, DB_ASSOC))
                        {
                                // expire subscription exemptions
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user_exempt
                                        SET active = '0'
                                        WHERE exemptid = '" . $exemptions['exemptid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                $expiredexemptions++;
                                // send email to notify about subscription permission exemption expiry and renewal details
                                // >> this will be added at a later date <<
                        }
			if ($expiredexemptions > 0)
			{
				$cronlog .= "subscription_expiry->user_subscription_exemptions() [$expiredexemptions], ";
			}
                }
                return $cronlog;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Sun, Jun 16th, 2019
|| ####################################################################
\*======================================================================*/
?>
