<?php
/**
 * common_sizingrule.
 *
 * @package      sheel\Common\Sizingrule
 * @version      1.0.0.0
 * @author       sheel
 */
class common_sizingrule extends common
{
	function construct_category_pulldown($fieldname = 'typecategory', $selected = '',  $extracss,  $showpleaseselect = false)
	{
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= ' ' . ((empty($extracss)) ? 'class="draw-select"' : $extracss) . '>';

		if ($showpleaseselect) {
			$html .= '<option value="" readonly="readonly">{_category}</option><!--<option value="">{_category}</option>-->';
		}
		$sql = $this->sheel->db->query("
            SELECT id, code, name
            FROM " . DB_PREFIX . "size_type_categories
            ORDER BY  code ASC
            ");
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$html .= '<option value="' . $row['id'] . '"';
				$html .= ($row['id'] == $selected) ? ' selected="selected"' : '';
				$html .= '>' . $row['name'] . '</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
	function construct_gender_pulldown($gendercode = 'Male', $fieldname = 'gender', $disabletype = false, $extracss, $typefieldname = 'type', $typesdivid = 'type-wrapper', $showpleaseselect = false)
	{
		$gender = array('Male' => '{_male}', 'Female' => '{_female}');
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disabletype == false)
			? ' onchange="print_types(\'' . $typefieldname . '\', \'' . $fieldname . '\', \'' . $typesdivid . '\');"'
			: '') . ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '">';

		if ($showpleaseselect) {
			$html .= '<option value="" readonly="readonly">{_gender}</option><!--<option value="">{_gender}</option>-->';
		}
		foreach ($gender as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= ($key == $gendercode) ? ' selected="selected"' : '';
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	function construct_type_checkbox($gender, $allselected = false, $returnid = false)
	{
		if ($gender == 'Female') {
			$gender = 'F';
		} else if ($gender == 'Male') {
			$gender = 'M';
		}
		$html = '';
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, code
			FROM " . DB_PREFIX . "size_types
			WHERE (gender = '" . $gender . "' OR gender='U')
			    AND needsize = '1'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {

			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$html .= '<div class="draw-input-wrapper draw-input-wrapper--inline">';
				if ($returnid) {
					$html .= '<input type="checkbox" name="type[]" id="type" class="draw-checkbox" value="' . $res['id'] . '" ';
				} else {
					$html .= '<input type="checkbox" name="type[]" id="type" class="draw-checkbox" value="' . $res['code'] . '" ';
				}
				$html .= $allselected ? 'checked="checked"' : '';
				$html .= '> ' . $res['code'];
				$html .= '<span class="draw-checkbox--styled"></span></div>';
				$html .= '<br>';
			}
		}
		return $html;
	}
	function construct_impact_pulldown($impact = 'Fit', $fieldname = 'impact', $disablevalue = false, $extracss, $valuefieldname = 'impactvalue', $valuedivid = 'value-wrapper', $showpleaseselect = false)
	{
		$impactvalue = array('Fit' => '{_fit}', 'Cut' => '{_cut}', 'Size' => '{_size}');
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disablevalue == false)
			? ' onchange="print_impactvalues(\'' . $fieldname . '\', \'' . $valuefieldname . '\', \'' . $valuedivid . '\');"'
			: '') . ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '">';

