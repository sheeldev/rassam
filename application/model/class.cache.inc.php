<?php
/**
* cache class to perform the majority of cache handling operations in sheel
*
* @package      sheel\Cache
* @version		1.0.0.0
* @author       sheel
*/
class cache
{
	protected $sheel;
	/**
	* Cache name placeholder
	*
	* @var string
	* @access public
	*/
	public $name;
	/**
	* Cache instance placeholder
	*
	* @var string
	* @access public
	*/
	public $instance;
	public $ttl;
	// cache filename using md5 filehash (default false)
	public $md5 = true;
	// save the cache key with some extras
	public $params = array('uid' => true, 'sid' => true, 'rid' => true, 'styleid' => true, 'slng' => true);
	/**
	* Cache constructor
	*
	* @param      boolean     cache filename using md5 filehash (default false)
	* @param      array       cache parameters
	*
	*/
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$engine = ((defined('LOCATION') AND LOCATION == 'installer') ? 'none' : CACHE_ENGINE);
		switch ($engine)
		{
			case 'filecache':
			{
				$this->name = 'sheel_filecache';
				break;
			}
			case 'none':
			{
				$this->name = 'sheel_nocache';
				break;
			}
			default:
			{
				$this->name = 'sheel_nocache';
				break;
			}
		}
		$this->instance = new $this->name(CACHE_EXPIRY, CACHE_PREFIX, $this->md5, $this->params);
		return $this->instance;
	}
	/**
	* Function to fetch the cache based on a cache key
	*
	* @param      string      cache key
	* @param      array       params
	*
	* @return     string      Returns cache data
	*/
	public function fetch($key = '', $params = array())
	{
		if (!empty($key))
		{
			return $this->instance->fetch($key, $params);
		}
		return false;
	}
	/**
	* Function to save the cache based on a cache key
	*
	* @param      string      cache key
	* @param      string      cache data
	* @param      integer     time to live
	* @param      array       params
	*
	*/
	public function store($key = '', $data = '', $ttl = 0, $params = array())
	{
		if (!empty($key))
		{
			if ($ttl > 0)
			{
				$this->ttl = $ttl;
			}
			return $this->instance->store($key, $data, $ttl, $params);
		}
		return false;
	}
	/**
	* Function to delete a cache value based on a cache key
	*
	* @param      string      cache key
	* @param      array       params
	*
	*/
	public function delete($key = '', $params = array())
	{
		if (!empty($key))
		{
			return $this->instance->delete($key, $params);
		}
		return false;
	}
}
/**
* Core no-cache class
*
* @package      sheel\Cache\NoCache
* @version	6.0.0.622
* @author       sheel
*/
class sheel_nocache extends cache
{
	/**
	* Cache constructor
	*
	* @param      integer     cache expiry
	* @param      string      cache prefix
	* @param      boolean     use cache filehash
	* @param      array       cache parameters
	*
	*/
	function __construct($ttl = 60, $prefix = '', $md5 = false, $params = array()){}
	/**
	* Fetch items from cache
	*
	* @return
	*/
	public function fetch($key = '', $params = array())
	{
		return false;
	}
	/**
	* Store items in cache
	*
	* @return
	*/
	public function store($key = '', $data = '', $ttl = 0, $params = array())
	{
		return false;
	}
	/**
	* Delete items in cache
	*
	* @return
	*/
	public function delete($key = '', $params = array())
	{
		return false;
	}
}
/**
* sheel file system cache class to perform the majority of database caching in sheel
*
* @package      sheel\Cache\FileCache

* @author       sheel
*/
class sheel_filecache extends cache
{
	/**
	* Cache time to live placeholder
	*
	* @var integer
	* @access public
	*/
	public $ttl;
	/**
	* Cache prefix
	*
	* @var string
	* @access public
	*/
	public $prefix;
	/**
	* Cache filehash placeholder
	*
	* @var string
	* @access public
	*/
	public $md5;
	/**
	* Cache parameter array
	*
	* @var array
	* @access public
	*/
	public $params = array('uid' => true, 'sid' => true, 'rid' => true, 'styleid' => true, 'slng' => true);
	/**
	* Cache data placeholder
	*
	* @var string
	* @access public
	*/
	public $data;
	/**
	* Cache constructor
	*
	* @param      integer     cache expiry
	* @param      string      cache prefix
	* @param      boolean     use cache filehash
	* @param      array       cache parameters
	*
	*/
	public function __construct($ttl = 60, $prefix = 'sheel_', $md5 = false, $params = array('uid' => true, 'sid' => true, 'rid' => true, 'styleid' => true, 'slng' => true))
	{
		$this->ttl = $ttl;
		$this->prefix = $prefix;
		$this->md5 = $md5;
		$this->params = $params;
		$this->gc();
	}
	/**
	* Garbage cache collection removal
	*
	* @return
	*/
	private function gc()
	{
		if ($handle = opendir(DIR_TMP . DIR_DATASTORE_NAME . '/'))
		{
			$dir_array = array();
			while (false !== ($file = readdir($handle)))
			{
				if ($file != '.' AND $file != '..')
				{
					if (file_exists(DIR_TMP . DIR_DATASTORE_NAME . '/' . $file))
					{
						$lastmod = @filemtime(DIR_TMP . DIR_DATASTORE_NAME . '/' . $file);
						if (($lastmod + ($this->ttl)) < time())
						{
							@unlink(DIR_TMP . DIR_DATASTORE_NAME . '/' . $file);
						}
					}
				}
			}
			closedir($handle);
		}
	}
	/**
	* Fetch items from sheel file sytem cache
	*
	* @param      string       cache key
	* @param      array        params
	*
	* @return     string       Returns cached data
	*/
	public function fetch($key = '', $params = array())
	{
		if (!empty($key))
		{
			$filename = $this->getfilename($key, $params);
			if (!file_exists($filename))
			{
				return false;
			}
			$data = file_get_contents($filename);
			$detect = json_decode($data);
			if ($data != '' AND json_last_error() === JSON_ERROR_NONE)
			{
				$data = json_decode($data, true);
			}
			return $data;
		}
		return false;
	}
	/**
	* Store items in sheel file system cache/datastore/ folder
	*
	* @param      string       cache key
	* @param      string       data to cache
	* @param      integer      time to live (in seconds) default 60 seconds
	* @param      array        params
	*
	* @return     null
	*/
	public function store($key = '', $data = '', $ttl = 0, $params = array())
	{
		if ($key != '')
		{
			if ($ttl > 0)
			{
				$this->ttl = $ttl;
			}
			if ($data == '')
			{
				return false;
			}
			$filename = $this->getfilename($key, $params);
			if (is_array($data))
			{
				$data = json_encode($data);
			}
			file_put_contents($filename, $data);
			touch($filename);
		}
		return false;
	}
	/**
	* Delete items in sheel file system cache
	*
	* @param      string       cache key
	* @param      array        params
	*
	* @return     boolean      Returns true or false if cache could not be deleted
	*/
	public function delete($key = '', $params = array())
	{
		if (!empty($key))
		{
			$filename = $this->getfilename($key, $params);
			if (file_exists($filename))
			{
				return unlink($filename);
			}
		}
		return false;
	}
	/**
	* Get local cache filename on server
	*
	* @param       string       cache key
	* @param       array        params
	*
	* @return      string       Return full folder and filename of cache file
	*/
	private function getfilename($key = '', $params = array())
	{
		if (!empty($key))
		{
			if (count($params) > 0)
			{
				$uid = ((isset($_SESSION['sheeldata']['user']['userid']) AND $params['uid']) ? $_SESSION['sheeldata']['user']['userid'] : 0);
				$sid = ((isset($_SESSION['sheeldata']['user']['subscriptionid']) AND $params['sid']) ? $_SESSION['sheeldata']['user']['subscriptionid'] : 0);
				$rid = ((isset($_SESSION['sheeldata']['user']['roleid']) AND $params['rid']) ? $_SESSION['sheeldata']['user']['roleid'] : 0);
				$styleid = ((isset($_SESSION['sheeldata']['user']['styleid']) AND $params['styleid']) ? $_SESSION['sheeldata']['user']['styleid'] : 0);
				$slng = ((isset($_SESSION['sheeldata']['user']['slng']) AND $params['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 0);
			}
			else
			{
				$uid = ((isset($_SESSION['sheeldata']['user']['userid']) AND $this->params['uid']) ? $_SESSION['sheeldata']['user']['userid'] : 0);
				$sid = ((isset($_SESSION['sheeldata']['user']['subscriptionid']) AND $this->params['sid']) ? $_SESSION['sheeldata']['user']['subscriptionid'] : 0);
				$rid = ((isset($_SESSION['sheeldata']['user']['roleid']) AND $this->params['rid']) ? $_SESSION['sheeldata']['user']['roleid'] : 0);
				$styleid = ((isset($_SESSION['sheeldata']['user']['styleid']) AND $this->params['styleid']) ? $_SESSION['sheeldata']['user']['styleid'] : 0);
				$slng = ((isset($_SESSION['sheeldata']['user']['slng']) AND $this->params['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 0);
			}
			$key = $key . '_' . $styleid . '_' . $slng . '_' . $uid . '_' . $sid . '_' . $rid . '_' . SITE_ID;
			if ($this->md5)
			{
				$key = md5($key);
			}
			$key = $this->prefix . $key;
			return DIR_TMP . DIR_DATASTORE_NAME . '/' . $key;
		}
		return false;
	}
}
?>
