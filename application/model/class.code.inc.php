<?php

class code
{
	protected $sheel;
	public $code;
	/**
	* Constructor
	*
	*/
	function __construct($sheel)
	{
		$this->sheel = $sheel;	
	}
	function get_code($userid, $groupid, $expiry='0') {
		if ($userid > 0 AND $groupid > 0) {
			$this->code = array();
			$expired = false;
			$query = $this->sheel->db->query("
				SELECT id, code, codegroupid, userid, expiry, used, verified
				FROM " . DB_PREFIX . "codes 
				WHERE codegroupid = '" . intval($groupid) . "' and userid = '" . intval($userid) . "' and used ='0'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		
			if ($this->sheel->db->num_rows($query) > 0) {
				$this->code = $this->sheel->db->fetch_array($query, DB_ASSOC);
				if (time() >= $this->code['expiry']) {
					$expired = true;
					$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "codes
						SET used = '1' where id = '" . $this->code['id'] . "'
						");
				}
			}
			else {
				$expired = true;
			}
			if ($expired AND $expiry > 0) {
				$exposedtime = time() + (intval($expiry)*60);
				$this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "codes
				(id, code, codegroupid, userid, expiry, used, verified)
				VALUES(
				NULL,
				'" . rand(100000, 999999) . "',
				'" . $groupid . "',
				'" . $userid . "',
				'" . $exposedtime . "',
				'" . 0 . "',
				'" . 0 . "')
			");
			$newcodeid = $this->sheel->db->insert_id();
			$querynewid = $this->sheel->db->query("
				SELECT id, code, codegroupid, userid, expiry, used, verified
				FROM " . DB_PREFIX . "codes 
				WHERE id = '" . intval($newcodeid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$this->code = $this->sheel->db->fetch_array($querynewid, DB_ASSOC);
			$this->code['expired'] = '1';
			}

			else {
				$querynewid = $this->sheel->db->query("
					SELECT id, code, codegroupid, userid, expiry, used, verified
					FROM " . DB_PREFIX . "codes 
					WHERE codegroupid = '" . intval($groupid) . "' and userid = '" . intval($userid) . "' and used ='0'
					LIMIT 1
					", 0, null, __FILE__, __LINE__);
				$this->code = $this->sheel->db->fetch_array($querynewid, DB_ASSOC);
				$this->code['expired'] = '0';
			}
		}
		return $this->code;	
	}

	function isverified ($codeid, $code, $userid) {
		$this->code = array();
		$verified = false;
		$query = $this->sheel->db->query("
			SELECT id, code, codegroupid, userid, expiry, used, verified
			FROM " . DB_PREFIX . "codes 
			WHERE id = '" . intval($codeid) . "' and userid = '" . intval($userid) . "' and verified ='1' and code='".$code."' and used='1'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);

		if ($this->sheel->db->num_rows($query) > 0) {
			$this->code = $this->sheel->db->fetch_array($query, DB_ASSOC);
			if ($this->code['verified']) {
				$verified=true;
			}
		}
		return $verified;
	}
	function set_verified($codeid) {
		$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "codes
				SET used= '1', verified = '1'
				WHERE id = '" . intval($codeid) . "'
			");

	}
}
?>
