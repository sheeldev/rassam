<?php
/**
 * SMTP class to perform the simple mail transfer protocol operations within sheel.
 *
 * @package      sheel\SMTP
 * @version      1.0.0.0
 * @author       sheel
 */
class smtp
{
	protected $sheel;
	/**
	 * SMTP hostname
	 */
	var $host;
	/**
	 * SMTP port number
	 */
	var $port;
	/**
	 * SMTP user name
	 */
	var $username;
	/**
	 * SMTP password
	 */
	var $password;
	/**
	 * Debug mode & logging
	 */
	var $debug = false;
	var $file_log = SMTPLOG;
	var $log = array();
	/**
	 * SMTP socket resource
	 */
	var $connection = null;
	var $return_code = 0;
	var $return = '';
	var $authtype = 'LOGIN';
	/**
	 * Constructor
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->host = trim($this->sheel->config['globalserversmtp_host']);
		$this->port = (!empty($this->sheel->config['globalserversmtp_port']) ? intval($this->sheel->config['globalserversmtp_port']) : 25);
		$this->username = trim($this->sheel->config['globalserversmtp_user']);
		$this->password = trim($this->sheel->config['globalserversmtp_pass']);
		$this->debug = $this->sheel->config['smtpdebug'];
		$this->authtype = $this->sheel->config['smtpauthtype'];
		$this->delimiter = "\r\n";
	}
	/**
	 * Function to send the message
	 */
	function sendMessage($msg, $expectedResult = false)
	{
		if ($msg !== false and !empty($msg)) {
			fputs($this->connection, $msg . $this->delimiter);
		}
		if ($expectedResult !== false) {
			$result = '';
			while ($line = fgets($this->connection, 1024)) {
				$result .= $line;
				if (preg_match('#^(\d{3}) #', $line, $matches)) {
					break;
				}
			}
			$this->return_code = ((isset($matches[1]) and !empty($matches[1])) ? intval($matches[1]) : ''); // 334
			$this->return = ((isset($result) and !empty($result)) ? $result : ''); // 503 STARTTLS command used when not advertised
			$this->set_debug('(sent: ' . $msg . ') (expected: ' . $expectedResult . ') (received code: ' . $this->return_code . ') (received: ' . rtrim($this->return) . ')');
			return ($this->return_code == $expectedResult);
		}
		return true;
	}
	/**
	 * Sets a user defined error message to be printed to the browser
	 */
	function errorMessage($msg)
	{
		$this->set_error($msg);
		$this->close_log();
		return false;
	}
	/**
	 * Function to dispatch the email
	 */
	function send()
	{
		if (!$this->toemail) {
			return false;
		}

		$this->open_log();

		$this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if (function_exists('stream_set_timeout') and DIRECTORY_SEPARATOR != '\\') {
			@stream_set_timeout($this->connection, 30, 0);
		}
		if (is_resource($this->connection)) {
			if ($this->debug) {
				$this->set_debug('Successfully connected to SMTP server (' . $this->host . ' port: ' . $this->port . ')');
			}
			if (!$this->sendMessage(false, 220)) {
				return $this->errorMessage('Unexpected response when connecting to SMTP server');
			}
			if ($this->sheel->config['globalserversmtp_usetls'] or (!empty($this->username) and !empty($this->password))) {
				$helo = 'EHLO';
			} else {
				$helo = 'HELO';
			}
			if (!$this->sendMessage($helo . ' ' . $this->host, 250)) {
				return $this->errorMessage('Unexpected response from SMTP server during initial ' . $helo . ' handshake');
			}
			if ($this->sheel->config['globalserversmtp_usetls'] and preg_match("#250( |-)STARTTLS#mi", $this->return)) {
				if (!$this->sendMessage('STARTTLS', 220)) {
					return $this->errorMessage('The server did not understand the STARTTLS command.');
				}
				if (!@stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
					return $this->errorMessage('Failed to start TLS encryption');
				}
				// resend EHLO to fetch updated service list
				if (!$this->sendMessage($helo . ' ' . $this->host, 250)) {
					return $this->errorMessage('Unexpected response from SMTP server during secondary EHLO handshake (after TLS)');
				}
			}
			if (!empty($this->username) and !empty($this->password)) {
				if (!preg_match("#250( |-)AUTH( |=)(.+)$#mi", $this->return, $matches)) {
					return $this->errorMessage('The SMTP server did not understand the AUTH command');
				}
				$auth_methods = explode(" ", trim($matches[3])); // 250-AUTH PLAIN LOGIN becomes PLAIN LOGIN
				if (!in_array($this->authtype, $auth_methods)) {
					return $this->errorMessage('The SMTP server does not support the current AUTH method (AUTH ' . $this->authtype . ')');
				}
				if (!$this->sendMessage('AUTH ' . $this->authtype, 334)) // AUTH 'LOGIN', 'CRAM-MD5', 'PLAIN'
				{
					return $this->errorMessage('Unexpected response from SMTP server during AUTH ' . $this->authtype);
				} else {
					if ($this->authtype == 'LOGIN') {
						if (!$this->sendMessage(base64_encode($this->username), 334)) {
							return $this->errorMessage('SMTP server rejected the username via (AUTH ' . $this->authtype . ')');
						}
						if (!$this->sendMessage(base64_encode($this->password), 235)) {
							return $this->errorMessage('SMTP server rejected the password via (AUTH ' . $this->authtype . ')');
						}
					} else if ($this->authtype == 'PLAIN') {
						if (!$this->sendMessage(base64_encode(chr(0) . $this->username . chr(0) . $this->password), 235)) {
							return $this->errorMessage('SMTP server rejected the username or password via (AUTH ' . $this->authtype . ')');
						}
					} else if ($this->authtype == 'CRAM-MD5') {
						$challenge = base64_decode(substr($this->return, 4));
						if (!$this->sendMessage(base64_encode($this->username . ' ' . $this->cram_md5_response($this->password, $challenge)), 235)) {
							return $this->errorMessage('SMTP server rejected the username, password or challenge response via (AUTH ' . $this->authtype . ')');
						}
					} else {
						return $this->errorMessage('The SMTP server does not support the current AUTH method (AUTH ' . $this->authtype . ')');
					}
				}
			}
			if (!$this->sendMessage('MAIL FROM: <' . $this->fromemail . '>', 250)) {
				return $this->errorMessage('The SMTP server does not understand the MAIL FROM command');
			}
			$addresses = explode(',', $this->toemail);
			foreach ($addresses as $address) {
				if (!$this->sendMessage('RCPT TO: <' . trim($address) . '>', 250)) {
					return $this->errorMessage('The mail server does not understand the RCPT TO command');
				}
			}
			if ($this->sendMessage('DATA', 354)) {
				$this->sendMessage('To: ' . $this->toemail, false);
				$this->sendMessage(trim($this->headers), false);
				$this->sendMessage('Subject: ' . $this->subject, false);
				$this->sendMessage($this->message, false);
			} else {
				return $this->errorMessage('The SMTP server did not understand the DATA command');
			}
			$this->sendMessage('.', 250);
			$this->sendMessage('QUIT', 221);
			fclose($this->connection);

			$this->set_log('Successfully sent email via SMTP');
			$this->close_log();
			return true;
		}
		return $this->errorMessage('Unable to connect to the SMTP server with the given details');
	}
	/**
	 * Generate a CRAM-MD5 response from a server challenge.
	 *
	 * @param string $password Password.
	 * @param string $challenge Challenge sent from SMTP server.
	 *
	 * @return string CRAM-MD5 response.
	 */
	function cram_md5_response($password, $challenge)
	{
		if (strlen($password) > 64) {
			$password = pack('H32', md5($password));
		}
		if (strlen($password) < 64) {
			$password = str_pad($password, 64, chr(0));
		}
		$k_ipad = substr($password, 0, 64) ^ str_repeat(chr(0x36), 64);
		$k_opad = substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64);
		$inner = pack('H32', md5($k_ipad . $challenge));
		return md5($k_opad . $inner);
	}
	/*
	 * Function to set an apache style payment log record from any error array details supplied
	 *
	 * @param       string         error text message
	 *
	 * @return      nothing
	 */
	function set_error($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] [error] ' . $text;
		$this->save_log();
	}
	/*
	 * Function to set an apache style debug log record from any error array details supplied
	 *
	 * @param       string         error text message
	 *
	 * @return      nothing
	 */
	function set_debug($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] [debug] ' . $text;
		$this->save_log();
	}
	function open_log()
	{
		$this->log[] = "----------------------------------------------------------------------------------------------------
=> Log opened from sheel SMTP (smtpiL) at " . vdate('D M d Y H:i:s O', time());
		$this->save_log();
	}
	function close_log()
	{
		$this->log[] = "=> Log closed " . vdate('D M d Y H:i:s O', time());
		$this->save_log();
	}
	/*
	 * Function to set an apache style log record from text being sent from the upgrader
	 *
	 * @param       string         text message to log
	 *
	 * @return      nothing
	 */
	function set_log($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] ' . $text;
		$this->save_log();
	}
	/*
	 * Function to save the log response to the log file
	 *
	 * @return      nothing
	 */
	function save_log()
	{
		if ($this->debug) {
			if (!empty($this->file_log) and file_exists($this->file_log)) {
				if (isset($this->log) and count($this->log) > 0) {
					if ($fp = fopen($this->file_log, 'a')) {
						foreach ($this->log as $line) {
							fwrite($fp, $line . LINEBREAK);
						}
						fclose($fp);
					}
				}
			}
		}
		$this->log = array();
	}
	function clean_logs()
	{ // runs monthly
		if (!empty($this->file_log) and file_exists($this->file_log)) {
			$fp = fopen($this->file_log, 'w');
			fclose($fp);
		}
		return 'smtp->clean_logs(), ';
	}
}
?>