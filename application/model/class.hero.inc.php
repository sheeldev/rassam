<?php
/**
 * Hero class to perform the majority of Hero functions in sheel.
 *
 * @package      sheel\Hero
 * @version      1.0.0.0
 * @author       sheel
 */
class hero
{
	protected $sheel;

    public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, mode, cid, filename, imagemap, caption1, caption2, caption3, buttoncaption, buttonurl, newwindow, width, height,styleid, date_added, sort
                        FROM " . DB_PREFIX . "hero
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$this->cache[$res['mode']] = $res;
			}
			unset($res);
		}
	}

	function fetch_heros($mode = '', $cid = 0)
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		$cidcondition = "";
		$heros = array();
		if ($cid > 0)
		{
			$cidcondition = "AND cid = '" . intval($cid). "'";
		}
		$styleid = $this->sheel->config['defaultstyle'];
		if (isset($_SESSION['sheeldata']['user']['styleid']) AND $_SESSION['sheeldata']['user']['styleid'] > 0)
		{
			$styleid = $_SESSION['sheeldata']['user']['styleid'];
		}

		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, filename, imagemap, width, height
			FROM " . DB_PREFIX . "hero
			WHERE mode = '" . $this->sheel->db->escape_string($mode) . "'
			$cidcondition
			AND styleid = '" . intval($styleid) ."'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		$rowstotal = $this->sheel->db->num_rows($sql);
		if ($rowstotal > 0)
		{
			while ($res = $this->sheel->db->fetch_assoc($sql))
			{
				$heros[] = $res;
			}
		}

		$this->sheel->timer->stop();
		return $heros;
	}
}
?>