		if ($showpleaseselect) {
			$html .= '<option value="" readonly="readonly">{_value}</option><!--<option value="">{_value}</option>-->';
		}
		foreach ($impactvalue as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= ($key == $impactvalue) ? ' selected="selected"' : '';
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	function construct_impactvalue_pulldown($impact = 'Fit', $fieldname = 'impactvalue', $defaultvalue = '', $disabled = false, $showpleaseselect = false, $extracss = '', $returnarray = false)
	{
		$defaulcompany = '';
		$searchcondition = '$orderby=code asc';
		$tempresult = $result = array();
		$sqldefault = $this->sheel->db->query("
		SELECT bc_code
		FROM " . DB_PREFIX . "companies 
		WHERE isdefault='1' 
		LIMIT 1");
		if ($this->sheel->db->num_rows($sqldefault) > 0) {
			while ($res = $this->sheel->db->fetch_array($sqldefault, DB_ASSOC)) {
				$defaulcompany = $res['bc_code'];
			}
		}
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '"' . ($disabled ? ' readonly="readonly"' : '') . '>';

		if (!empty($impact)) {
			switch ($impact) {
				case 'Fit': {
					if (!$this->sheel->dynamics->init_dynamics('erFits', $defaulcompany)) {
						$html .= '<option value="">{_error}</option>';
					}
					$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$tempresult = $apiResponse->getData();
					} else {
						$html .= '<option value="">{_error}</option>';
					}
					break;
				}
				case 'Cut': {
					if (!$this->sheel->dynamics->init_dynamics('erCuts', $defaulcompany)) {
						$html .= '<option value="">{_error}</option>';
					}
					$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$tempresult = $apiResponse->getData();
					} else {
						$html .= '<option value="">{_error}</option>';
					}
					break;
				}
				case 'Size': {
					if (!$this->sheel->dynamics->init_dynamics('erSizes', $defaulcompany)) {
						$html .= '<option value="">{_error}</option>';
					}
					$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
					if ($apiResponse->isSuccess()) {
						$tempresult = $apiResponse->getData();
					} else {
						$html .= '<option value="">{_error}</option>';
					}
					break;
				}
			}
		}
		foreach ($tempresult as $key => $value) {

			foreach ($value as $key1 => $value1) {
				if ($key1 == 'code') {
					$code = $value1;
				}
				if ($key1 == 'name') {
					$name = $value1;
				}
			}
			$result += [$code => $code . ' > ' . $name];
		}
		foreach ($result as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= (isset($defaultvalue) and !empty($defaultvalue) and ($key == $defaultvalue)) ? ' selected="selected"' : '';
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select>';
		if ($returnarray) {
			return $result;
		} else {
			return $html;
		}
	}

	function construct_uom_pulldown($fieldname = 'uom', $defaultvalue = '', $disabled = false, $showpleaseselect = false, $extracss = '')
	{
		$defaulcompany = '';
		$searchcondition = '$orderby=code asc';
		$tempresult = $result = array();
		$sqldefault = $this->sheel->db->query("
		SELECT bc_code
		FROM " . DB_PREFIX . "companies 
		WHERE isdefault='1' 
		LIMIT 1");
		if ($this->sheel->db->num_rows($sqldefault) > 0) {
			while ($res = $this->sheel->db->fetch_array($sqldefault, DB_ASSOC)) {
				$defaulcompany = $res['bc_code'];
			}
		}
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '"' . ($disabled ? ' readonly="readonly"' : '') . '>';
		if (!$this->sheel->dynamics->init_dynamics('erUOM', $defaulcompany)) {
			$html .= '<option value="">{_error}</option>';
		}
		$apiResponse = $this->sheel->dynamics->select('?' . $searchcondition);
		if ($apiResponse->isSuccess()) {
			$tempresult = $apiResponse->getData();
		} else {
			$html .= '<option value="">{_error}</option>';
		}
		foreach ($tempresult as $key => $value) {

			foreach ($value as $key1 => $value1) {
				if ($key1 == 'code') {
					$code = $value1;
				}
				if ($key1 == 'name') {
					$name = $value1;
				}
			}
			$result += [$code => $code . ' > ' . $name];
		}
		foreach ($result as $key => $value) {
			$html .= '<option value="' . $key . '"';
			$html .= (isset($defaultvalue) and !empty($defaultvalue) and ($key == $defaultvalue)) ? ' selected="selected"' : '';
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	function get_default_uom($measurement = '')
	{
		$uom = '';
		$defaultuoms = json_decode($this->sheel->config['defaultuom'], true);
		foreach ($defaultuoms as $key => $value) {
			if ($key == $measurement) {
				$uom = $value;
			}
		}
		return $uom;
	}
	function get_smplx_measurement($measurement = '')
	{
		$smplx = '';
		$defaultuoms = json_decode($this->sheel->config['smplxmeasurements'], true);
		foreach ($defaultuoms as $key => $value) {
			if ($key == $measurement) {
				$smplx = $value;
			}
		}
		return $smplx;
	}
}
?>