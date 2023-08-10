<?php
class admincp_common extends admincp
{
	public function disableapis($ids = array())
	{
		$allerrors = $successids = $failedids = '';
		$count = 0;
		$this->counter = 1;
		foreach ($ids AS $inc => $itemid)
		{
			$response = $this->disableapi($itemid);
			if ($response === true)
			{
				$successids .= "$itemid~";
				$count++;
			}
			else
			{
				$failedids .= "$itemid~";
				$allerrors .= $response . '|';
			}
		}
		$this->sheel->template->templateregistry['action'] = '{_disabled_lc}';
		$this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_api_endpoint_lower}' : '{_api_endpoints_lower}');
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'API end points disabled successfully' : 'Failure disabling API end points'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
	}
	public function enableapis($ids = array())
	{
		$allerrors = $successids = $failedids = '';
		$count = 0;
		$this->counter = 1;
		foreach ($ids AS $inc => $itemid)
		{
			$response = $this->enableapi($itemid);
			if ($response === true)
			{
				$successids .= "$itemid~";
				$count++;
			}
			else
			{
				$failedids .= "$itemid~";
				$allerrors .= $response . '|';
			}
		}
		$this->sheel->template->templateregistry['action'] = '{_enabled_lc}';
		$this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_api_endpoint_lower}' : '{_api_endpoints_lower}');
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'API end points enabled successfully' : 'Failure enabling API end points'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
	}
	private function disableapi($itemid = 0)
	{
		$this->sheel->db->query("
			UPDATE " . DB_PREFIX . "api
			SET `value` = '0'
			WHERE id = '" . intval($itemid) . "'
			LIMIT 1
		");
		return true;
	}
	private function enableapi($itemid = 0)
	{
		$this->sheel->db->query("
			UPDATE " . DB_PREFIX . "api
			SET `value` = '1'
			WHERE id = '" . intval($itemid) . "'
			LIMIT 1
		");
		return true;
	}
}
?>
