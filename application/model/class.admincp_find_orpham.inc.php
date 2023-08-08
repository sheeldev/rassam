<?php
class admincp_find_orphan extends admincp
{
	var $basedir = '';
	var $files;
	var $folders;
	var $keyword;
	var $matches = array();
	var $match = 0;
	var $totalphrases = 0;
	var $orphanphrases = 0;
	var $query = '';
	var $filecontents = array();

	function find_phrase($root)
	{
		$this->basedir = $root;
		$this->files = 0;
		$this->folders = array();
		$sql = $this->sheel->db->query("
			SELECT phraseid, varname
			FROM " . DB_PREFIX . "language_phrases
			WHERE phrasegroup != 'admincp_configuration'
				AND phrasegroup != 'admincp_permissions'
				AND phrasegroup != 'admincp_configuration_groups'
				AND varname NOT LIKE '%permission_desc'
				AND varname NOT LIKE '%permission_help'
				AND ismaster = '1'
			ORDER BY phraseid ASC
		");
		$this->totalphrases = $this->sheel->db->num_rows($sql);
		$this->query = '';
		$this->__cache_files($root);
		while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
		{
			$this->__search('', $res['varname']);
			if ($this->matches[$res['varname']] == 0)
			{
				$this->orphanphrases = $this->orphanphrases + 1;
				$this->query .= "DELETE FROM " . DB_PREFIX . "language_phrases WHERE varname = '" . $res['varname'] . "';" . LINEBREAK;
			}
			flush();
		}
		return $this->query;
	}
	function find_emailtemplate($root)
	{
		$this->basedir = $root;
		$this->files = 0;
		$this->folders = array();
		$sql = $this->sheel->db->query("SELECT id, varname FROM " . DB_PREFIX . "email ORDER BY id ASC");
		$this->totalphrases = $this->sheel->db->num_rows($sql);
		$this->query = '';
		$this->__cache_files($root);
		while ($res = $this->sheel->db->fetch_array($sql))
		{
			$this->__search('', $res['varname']);
			if ($this->matches[$res['varname']] == 0)
			{
				$this->orphanphrases = $this->orphanphrases + 1;
				$this->query .= "DELETE FROM " . DB_PREFIX . "email WHERE varname = '" . $res['varname'] . "';" . LINEBREAK;
			}
			flush();
		}
		return $this->query;
	}
	function __cache_files($dir = '', $varname = '')
	{
		$path = $dir;
		foreach (scandir($path) AS $found)
		{
			if (!$this->__isdot($found) AND !$this->__issvn($found) AND !$this->__isimg($found) AND !$this->__isinstall($found) AND !$this->__isuploads($found))
			{
				$absolute = "$path/$found";
				$relative = $dir == '' ? $found : "$dir/$found";
				$ext = substr($absolute, -3);
				if (is_dir($absolute))
				{
					$this->folders[] = $relative;
					$this->__cache_files($relative);
				}
				else if (is_file($absolute) AND ($ext == 'php' OR $ext == 'tml' OR $ext == 'htm' OR $ext == 'xml' OR $ext == '.js'))
				{
					$this->filecontents[] = file_get_contents($absolute, "r");
				}
			}
		}
		echo '';
	}
	function __search($dir = '', $varname = '')
	{
		$path = $dir == '' ? $this->basedir : "{$this->basedir}/$dir";
		$this->matches[$varname] = 0;
		foreach ($this->filecontents AS $key => $value)
		{
			if (isset($value) AND isset($varname))
			{
				$match = strpos($value, $varname);
				if (is_numeric($match) AND $match > 0)
				{
					$this->matches[$varname] = 1;
					break;
				}
			}
		}
	}
	function __isdot($s)
	{
		return ($s == '.' || $s == '..');
	}
	function __issvn($s)
	{
		return ($s == '.svn');
	}
	function __isimg($s)
	{
		return ($s == 'images');
	}
	function __isinstall($s)
	{
		return ($s == 'xml');
	}
	function __isuploads($s)
	{
		return ($s == 'uploads');
	}
}
?>
