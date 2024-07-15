<?php
/**
 * xlsx class to perform the majority of importing and exporting functions within sheel.
 *
 * @package      sheel\xlsx
 * @version      1.0.0.0
 * @author       sheel
 */
class xlsx
{
	protected $sheel;

	function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	function size_xlsx_to_db($data, $staffs, $customer_ref, $userid = 0, $bulk_id = 0)
	{
		if ($bulk_id > 0) {
			foreach ($data as $t) {
				echo $staffs[$t[0]];
				$staffdetails = explode('|', $staffs[trim($t[0])]);
				$stmt = $this->sheel->db->prepare("
				INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
				(id, staffcode, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
				VALUES (
					NULL,
					?, ?, ?, ?, ?, ?, ?, ?, '[No Errors]',?, '0', ?, ?
				)
				ON DUPLICATE KEY UPDATE
					staffcode = " . DB_PREFIX . "bulk_tmp_sizes.staffcode,
					positioncode = " . DB_PREFIX . "bulk_tmp_sizes.positioncode,
					departmentcode = " . DB_PREFIX . "bulk_tmp_sizes.departmentcode,
					fit = " . DB_PREFIX . "bulk_tmp_sizes.fit,
					cut = " . DB_PREFIX . "bulk_tmp_sizes.cut,
					size = " . DB_PREFIX . "bulk_tmp_sizes.size,
					type = " . DB_PREFIX . "bulk_tmp_sizes.type,
					customerno = " . DB_PREFIX . "bulk_tmp_sizes.customerno,
					dateuploaded = " . DB_PREFIX . "bulk_tmp_sizes.dateuploaded,
					uploaded = " . DB_PREFIX . "bulk_tmp_sizes.uploaded,
					user_id = " . DB_PREFIX . "bulk_tmp_sizes.user_id,
					bulk_id = " . DB_PREFIX . "bulk_tmp_sizes.bulk_id
			");

				$stmt->bind_param(
					"sssssssssss",
					trim($t[0]),
					trim($staffdetails[0]),
					trim($staffdetails[1]),
					trim($t[1]),
					trim($t[2]),
					trim($t[3]),
					trim($t[4]),
					trim($customer_ref),
					$this->sheel->db->escape_string(DATETODAY),
					$userid,
					$bulk_id
				);
				$stmt->execute();

			}

		} else {
			foreach ($data as $t) {
				$stmt = $this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
				(id, staffcode, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
				VALUES (
					NULL,
					'" . $t['staffcode'] . "',
					'" . $t['positioncode'] . "',
					'" . $t['departmentcode'] . "',
					'" . $t['fit'] . "',
					'" . $t['cut'] . "',
					'" . $t['size'] . "',
					'" . $t['type'] . "',
					'" . $customer_ref . "',
					'" . $t['error'] . "',
					'" . $this->sheel->db->escape_string(DATETODAY) . "',
					'0',
					'" . $userid . "',
					'" . $bulk_id . "'
				)
				ON DUPLICATE KEY UPDATE
				staffcode = " . DB_PREFIX . "bulk_tmp_sizes.staffcode,
				positioncode = " . DB_PREFIX . "bulk_tmp_sizes.positioncode,
				departmentcode = " . DB_PREFIX . "bulk_tmp_sizes.departmentcode,
				fit = " . DB_PREFIX . "bulk_tmp_sizes.fit,
				cut = " . DB_PREFIX . "bulk_tmp_sizes.cut,
				size = " . DB_PREFIX . "bulk_tmp_sizes.size,
				type = " . DB_PREFIX . "bulk_tmp_sizes.type,
				customerno = " . DB_PREFIX . "bulk_tmp_sizes.customerno,
				errors = " . DB_PREFIX . "bulk_tmp_sizes.errors,
				dateuploaded =" . DB_PREFIX . "bulk_tmp_sizes.dateuploaded,
				uploaded = " . DB_PREFIX . "bulk_tmp_sizes.uploaded,
				user_id = " . DB_PREFIX . "bulk_tmp_sizes.user_id,
				bulk_id = " . DB_PREFIX . "bulk_tmp_sizes.bulk_id
			");
			}
		}
	}
	function measurement_xlsx_to_db($data, $staffs, $customer_ref, $userid = 0, $bulk_id = 0)
	{
		foreach ($data as $t) {
			$staffdetails = explode('|', $staffs[$t[0]]);
			$this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "bulk_tmp_measurements
                        (id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
                        VALUES (
                        NULL,
						'" . $t[0] . "',
						'" . $t[3] . "',
						'" . $staffdetails[0] . "',
						'" . $staffdetails[1] . "',
						'" . $t[4] . "',
						'" . $t[5] . "',
						'" . $customer_ref . "',
						'',
                        '" . $this->sheel->db->escape_string(DATETODAY) . "',
                        '0',
						'" . $userid . "',
						'" . $bulk_id . "')
                    ", 0, null, __FILE__, __LINE__);
		}
	}

	function staff_xlsx_to_db($data, $customer_ref, $nextid, $userid = 0, $bulk_id = 0)
	{
		foreach ($data as $t) {
			$position = explode('>', $t[2]);
			$department = explode('>', $t[3]);
			$this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "bulk_tmp_staffs
                        (id, code, name, gender, positioncode, departmentcode, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
                        VALUES (
                        NULL,
                        '" . $customer_ref . '-' . $nextid . "',
						'" . $t[0] . "',
						'" . $t[1] . "',
						'" . $position[0] . "',
						'" . $department[0] . "',
						'" . $customer_ref . "',
						'',
                        '" . $this->sheel->db->escape_string(DATETODAY) . "',
                        '0',
						'" . $userid . "',
						'" . $bulk_id . "')
                    ", 0, null, __FILE__, __LINE__);
			$nextid++;

		}
	}
	function isEmptyRow($row)
	{
		foreach ($row as $cell) {
			if (null !== $cell)
				return false;
		}
		return true;
	}

	function clean_cache()
	{
		$files = glob(DIR_TMP_XLSX . '*.xlsx');
		if (!empty($files) and is_array($files) and count($files) > 0) {
			foreach ($files as $file) {
				if (!empty($file) and $file != '' and file_exists($file)) {
					@unlink($file);
				}
			}
		}
		return 'xlsx->clean_cache(), ';
	}
}
?>