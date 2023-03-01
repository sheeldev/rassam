<?php
/**
* Membership roles class for sheel.
*
* @package      sheel\Membership\Roles
* @version      1.0.0.0
* @author       sheel
*/
class role
{
	protected $sheel;

	function __construct($sheel)
	{
			$this->sheel = $sheel;
	}
	
	/**
	* Function to print a particular role title
	*
	* @param       integer        role id
	*
	* @return      string         Returns the role title
	*/
	function print_role($roleid = 0, $slng = 'eng')
	{
		if (empty($slng))
		{
			$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		}
		$sqlroles = $this->sheel->db->query("
			SELECT title_$slng AS title
			FROM " . DB_PREFIX . "roles
			WHERE roleid = '" . intval($roleid) . "'
				AND roletype = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sqlroles) > 0)
		{
			$roles = $this->sheel->db->fetch_array($sqlroles, DB_ASSOC);
			return stripslashes($roles['title']);
		}
		return '{_no_role}';
	}
	/**
	* Function to print the role pulldown menu with selected options as the roles
	*
	* @param       string         selected role option
	* @param       bool           show "none selected" option
	* @param       bool           show role plan count beside role name
	* @param       bool           are we generating the pulldown via admincp?
	* @param       string         extra javascript
	* @param       string         language identifer (eng)
	* @param       string         select menu class
	* @param       string         select menu id
	* @param       string         select menu fieldname
	*
	* @return      string         Returns HTML representation of the role pulldown menu
	*/
	function print_role_pulldown($selected = '', $shownoneselected = 0, $showplancount = 0, $adminmode = 0, $js = '', $slng = '', $class = 'draw-select', $id = 'form_roleid', $fieldname = 'form[roleid]', $disabled = false)
	{
		if (empty($slng))
		{
			$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : $this->sheel->language->fetch_site_slng();
		}
		$arr = array();
		$default = $selected;
		if ($adminmode == 0)
		{
			$sql = "
				SELECT roleid, purpose_$slng AS purpose, title_$slng AS title, custom, roletype, roleusertype, active
				FROM " . DB_PREFIX . "roles
				WHERE active = '1'
			";
		}
		else
		{
			$sql = "
				SELECT roleid, purpose_$slng AS purpose, title_$slng AS title, custom, roletype, roleusertype, active
				FROM " . DB_PREFIX . "roles
				WHERE active = '1'
				AND (roletype = 'product' OR roletype = 'both')
			";
		}
		if (isset($shownoneselected) AND $shownoneselected)
		{
			$selected = ((empty($selected)) ? '-1' : $selected);
			$arr['-1'] = '{_select}';
		}
		$sqlroles = $this->sheel->db->query($sql, 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sqlroles) > 0)
		{
			while ($roles = $this->sheel->db->fetch_array($sqlroles, DB_ASSOC))
			{
				if (isset($adminmode) AND $adminmode OR $roleattach > 0)
				{
					$arr[$roles['roleid']] = stripslashes($roles['title']) . ' - ' . stripslashes($roles['purpose']);
					$arr[$roles['roleid']] .= (((isset($showplancount) AND $showplancount)) ? ' - ' . $roleattach . ((($roleattach == 1)) ? ' {_subscription_plan}' : ' {_subscription_plans}') : '');
				}
			}
		}
		$extradisabled = (($disabled) ? ' disabled="disabled"' : '');
		return $this->sheel->construct_pulldown($id, $fieldname, $arr, $selected, 'class="' . $class . '"' . $extradisabled . ' ' . $js);
	}

	/**
	* Function to fetch a particular role id for a user
	*
	* @param       integer        user id
	*
	* @return      bool           Returns integer role id value
	*/
	function fetch_user_roleid($userid = 0)
	{
		$sqlroles = $this->sheel->db->query("
			SELECT u.roleid
			FROM " . DB_PREFIX . "users u
			WHERE u.user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sqlroles) > 0)
		{
			$roles = $this->sheel->db->fetch_array($sqlroles, DB_ASSOC);
			return $roles['roleid'];
		}
		return 0;
	}
}
?>
