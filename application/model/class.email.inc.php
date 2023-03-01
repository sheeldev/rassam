<?php
/**
 * Email data manager class to handle the majority of sending emails in sheel.
 *
 * @package      sheel\Email
 * @version      1.0.0.0
 * @author       sheel
 */
class email
{
	protected $sheel;
	var $mail = null;
	var $from = null;
	var $fromname = '';
	var $slng = null;
	var $subject = null;
	var $subject_a = null;
	var $message = null;
	var $message_a = null;
	var $messagehtml = null;
	var $messagehtml_a = null;
	var $emailid = '';
	var $logtype = 'alert';
	var $type = null;
	var $bcc = null;
	public $varname = '';
	public $sent = false;
	public $toqueue = true;
	public $user_id = 0;

	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->toqueue = ($this->sheel->config['emailssettings_queueenabled'] == '1') ? true : false;
	}
	function get($varname = '')
	{
		if (!empty($varname)) {
			if (empty($this->slng)) {
				$this->slng = 'eng';
			}
			$sql = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, subject_" . $this->slng . " AS subject, message_" . $this->slng . " AS message, messagehtml_" . $this->slng . " AS messagehtml, type, bcc
				FROM " . DB_PREFIX . "email
				WHERE varname = '" . $this->sheel->db->escape_string($varname) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$this->varname = $varname;
				$this->emailid = md5(trim($varname));
				$this->subject = stripslashes(trim($res['subject']));
				$this->message = stripslashes(trim($res['message']));
				$this->messagehtml = stripslashes(trim($res['messagehtml']));
				$this->bcc = stripslashes(trim($res['bcc']));
				$this->type = $res['type'];
				unset($res);
			}
		}
	}
	function set($toconvert = array())
	{
		if (!empty($this->mail)) {
			$emails = array();
			if (!empty($this->bcc)) {
				$bcctmp = explode(',', $this->bcc);
				foreach ($bcctmp as $bccemail) {
					if (!empty($bccemail)) {
						$emails[] = trim($bccemail);
					}
				}
				if (is_array($emails)) {
					$emails[] = $this->mail;
				}
				$this->mail = $emails;
			}
			unset($emails);
			if (is_array($this->mail)) { // multiple receipents
				foreach ($this->mail as $email) {
					if ($this->is_valid_email($email)) {
						$commonfields = array(
							'{{site_name}}' => SITE_NAME,
							'{{site_email}}' => SITE_EMAIL,
							'{{site_phone}}' => SITE_PHONE,
							'{{site_address}}' => SITE_ADDRESS,
							'{{http_server_admin}}' => HTTP_SERVER_ADMIN,
							'{{https_server_admin}}' => HTTPS_SERVER_ADMIN,
							'{{https_server}}' => HTTPS_SERVER,
							'{{http_server}}' => HTTP_SERVER,
							'{{generate_date}}' => $this->sheel->common->print_date(DATETIME24H, $this->sheel->config['globalserverlocale_globaltimeformat'], 0, 0),
							'{{email_id}}' => $this->emailid,
							'{{email_notifications}}' => HTTPS_SERVER . 'preferences/email/',
							'{{email_unsubscribe}}' => HTTPS_SERVER . 'preferences/email/?do=unsubscribe&id=' . urlencode($this->emailid) . '&e=' . urlencode(base64_encode($email)),
							'{{email_unsubscribeall}}' => HTTPS_SERVER . 'preferences/email/?do=unsubscribeall',
							'{{textdirection}}' => $this->sheel->config['template_textdirection'],
							'{{textalignment}}' => $this->sheel->config['template_textalignment'],
							'{{twitter}}' => $this->sheel->config['globalserversettings_twitterurl'],
							'{{facebook}}' => $this->sheel->config['globalserversettings_facebookurl'],
							'{{linkedin}}' => $this->sheel->config['globalserversettings_linkedin'],
							'{{youtube}}' => $this->sheel->config['globalserversettings_youtubeurl'],
							'{{instagram}}' => $this->sheel->config['globalserversettings_instaurl'],
							'{{twitter}}' => $this->sheel->config['globalserversettings_twitterurl'],
							'{{facebook}}' => $this->sheel->config['globalserversettings_facebookurl'],
							'{{linkedin}}' => $this->sheel->config['globalserversettings_linkedin'],
							'{{youtube}}' => $this->sheel->config['globalserversettings_youtubeurl'],
							'{{instagram}}' => $this->sheel->config['globalserversettings_instaurl'],
							'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/'
						);

						if (isset($toconvert) and is_array($toconvert)) {
							$this->subject_a[$email] = $this->subject;
							$this->message_a[$email] = $this->message;
							$this->messagehtml_a[$email] = $this->messagehtml;
							foreach ($toconvert as $search => $replace) {
								if (!empty($search)) {
									$this->subject_a[$email] = str_replace("$search", $replace, $this->subject_a[$email]);
									$this->message_a[$email] = str_replace("$search", $replace, $this->message_a[$email]);
									$this->messagehtml_a[$email] = str_replace("$search", $replace, $this->messagehtml_a[$email]);
								}
							}
							$this->message_a[$email] = strip_tags($this->message_a[$email]); // strip html tags from plain-text version email
						}

						foreach ($commonfields as $search => $replace) {
							if (!empty($search)) {
								$this->subject_a[$email] = str_replace("$search", $replace, $this->subject_a[$email]);
								$this->message_a[$email] = str_replace("$search", $replace, $this->message_a[$email]);
								$this->messagehtml_a[$email] = str_replace("$search", $replace, $this->messagehtml_a[$email]);
							}
						}
						$this->sheel->template->templateregistry['message_a'] = $this->message_a[$email];
						$this->sheel->template->templateregistry['messagehtml_a'] = $this->messagehtml_a[$email];
						$this->message_a[$email] = $this->sheel->template->parse_template_phrases('message_a');
						$this->messagehtml_a[$email] = $this->sheel->template->parse_template_phrases('messagehtml_a');
					}
				}
			} else { // single receipent
				if ($this->is_valid_email($this->mail)) {
					$commonfields = array(
						'{{site_name}}' => SITE_NAME,
						'{{site_email}}' => SITE_EMAIL,
						'{{site_phone}}' => SITE_PHONE,
						'{{site_address}}' => SITE_ADDRESS,
						'{{http_server_admin}}' => HTTP_SERVER_ADMIN,
						'{{https_server_admin}}' => HTTPS_SERVER_ADMIN,
						'{{https_server}}' => HTTPS_SERVER,
						'{{http_server}}' => HTTP_SERVER,
						'{{generate_date}}' => $this->sheel->common->print_date(DATETIME24H, $this->sheel->config['globalserverlocale_globaltimeformat'], 0, 0),
						'{{email_id}}' => $this->emailid,
						'{{email_notifications}}' => HTTPS_SERVER . 'preferences/email/',
						'{{email_unsubscribe}}' => HTTPS_SERVER . 'preferences/email/?do=unsubscribe&id=' . urlencode($this->emailid) . '&e=' . urlencode(base64_encode($this->mail)),
						'{{email_unsubscribeall}}' => HTTPS_SERVER . 'preferences/email/?do=unsubscribeall',
						'{{textdirection}}' => $this->sheel->config['template_textdirection'],
						'{{textalignment}}' => $this->sheel->config['template_textalignment'],
						'{{twitter}}' => $this->sheel->config['globalserversettings_twitterurl'],
						'{{facebook}}' => $this->sheel->config['globalserversettings_facebookurl'],
						'{{linkedin}}' => $this->sheel->config['globalserversettings_linkedin'],
						'{{youtube}}' => $this->sheel->config['globalserversettings_youtubeurl'],
						'{{instagram}}' => $this->sheel->config['globalserversettings_instaurl'],
						'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/'
					);
					if (isset($toconvert) and is_array($toconvert)) {
						foreach ($toconvert as $search => $replace) {
							if (!empty($search)) {
								$this->subject = str_replace("$search", $replace, $this->subject);
								$this->message = str_replace("$search", $replace, $this->message);
								$this->messagehtml = str_replace("$search", $replace, $this->messagehtml);
							}
						}
						$this->message = strip_tags($this->message); // strip html tags from plain-text version email
					}

					foreach ($commonfields as $search => $replace) {
						if (!empty($search)) {
							$this->subject = str_replace("$search", $replace, $this->subject);
							$this->message = str_replace("$search", $replace, $this->message);
							$this->messagehtml = str_replace("$search", $replace, $this->messagehtml);
						}
					}
					$this->sheel->template->templateregistry['message'] = $this->message;
					$this->sheel->template->templateregistry['messagehtml'] = $this->messagehtml;
					$this->message = $this->sheel->template->parse_template_phrases('message');
					$this->messagehtml = $this->sheel->template->parse_template_phrases('messagehtml');
				}
			}
		}
	}
	function send() // -> send_email() -> add_to_log() || add_to_queue()

	{
		if (empty($this->from)) {
			$this->from = SITE_EMAIL;
		}
		if (empty($this->fromname)) {
			$this->fromname = SITE_NAME;
		}
		if (!empty($this->mail)) {
			if (!$this->toqueue) {

				if (is_array($this->mail)) { // handle sending the same email template to multiple receipents
					foreach ($this->mail as $email) {
						if ($this->is_valid_email($email) and $this->is_notification_unsubscribed($email, $this->varname) == false and isset($this->subject_a[$email]) and isset($this->message_a[$email])) {
							$this->send_email($email, $this->subject_a[$email], $this->message_a[$email], $this->messagehtml_a[$email], $this->from, $this->fromname, $this->logtype, $this->slng);
						}
					}
				} else {
					if ($this->is_valid_email($this->mail) and $this->is_notification_unsubscribed($this->mail, $this->varname) == false) {
						$this->send_email($this->mail, $this->subject, $this->message, $this->messagehtml, $this->from, $this->fromname, $this->logtype, $this->slng);
					}
				}
			} else {
				$this->add_to_queue();
			}
		}
		$this->mail = $this->subject = $this->subject_a = $this->message = $this->message_a = $this->messagehtml = $this->messagehtml_a = $this->from = $this->fromname = $this->emailid = $this->varname = $this->type = $this->bcc = null;
		$this->logtype = 'alert';
	}
	function add_to_queue()
	{
		if (is_array($this->mail)) { // multiple recipients
			foreach ($this->mail as $email) {
				if ($this->is_valid_email($email) and $this->is_notification_unsubscribed($email, $this->varname) == false and isset($this->subject_a[$email]) and isset($this->message_a[$email])) {
					$this->sheel->db->query("
						INSERT INTO " . DB_PREFIX . "email_queue
						(id, mail, fromemail, fromname, subject, message, messagehtml, date_added, varname, type)
						VALUES(
						NULL,
						'" . $this->sheel->db->escape_string($email) . "',
						'" . $this->sheel->db->escape_string($this->from) . "',
						'" . $this->sheel->db->escape_string($this->fromname) . "',
						'" . $this->sheel->db->escape_string($this->subject_a[$email]) . "',
						'" . $this->sheel->db->escape_string($this->message_a[$email]) . "',
						'" . $this->sheel->db->escape_string($this->messagehtml_a[$email]) . "',
						'" . $this->sheel->db->escape_string(TIMESTAMPNOW) . "',
						'" . $this->sheel->db->escape_string($this->varname) . "',
						'" . $this->sheel->db->escape_string($this->type) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
		} else { // single recipient
			$this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "email_queue
				(id, mail, fromemail, fromname, subject, message, messagehtml, date_added, varname, type, bcc)
				VALUES(
				NULL,
				'" . $this->sheel->db->escape_string($this->mail) . "',
				'" . $this->sheel->db->escape_string($this->from) . "',
				'" . $this->sheel->db->escape_string($this->fromname) . "',
				'" . $this->sheel->db->escape_string($this->subject) . "',
				'" . $this->sheel->db->escape_string($this->message) . "',
				'" . $this->sheel->db->escape_string($this->messagehtml) . "',
				'" . $this->sheel->db->escape_string(TIMESTAMPNOW) . "',
				'" . $this->sheel->db->escape_string($this->varname) . "',
				'" . $this->sheel->db->escape_string($this->type) . "',
				'" . $this->sheel->db->escape_string($this->bcc) . "')
			", 0, null, __FILE__, __LINE__);
		}
	}
	function add_to_log()
	{
		$mail = !empty($this->mail) ? $this->mail : '';
		$logtype = !empty($this->logtype) ? $this->logtype : '';
		$user_id = !empty($this->user_id) ? $this->user_id : '';
		$subject = !empty($this->subject) ? strip_tags($this->subject) : '';
		$message = !empty($this->message) ? strip_tags($this->message) : '';
		$messagehtml = !empty($this->messagehtml) ? $this->messagehtml : '';
		$varname = !empty($this->varname) ? $this->varname : '';
		$type = !empty($this->type) ? $this->type : 'global';
		$bcc = !empty($this->bcc) ? $this->bcc : '';
		$sent = ($this->sent) ? 'yes' : 'no';
		$isadmin = (($logtype == 'dberror') ? '1' : '0');
		if (is_array($mail)) {
			foreach ($mail as $email) {
				$this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "emaillog
					(emaillogid, logtype, user_id, email, subject, body, bodyhtml, date, varname, type, bcc, sent, isadmin)
					VALUES(
					NULL,
					'" . $this->sheel->db->escape_string($logtype) . "',
					'" . $this->sheel->db->escape_string($user_id) . "',
					'" . $this->sheel->db->escape_string($email) . "',
					'" . $this->sheel->db->escape_string($this->subject_a[$email]) . "',
					'" . $this->sheel->db->escape_string($this->message_a[$email]) . "',
					'" . $this->sheel->db->escape_string($this->messagehtml_a[$email]) . "',
					'" . DATETIME24H . "',
					'" . $this->sheel->db->escape_string($varname) . "',
					'" . $this->sheel->db->escape_string($type) . "',
					'" . $this->sheel->db->escape_string($bcc) . "',
					'" . $sent . "',
					'" . $isadmin . "')
				", 0, null, __FILE__, __LINE__);
			}
		} else {
			$this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "emaillog
				(emaillogid, logtype, user_id, email, subject, body, bodyhtml, date, varname, type, bcc, sent, isadmin)
				VALUES(
				NULL,
				'" . $this->sheel->db->escape_string($logtype) . "',
				'" . $this->sheel->db->escape_string($user_id) . "',
				'" . $this->sheel->db->escape_string($mail) . "',
				'" . $this->sheel->db->escape_string($subject) . "',
				'" . $this->sheel->db->escape_string($message) . "',
				'" . $this->sheel->db->escape_string($messagehtml) . "',
				'" . DATETIME24H . "',
				'" . $this->sheel->db->escape_string($varname) . "',
				'" . $this->sheel->db->escape_string($type) . "',
				'" . $this->sheel->db->escape_string($bcc) . "',
				'" . $sent . "',
				'" . $isadmin . "')
			", 0, null, __FILE__, __LINE__);
		}
	}
	/**
	 * Function to dispatch new email using php's mb_send_mail() or pre-configured SMTP
	 *
	 * @param       string         to email address
	 * @param       string         email subject
	 * @param       string         email message (plain-text version) (required)
	 * @param       string         email message (HTML version) (not required, if not set, will send in plain-text mode only)
	 * @param       string         email from address
	 * @param       string         email from name
	 * @param       string         log type (default alert)
	 * @param       string         short form language identifier (default eng)
	 * @param       string         blind carbon copy list ["Peter" <peter@sheel.com>, "Erin" <erin@sheel.com>] or [peter@sheel.com, erin@sheel.com]
	 *
	 */
	function send_email($toemail = '', $subject = '', $message = '', $messagehtml = '', $from = '', $fromname = '', $logtype = 'alert', $slng = '')
	{
		if (empty($toemail) or empty($subject) or empty($message)) {
			return false;
		}
		$pathto_sendmail = @ini_get('sendmail_path');
		$encoding = 'utf-8';
		$delimiter = "\r\n";
		$delimiter2 = "\n";
		$this->user_id = 0;
		if (!empty($slng)) {
			$locale = $this->sheel->fetch_language_locale(0, $slng);
			setlocale(LC_TIME, $locale['locale']);
			unset($locale);
		}

		$toemail = $this->sheel->common->un_htmlspecialchars(trim($toemail));
		$from = ((empty($from)) ? SITE_EMAIL : $from);
		$fromname = ((empty($fromname)) ? SITE_NAME : $fromname);
		$subject = trim($subject);
		$subject = strip_tags($subject);
		$subject = $this->sheel->template->parse_hash('emailtemplate', array('ilpage' => $this->sheel->ilpage), 1, $subject);
		$subject = $this->sheel->template->parse_template_phrases('emailtemplate');
		$subject = $this->sheel->bbcode->strip_bb_tags($subject);
		$subject = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&euro;', '&pound;'), array("<", ">", '&', '\'', '"', '<', '>', '€', '£'), htmlspecialchars_decode($subject, ENT_NOQUOTES));
		$message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
		$message = $this->sheel->template->parse_hash('emailtemplate', array('ilpage' => $this->sheel->ilpage), 1, $message);
		$message = $this->sheel->template->parse_template_phrases('emailtemplate');
		$message = $this->sheel->bbcode->strip_bb_tags($message);
		$message = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&euro;', '&pound;'), array("<", ">", '&', '\'', '"', '<', '>', '€', '£'), htmlspecialchars_decode($message, ENT_NOQUOTES));
		$boundry = md5($message . microtime());

		$headers = 'From: "' . $fromname . '" <' . $from . '>' . $delimiter .
			"Return-Path: " . $from . $delimiter .
			'Message-ID: <' . date('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . HTTPS_SERVER . '>' . $delimiter .
			"X-Priority: 3" . $delimiter .
			"X-Mailer: sheel " . $this->sheel->config['ilversion'] . "." . $this->sheel->config['ilbuild'] . $delimiter .
			"MIME-Version: 1.0" . $delimiter .
			"X-Sender-IP: $_SERVER[SERVER_ADDR]" . $delimiter .
			'Date: ' . date('r') . $delimiter . ((!empty($messagehtml))
				? 'Content-Type: multipart/alternative;boundary=' . $boundry . $delimiter
				: "Content-Type: text/plain;charset=$encoding" . $delimiter) .
			"Content-Transfer-Encoding: 7bit" . $delimiter2;

		$message = ((!empty($messagehtml)) ? 
			'This is a multi-part message in MIME format.' . $delimiter2 . $delimiter2 .
			'--' . $boundry . $delimiter .
			'Content-type: text/plain;charset=' . $encoding . $delimiter2 .
			'Content-Transfer-Encoding: 7bit' . $delimiter2 . $delimiter2 . $message . $delimiter2 . $delimiter2 .
			'--' . $boundry . $delimiter .
			'Content-type: text/html;charset=' . $encoding . $delimiter2 .
			'Content-Transfer-Encoding: 7bit' . $delimiter2 . $delimiter2 . $messagehtml . $delimiter2 . $delimiter2 .
			'--' . $boundry . '--'
			: $message);
		if ($this->sheel->config['globalserversmtp_enabled'] and !empty($this->sheel->config['globalserversmtp_host']) and !empty($this->sheel->config['globalserversmtp_port'])) { // smtp
			@ini_set('SMTP', $this->sheel->config['globalserversmtp_host']);
			@ini_set('smtp_port', $this->sheel->config['globalserversmtp_port']);
			$this->sheel->smtp->toemail = $toemail;
			$this->sheel->smtp->fromemail = $from;
			$this->sheel->smtp->headers = $headers;
			$this->sheel->smtp->subject = $subject;
			$this->sheel->smtp->message = $message;
			$this->sent = $this->sheel->smtp->send();
			if ($this->sent) {
				$sql = $this->sheel->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $this->sheel->db->escape_string($toemail) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($sql) > 0) {
					$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
					$this->user_id = $res['user_id'];
					unset($res);
				}
			}
			else {
			}
		} else { // regular php mail
			@ini_set('sendmail_from', $from);
			if (mb_send_mail($toemail, $subject, $message, $headers)) {
				$this->sent = true;
				$sql = $this->sheel->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $this->sheel->db->escape_string($toemail) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($sql) > 0) {
					$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
					$this->user_id = $res['user_id'];
					unset($res);
				}
			}
		}
		$this->add_to_log();
	}
	/**
	 * Function to unsubscribe a user from receiving a particular email template on the site
	 *
	 * @param       string         email address
	 * @param       string         email template varname (md5 only)
	 *
	 * @return      bool           Returns true on successful email unsubscribe
	 */
	function unsubscribe_notification($email = '', $emailvarname = '')
	{
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$sql = $this->sheel->db->query("
			SELECT name_" . $this->sheel->db->escape_string($slng) . " AS name, varname
			FROM " . DB_PREFIX . "email
			WHERE MD5(varname) = '" . $this->sheel->db->escape_string($emailvarname) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$sql2 = $this->sheel->db->query("
				SELECT id
				FROM " . DB_PREFIX . "email_optout
				WHERE MD5(varname) = '" . $this->sheel->db->escape_string($emailvarname) . "'
					AND email = '" . $this->sheel->db->escape_string($email) . "'
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql2) == 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "email_optout
					(id, email, varname)
					VALUES (
					NULL,
					'" . $this->sheel->db->escape_string($email) . "',
					'" . $this->sheel->db->escape_string($res['varname']) . "')
				", 0, null, __FILE__, __LINE__);
				return true;
			}
		}
		return false;
	}
	/**
	 * Function to determine if a specific user email address is unsubscribed to a particular notification
	 *
	 * @param       string         email address
	 * @param       string         email varname (non-encrypted version)
	 *
	 * @return      bool           Returns true if an email is unsubscribed from a specific notification otherwise returns false
	 */
	function is_notification_unsubscribed($email = '', $varname = '')
	{
		$sql = $this->sheel->db->query("
			SELECT id
			FROM " . DB_PREFIX . "email_optout
			WHERE varname = '" . $this->sheel->db->escape_string($varname) . "'
				AND email = '" . $this->sheel->db->escape_string($email) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			return true;
		}
		return false;
	}
	/**
	 * Function to determine if a supplied email address is valid based on it's apperence
	 *
	 * @param       string         email address
	 *
	 * @return      string         Returns true or false if email address is valid
	 */
	function is_valid_email($email = '')
	{
		return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s\'"<>]+\.+[a-z]{2,}))$#si', $email);
	}
	function dispatch_in_queue()
	{ // run from cron.emailqueue.php
		$cronlog = '';
		if (isset($this->sheel->config['emailssettings_queueenabled']) and $this->sheel->config['emailssettings_queueenabled']) {
			$s = $ns = 0;
			$sql = $this->sheel->db->query("
				SELECT id, mail, fromemail, fromname, subject, message, messagehtml, date_added, varname, type, bcc
				FROM " . DB_PREFIX . "email_queue
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$this->toqueue = false;
				while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
					$this->mail = $res['mail'];
					$this->from = $res['fromemail'];
					$this->fromname = $res['fromname'];
					$this->subject = $res['subject'];
					$this->message = $res['message'];
					$this->messagehtml = $res['messagehtml'];
					$this->varname = $res['varname'];
					$this->type = $res['type'];
					$this->bcc = $res['bcc'];
					$this->send();
					$this->sheel->db->query("
		                                DELETE FROM " . DB_PREFIX . "email_queue
		                                WHERE id = '" . intval($res['id']) . "'
		                        ", 0, null, __FILE__, __LINE__);
					if ($this->sent) {
						$s++;
					} else {
						$ns++;
					}
				}
			}
			if ($s > 0) {
				$cronlog = 'email->dispatch_in_queue() [' . number_format($s) . ' sent], ';
			}

		}
		return $cronlog;
	}
}
?>