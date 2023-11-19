<?php

class access
{
	protected $sheel;
	/**
	 * Constructor
	 *
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	function has_access($userid, $page)
	{
		$hasaccess = false;
		$query1 = $this->sheel->db->query("
				SELECT hasaccess
				FROM " . DB_PREFIX . "roles_access 
				WHERE roleid = '0' and accessname ='" . $page . "'
				LIMIT 1
				", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($query1) > 0) {
			$access = $this->sheel->db->fetch_array($query1, DB_ASSOC);
			if ($access['hasaccess']) {
				$hasaccess = true;
			}
		} else {
			$query = $this->sheel->db->query("
					SELECT roleid
					FROM " . DB_PREFIX . "users 
					WHERE user_id = '" . intval($userid) . "' and status ='active'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($query) > 0) {
				$user = $this->sheel->db->fetch_array($query, DB_ASSOC);
				$query1 = $this->sheel->db->query("
						SELECT hasaccess
						FROM " . DB_PREFIX . "roles_access 
						WHERE roleid = '" . $user['roleid'] . "' and accessname ='" . $page . "'
						LIMIT 1
						", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($query1) > 0) {
					$access = $this->sheel->db->fetch_array($query1, DB_ASSOC);
					if ($access['hasaccess']) {
						$hasaccess = true;
					}
				}
			}
		}
		return $hasaccess;
	}
}
?>