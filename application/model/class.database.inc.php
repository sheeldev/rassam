<?php
if (!class_exists('sheel_database')) {
	echo 'Could not find database backend.';
	exit;
}

/**
 * MySQLi database class to perform the majority of database related functions in sheel.
 *
 * @package      sheel\Database\MySQLi
 * @version      6.0.0.622
 * @author       sheel
 */
class database extends sheel_database
{
	var $enablecache;
	var $types = array(
		DB_NUM => MYSQLI_NUM,
		DB_ASSOC => MYSQLI_ASSOC,
		DB_BOTH => MYSQLI_BOTH
	);

	/**
	 * MySQLi Database Interface Functions
	 *
	 * @var	    $functions
	 */
	var $functions = array(
		'select_db' => 'mysqli_select_db',
		'pconnect' => 'mysqli_real_connect',
		'connect' => 'mysqli_real_connect',
		'query' => 'mysqli_query',
		'query_unbuffered' => 'mysqli_unbuffered_query',
		'fetch_row' => 'mysqli_fetch_row',
		'fetch_object' => 'mysqli_fetch_object',
		'fetch_array' => 'mysqli_fetch_array',
		'fetch_field' => 'mysqli_fetch_field',
		'free_result' => 'mysqli_free_result',
		'data_seek' => 'mysqli_data_seek',
		'connect_error' => 'mysqli_connect_error',
		'connect_errno' => 'mysqli_connect_errno',
		'error' => 'mysqli_error',
		'errno' => 'mysqli_errno',
		'affected_rows' => 'mysqli_affected_rows',
		'num_rows' => 'mysqli_num_rows',
		'num_fields' => 'mysqli_num_fields',
		'field_name' => 'mysqli_field_name',
		'insert_id' => 'mysqli_insert_id',
		'list_tables' => 'mysqli_list_tables',
		'list_fields' => 'mysqli_list_fields',
		'escape_string' => 'mysqli_real_escape_string',
		'real_escape_string' => 'mysqli_real_escape_string',
		'close' => 'mysqli_close',
		'client_encoding' => 'mysqli_client_encoding',
		'create_db' => 'mysqli_create_db',
		'ping' => 'mysqli_ping',
		'prepare' => 'mysqli_prepare',
		'bind_param' => 'mysqli_stmt_bind_param',
		'execute' => 'mysqli_stmt_execute',
		'bind_result' => 'mysqli_stmt_bind_result',
		'stmp_fetch' => 'mysqli_stmt_fetch',
		'stmp_close' => 'mysqli_stmt_close'
	);

	/**
	 * Constructor
	 *
	 * @param	object	        sheel registry object
	 * @param        integer         cache time out
	 * @param        bool            cache results to database within cache table?
	 */
	function __construct(&$sheel, $cachetimeout = 1, $cachetodatabase = true)
	{
		$this->sheel = & $sheel;
		$this->connect();
	}

	/**
	 * Connect to the database and return the connection link resource
	 *
	 * Connects to a database server and physically returns the connection link identifier
	 *
	 * @return	boolean
	 */
	function db_connect()
	{
		$hostname = DB_SERVER;
		$username = DB_SERVER_USERNAME;
		$password = DB_SERVER_PASSWORD;
		$dbcharset = DB_CHARSET;
		$dbcollate = DB_COLLATE;
		$port = DB_SERVER_PORT;

		$link = mysqli_init();
		if (!$connect = @$this->functions['connect']($link, $hostname, $username, $password, '', $port)) {
			if ($this->functions['connect_errno']()) {
				$this->dberror($this->functions['connect_error']());
			}
		}
		if (!empty($dbcharset) and !empty($dbcollate)) {
			$this->functions['query']($link, "SET CHARACTER SET $dbcharset");
			$this->functions['query']($link, "SET NAMES $dbcharset");
			$this->functions['query']($link, "SET COLLATION_DATABASE $dbcollate");
			$this->functions['query']($link, "SET COLLATION_CONNECTION $dbcollate");
			$this->functions['query']($link, "SET character_set_results = '$dbcharset', character_set_client = '$dbcharset', character_set_connection = '$dbcharset', character_set_database = '$dbcharset', character_set_server = '$dbcharset', character_set_system = '$dbcharset'");
			$this->functions['query']($link, "SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION'");
			$this->functions['query']($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
			//mysqli_set_charset($link, "utf8");
		}
		return ((!$connect) ? false : $link);
	}

	/**
	 * Function to select the database with an associated mysql link identifier
	 *
	 * @param       string        database name
	 * @param       object        database link
	 *
	 */
	function select_db_wrapper($database = '', $link = null)
	{
		return $this->functions['select_db']($link, $database);
	}

	public function prepare($string = '')
	{
		$stmt = $this->functions['prepare']($this->connection_link, $string);
		return $stmt;
	}
	/**
	 * Function to perform a database specific query
	 *
	 * @param       string        sql query string
	 * @param       boolean       hide database errors? default false
	 * @param       string        cache to filesystem filename
	 * @param       string        script filename
	 * @param       string        script line number
	 * @param       boolean       buffered query (default true)
	 * @param       array         error from sql notices exemption array
	 *
	 * @return      object        query result
	 */
	public function query($string = '', $hideerrors = 0, $enablecache = null, $script = '', $line = '', $buffered = true, $errorexempt = array())
	{
		global $querytime;
		$this->enablecache = $enablecache;
		$this->query_count++;
		$this->timer();
		$qtimer = $this->gettime();
		if ($enablecache == null or !$enablecache or $enablecache == 0) {
			$queryresult = $this->functions['query']($this->connection_link, $string, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT));
		} else {
			$queryresult = $this->query_cache($string, $this->connection_link);
		}
		if ($this->errno() > 0) { // error
			if (!$hideerrors or $hideerrors == 0) {
				$this->dberror($string);
			} else {
				return false;
			}
		}
		$qtime = $this->stop();
		$querytime += $this->totaltime;
		$this->remove();
		return $queryresult;
	}

