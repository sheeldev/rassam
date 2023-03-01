<?php
define('DB_ASSOC', 1);
define('DB_NUM', 2);
define('DB_BOTH', 3);

/**
 * sheel database class to perform the majority of database caching in sheel
 *
 * @package      sheel\Database
 * @version      1.0.0.0
 * @author       sheel
 */
class sheel_database
{
	protected $sheel;
	/**
	 * Debug Mode
	 *
	 * @var	    $debug
	 */
	var $debug = false;
	/**
	 * Email Error Reporting
	 *
	 * @var	    $email_reporting
	 */
	var $error_reporting = true;
	var $email_reporting = false;
	/**
	 * Timer Variables
	 *
	 * @var	    $start
	 * @var      $end
	 * @var      $totaltime
	 * @var      $formatted
	 */
	var $start = null;
	var $end = null;
	var $totaltime = null;
	var $formatted = null;
	var $name = null;
	/**
	 * Database Connection Parameters
	 *
	 * @var	    $multiserver
	 * @var      $database
	 * @var      $explain
	 * @var      $querylist
	 * @var      $query_count
	 * @var      $connection_write
	 * @var      $connection_read
	 * @var      $connection_link
	 */
	var $multiserver = false;
	var $database = null;
	var $explain = null;
	var $querylist = array();
	var $query_count = 0;
	var $connection_write = null;
	var $connection_read = null;
	var $connection_link = null;
	var $error = '';
	var $errno = '';
	var $ttquery = 0;

	private $selectables = array();
	private $table;
	private $whereclause;
	private $limit;

