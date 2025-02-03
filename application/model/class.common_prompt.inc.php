<?php
/**
 * common_prompt.
 *
 * @package      sheel\Common\Prompt
 * @version      1.0.0.0
 * @author       sheel
 */
class common_prompt extends common
{
	function get_prompt_result($staffno, $prompt, $companycode)
	{
		if ($prompt == 'measurements_validation') {
			$staff = [];
			$staffmeasurements = [];
			$iserror = false;
			$errors = [];
			$errorMessage = '';
			if (!$this->sheel->dynamics->init_dynamics('erCustomerStaffs', $companycode)) {
				$iserror = true;
				$errorMessage = '{_inactive_dynamics_api}' . ': erCustomerStaffs';
			}
			$searchcondition = '$filter=code eq \'' . $staffno . '\'';
			$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
			if ($apiResponse->isSuccess()) {
				$staff = $apiResponse->getData()[0];
			} else {
				$iserror = true;
				$this->sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
				$errorMessage = $this->sheel->template->parse_template_phrases('message');
			}
			$staffmeasurements = [];
			$genaiparam = [];
	
	
			if (!$this->sheel->dynamics->init_dynamics('erStaffMeasurements', $companycode)) {
				$iserror = true;
				$errorMessage = '{_inactive_dynamics_api}' . ': erStaffMeasurements';
			}
			$searchcondition = '$filter=staffCode eq \'' . $staff['code'] . '\'';
			$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
			if ($apiResponse->isSuccess()) {
				$staffmeasurements = $apiResponse->getData();
			} else {
				$iserror = true;
				$this->sheel->template->templateregistry['message'] = $apiResponse->getErrorMessage();
				$errorMessage = $this->sheel->template->parse_template_phrases('message');
			}
			foreach ($staffmeasurements as &$measurement) {
				$genaiparam['{{' . $measurement['measurementCode'] . '}}'] = $measurement['value'];
			}
			$genaiparam['{{GENDER}}'] = $staff['gender'];
			$genaiparam['{{WUOM}}'] = 'KG';
			$genaiparam['{{MUOM}}'] = 'CM';
			$genai = $this->sheel->genai;
			$result = $genai->init_prompt($prompt, $_SESSION['sheeldata']['user']['userid']);
			if ($result !== true) {
				$iserror = true;
				$this->sheel->template->templateregistry['message'] = $result;
				$errorMessage = $this->sheel->template->parse_template_phrases('message');
			} else {
				if ($genai->set($genaiparam)) {
					$chat = $genai->chat();
					$airesponse = [];
					if ($chat->isSuccess()) {
						$airesponse = $chat->getData();
					} else {
						$iserror = true;
						$errors = $chat->getErrors();
						foreach ($errors as $error) {
							if (isset($error['message'])) {
								$html .= $error['message'] . "<br>";
							}
						}
					}
				}
				else {
					$iserror = true;
					$errorMessage = 'Wrong parameters';
				}	
			}
			$html .= $errorMessage;
		}
		else {

		}
		$html .= '<div><h1><span class="' . ($this->sheel->config["template_textdirection"] == 'ltr' ? 'right' : 'left') . ' bold"></span>' . $staff['code'] . ': '.$staff['name'].'</h1>';
		$html .= '<div class="hr-20-0-20-0"></div>';
		$html .= '</div>';
		$html .= '<div id="assmeblies_status">';
		$html .= '<div class="draw-card__section">';
		$html .= '<div class="table-wrapper bulk-action-div" style="">';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th width="8%"> <span><label>{_code}</label></span> </th>';
		$html .= '<th width="8%"> <span><label>{_range}</label></span> </th>';
		$html .= '<th width="8%"> <span><label>{_value}</label></span></th>';
		$html .= '<th width="50%"> <span><label>{_interpretation}</label></span></th>';
		$html .= '<th width="15%"> <span><label>{_alert}</label></span></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach ($airesponse as $ai) {
			$html .= '<tr valign="top">';
			$html .= '<td class="no-wrap">' . $ai['code'] . '</td>';
			$html .= '<td class="no-wrap"> <span>' . $ai['acceptable_ranges'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $ai['value'] . '</span></td>';
			$html .= '<td class="wrap">' . $ai['interpretation'] . '</td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . ($ai['alert']=='Abnormal' ? 'darkred' : 'green') . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $ai['alert'] . '</span></span></td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div>';
		$html .= '<div>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
}
?>