	/**
	 * Function to perform a database specific query and immediately returns the associated array/results
	 *
	 * @param       string        sql code
	 * @param       bool          hide database errors? default false
	 * @param       string        cache to filesystem filename
	 * @param       string        script filename
	 * @param       string        script line number
	 *
	 */
	public function query_fetch($string, $hideerrors = 0, $enablecache = null, $script = '', $line = '', $buffered = true)
	{
		global $querytime;
		$this->query_count++;
		$this->timer();
		$qtimer = $this->gettime();
		$query = $this->functions['query']($this->connection_link, $string, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT));
		if ($this->errno() > 0) {
			if (!$hideerrors or $hideerrors == 0) {
				$this->dberror($string);
			} else {
				return false;
			}
		}
		$qtime = $this->stop();
		$querytime += $this->totaltime;
		$this->remove();
		return $this->fetch_array($query);
	}

	/**
	 * Function to perform a database fetch array
	 *
	 * @param       string        sql code
	 * @param       string        sql result type
	 *
	 */
	public function fetch_array(&$query, $type = DB_BOTH)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}

	/**
	 * Function to perform a database fetch object
	 *
	 * @param       string        sql code
	 *
	 */
	public function fetch_object(&$query)
	{
		return $this->functions['fetch_object']($query);
	}

	/**
	 * Function to perform a database fetch associative array
	 *
	 * @param       string        sql code
	 * @param       string        sql result type
	 *
	 */
	public function fetch_assoc(&$query, $type = DB_ASSOC)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}

	/**
	 * Function to perform a database fetch row
	 *
	 * @param       string        sql code
	 *
	 */
	public function fetch_row(&$query)
	{
		return @$this->functions['fetch_row']($query);
	}

	/**
	 * Function to perform a database table list
	 *
	 * @param       string        sql code
	 *
	 */
	public function list_tables(&$query)
	{
		$tableList = array();
		$res = $this->query("SHOW TABLES");
		while ($cRow = $this->fetch_array($res, DB_BOTH)) {
			$tableList[] = $cRow[0];
		}
		return $tableList;
	}

	/**
	 * Function to fetch the total number of affected rows for the connection
	 *
	 */
	public function affected_rows()
	{
		return $this->functions['affected_rows']($this->connection_link);
	}

	/**
	 * Function to fetch a field value result from a table
	 *
	 * @param       string       table name
	 * @param       string       sql condition code
	 * @param       string       field name
	 *
	 */
	public function fetch_field($tbl = '', $condition = '', $field = '', $limit = '', $file = '', $line = '')
	{
		$limit = !empty($limit) ? ' LIMIT ' . $limit : '';
		$condition = !empty($condition) ? ' WHERE ' . $condition : '';
		$result = $this->query("
                        SELECT " . $this->escape_string($field) . "
                        FROM " . $this->escape_string($tbl) .
			$condition .
			$limit . "
		", 0, null, $file, $line);
		$row = ($this->fetch_array($result));
		return $row["$field"];
	}

	/**
	 * Function to perform a database num rows
	 *
	 * @param       string        sql code
	 *
	 */
	public function num_rows($query = '')
	{
		if ($this->enablecache) {
			return count($query);
		} else {
			return $this->functions['num_rows']($query);
		}
	}

	/**
	 * Function to perform a database num fields
	 *
	 * @param       string        sql code
	 *
	 */
	public function num_fields($query = '')
	{
		return $this->functions['num_fields']($query);
	}

	/**
	 * Function to perform a database field name
	 *
	 * @param       string        sql code
	 *
	 */
	public function field_name($query = '')
	{
		return $this->functions['field_name']($query);
	}

	/**
	 * Function to fetch the last insert id for the database connection
	 *
	 */
	public function insert_id()
	{
		return $this->functions['insert_id']($this->connection_link);
	}

	/**
	 * Function to close the database connection
	 *
	 */
	public function close()
	{
		@$this->functions['close']($this->connection_link);
	}

	/**
	 * Function to mimic database error handling
	 *
	 */
	public function error()
	{
		$this->error = ($this->connection_link === null) ? '' : $this->functions['error']($this->connection_link);
		return $this->error;
	}

	/**
	 * Function to mimic database error number handling
	 *
	 */
	public function errno()
	{
		$this->errno = ($this->connection_link === null) ? 0 : $this->functions['errno']($this->connection_link);
		return $this->errno;
	}

	/**
	 * Function to execute xxxx_real_escape_string()
	 *
	 * @param       string        sql code
	 *
	 */
	public function escape_string($query = '')
	{
		return $this->functions['real_escape_string']($this->connection_write, $query);
	}

	/**
	 * Function to frees the memory associated with a result
	 *
	 * @param       string        sql result
	 *
	 */
	public function free_result($res = '')
	{
		return $this->functions['free_result']($res);
	}
}
?>