	private $functions = array();
	/**
	 * Constructor
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		if (isset($this->functions) and function_exists($this->functions['real_escape_string'])) {
			$this->functions['escape_string'] = $this->functions['real_escape_string'];
		}
	}
	/**
	 * Initialize database connection
	 *
	 * Connects to a database server
	 *
	 * @return	boolean
	 */
	function connect()
	{
		$this->connection_write = $this->db_connect();
		$this->multiserver = false;
		$this->connection_read = & $this->connection_write;
		$this->database = DB_DATABASE;
		if ($this->connection_write) {
			$this->select_db($this->database);
		}
		return true;
	}
	/**
	 * Selects a database for usage
	 *
	 * @param	string	 name of the database to use
	 *
	 * @return	boolean
	 */
	function select_db($database = '')
	{
		$this->database = $database;
		if ($check_write = @$this->select_db_wrapper($this->database, $this->connection_write)) {
			$this->connection_link = & $this->connection_write;
			return true;
		} else {
			$this->connection_link = & $this->connection_write;
			$this->dberror('Cannot select database ' . ((empty($this->database)) ? '[no database set in config.php]' : $this->database) . ' for usage');
			return false;
		}
	}
	/**
	 * Function to perform database error handling
	 *
	 */
	function dberror($string = '')
	{
		if (!defined('SQLVERSION')) {
			define('SQLVERSION', 'Unknown');
		}
		if (!defined('MYSQL_VERSION')) {
			define('MYSQL_VERSION', 'Unknown');
		}
		if (!defined('SITE_CONTACT') and defined('SITE_EMAIL')) {
			define('SITE_CONTACT', SITE_EMAIL);
		}
		if ($this->error_reporting) {
			$_SERVER['HTTP_HOST'] = ((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '');
			$_SERVER['REQUEST_URI'] = ((isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '');
			$message = $messageemail = '';

			$messageemail = 'MySQL error      : ' . $this->error() . 'Error number     : ' . $this->errno();
			$subject = 'Database error on ' . vdate('M d, Y, H:i:s');
			if ($this->email_reporting and defined('SITE_EMAIL')) {
				$this->sheel->email->toqueue = false;
				$this->sheel->email->logtype = 'dberror';
				$this->sheel->email->mail = SITE_CONTACT;
				$this->sheel->email->from = SITE_NAME . ' MySQL ReportBot';
				$this->sheel->email->subject = $subject;
				$this->sheel->email->message = $messageemail;
				$this->sheel->email->send();
			}
			if (defined('SITE_EMAIL')) {
				// record bad database query
				$this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "error_log
					(`log_id`, `error_id`, `name`, `info`, `value`)
					VALUES (
					NULL,
					'0',
					'" . $this->sheel->db->escape_string($subject) . "',
					'" . $this->sheel->db->escape_string(trim($messageemail)) . "',
					'0')
				");
			}
		}
			echo ('MySQL error      : ' . stripslashes($this->sheel->db->escape_string(trim($messageemail))));
			exit();
	}
	/**
	 * Function to determine if a field within a table exists
	 *
	 * @param       string       field name
	 * @param       string       table name
	 *
	 * @return      boolean      Returns false on no field existing, true on field existing
	 */
	function field_exists($field = '', $table = '')
	{
		$exists = false;
		$columns = $this->query("SHOW COLUMNS FROM $table");
		while ($col = $this->fetch_assoc($columns)) {
			if ($col['Field'] == $field) {
				$exists = true;
				break;
			}
		}
		return $exists;
	}
	/**
	 * Function to determine if a database table exists based on the currently selected database
	 *
	 * @param       string       table name
	 *
	 * @return      boolean      Returns false on no table existing, true on table existing
	 */
	function table_exists($table = '')
	{
		$res = $this->query("SHOW TABLES LIKE '$table'");
		if ($this->num_rows($res) > 0) {
			return true;
		}
		return false;
	}
	/**
	 * Function to determine if a field within a table exists, and if not, to automatically add the necessary field column details
	 *
	 * @param       string       database table name
	 * @param       string       table field name to add (if does not already exist)
	 * @param       string       table field attributes to process (ie: VARCHAR(250) NOT NULL)
	 * @param       string       table field name that we'll add our new field name after (ie: AFTER `title`)
	 *
	 * @return      boolean      Returns valid sql string if added, blank string if already exists
	 */
	function add_field_if_not_exist($table = '', $column = '', $attributes = '', $addaftercolumn = '', $doquery = true)
	{
		$exists = false;
		$sql = '';
		$columns = $this->query("SHOW COLUMNS FROM $table", 0, null, __FILE__, __LINE__);
		while ($c = $this->fetch_assoc($columns)) {
			if (isset($c['Field']) and !empty($c['Field']) and $c['Field'] == $column) {
				$exists = true;
				break;
			}
		}
		if ($exists == false) {
			if ($doquery) {
				$sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
				$this->query($sql, 0, null, __FILE__, __LINE__);
			} else {
				$sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
			}
		}
		return $sql;
	}
	/**
	 * Function to determine if an existing field attribute needs to be changed preventing upgrades from any sql errors on duplicate attempts
	 *
	 * @param       string       database table name
	 * @param       string       table field name to add (if does not already exist)
	 * @param       string       table field attributes to process (ie: VARCHAR(250) NOT NULL)
	 * @param       string       table field (ie: NOT NULL DEFAULT)
	 * @param       string       table field default (ie: 0000-00-00) if type was `date`
	 *
	 * @return      boolean      Returns valid sql string if added, blank string if already exists
	 */
	function change_field_if_not_exist($table = '', $column = '', $attributes = '', $null = '', $default = '', $doquery = true)
	{
		$sql = '';
		$columns = $this->query("SHOW COLUMNS FROM $table", 0, null, __FILE__, __LINE__);
		while ($c = $this->fetch_assoc($columns)) {
			if (isset($c['Field']) and !empty($c['Field']) and $c['Field'] == $column) {
				// column exists.. find out if the `Type` or `Default` field attributes have changed..
				if ((isset($c['Type']) and strtolower($c['Type']) != strtolower($attributes)) or (isset($c['Default']) and strtolower($c['Default']) != strtolower($default))) {
					if ($doquery) {
						$sql = (empty($default) ? "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null" : "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null '$default'");
						$this->query($sql, 0, null, __FILE__, __LINE__);
					} else {
						$sql = (empty($default) ? "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null" : "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null '$default'");
					}
					break;
				}
			}
		}
		return $sql;
	}
	/**
	 * Timer function
	 *
	 */
	function timer()
	{
		$this->add();
	}
	/**
	 * Timer add function
	 *
	 */
	function add()
	{
		if (!$this->start) {
			$mtime1 = explode(" ", microtime());
			$this->start = $mtime1[1] + $mtime1[0];
		}
	}
	/**
	 * Get Time from timer() function
	 *
	 */
	function gettime()
	{
		if ($this->end) { // timer has been stopped
			return $this->totaltime;
		} else if ($this->start and !$this->end) { // timer is still going
			$mtime2 = explode(" ", microtime());
			$currenttime = $mtime2[1] + $mtime2[0];
			$totaltime = $currenttime - $this->start;
			return $this->format($totaltime);
		} else {
			return false;
		}
	}
	/**
	 * Stop time from timer() function
	 *
	 */
	function stop()
	{
		if ($this->start) {
			$mtime2 = explode(" ", microtime());
			$this->end = $mtime2[1] + $mtime2[0];
			$totaltime = $this->end - $this->start;
			$this->totaltime = $totaltime;
			$this->formatted = $this->format($totaltime);
			return $this->formatted;
		}
	}
	/**
	 * Remove time from timer() function
	 *
	 */
	function remove()
	{
		$this->name = $this->start = $this->end = $this->totaltime = $this->formatted = '';
	}
	/**
	 * Format time from timer() function
	 */
	function format($string = '')
	{
		return number_format($string, 7);
	}
	function query_cache($sql, $linkidentifier, $timeout = 60)
	{
		if (!$cache = $this->sheel->cache->fetch($sql)) {
			$result = $this->functions['query']($linkidentifier, $sql, MYSQLI_STORE_RESULT);
			if ($this->sheel->db->num_rows($result) > 0) {
				while ($res = $this->sheel->db->fetch_array($result, DB_ASSOC)) {
					$cache[] = $res;
				}
				$this->sheel->cache->store($sql, $cache, $timeout);
			}
			unset($result);
		}
		return $cache;
	}
	/*
	$db->select()->from('users');
	<<OR>>
	echo $db->select()->from('users')->result(); display: 'SELECT * FROM users'
	$db->select()->from('posts')->where('id > 200')->limit(10);
	echo $db->result(); display: 'SELECT * FROM posts WHERE id > 200 LIMIT 10'
	$db->select('firstname','email')->from('users')->where('id = 2399');
	// echo $db->result(); display: 'SELECT firstname, email FROM users WHERE id = 2399'
	*/
	public function select()
	{
		$this->selectables = func_get_args();
		return $this;
	}
	public function from($table)
	{
		$this->table = $table;
		return $this;
	}
	public function where($where)
	{
		$this->whereclause = $where;
		return $this;
	}
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}
	public function result($run = true)
	{
		$query[] = "SELECT";
		if (empty($this->selectables)) { // if the selectables array is empty, select all
			$query[] = "*";
		} else { // else select according to selectables
			$query[] = join(', ', $this->selectables);
		}
		$query[] = "FROM";
		$query[] = $this->table;
		if (!empty($this->whereclause)) {
			$query[] = "WHERE";
			$query[] = $this->whereclause;
		}
		if (!empty($this->limit)) {
			$query[] = "LIMIT";
			$query[] = $this->limit;
		}
		$query = join(' ', $query);
		if (!$run) {
			return $query;
		}
		return $this->query($query);
	}
}
?>