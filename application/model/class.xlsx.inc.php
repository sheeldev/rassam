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
				$sqlcat = $this->sheel->db->query("
					SELECT id
					FROM " . DB_PREFIX . "size_type_categories		
					WHERE name = '" . trim($t[6]) . "'
					LIMIT 1
					");
				$rowcat = $this->sheel->db->fetch_array($sqlcat, DB_ASSOC);
				$sqlallrec = $this->sheel->db->query("
				SELECT code
				FROM " . DB_PREFIX . "size_types		
				WHERE categoryid = '" . $rowcat['id'] . "'
				");
				$staffdetails = explode('|', $staffs[trim($t[0])]);
				while ($resallrec = $this->sheel->db->fetch_array($sqlallrec, DB_ASSOC)) {
					$stmt = $this->sheel->db->prepare("
						INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
						(id, staffcode, staffname, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
						VALUES (
							NULL,
							?, ?, ?, ?, ?, ?, ?, ?, ?, '[No Errors]',?, '0', ?, ?
						)
						ON DUPLICATE KEY UPDATE
							staffcode = " . DB_PREFIX . "bulk_tmp_sizes.staffcode,
							staffname = " . DB_PREFIX . "bulk_tmp_sizes.staffname,
							positioncode = " . DB_PREFIX . "bulk_tmp_sizes.positioncode,
							departmentcode = " . DB_PREFIX . "bulk_tmp_sizes.departmentcode,
							fit = " . DB_PREFIX . "bulk_tmp_sizes.fit,
							cut = " . DB_PREFIX . "bulk_tmp_sizes.cut,
							size = " . DB_PREFIX . "bulk_tmp_sizes.size,
							type = " . DB_PREFIX . "bulk_tmp_sizes.type,
							customerno = " . DB_PREFIX . "bulk_tmp_sizes.customerno,
							dateuploaded = " . DB_PREFIX . "bulk_tmp_sizes.dateuploaded,
							uploaded = 0,
							user_id = " . DB_PREFIX . "bulk_tmp_sizes.user_id,
							bulk_id = " . DB_PREFIX . "bulk_tmp_sizes.bulk_id
					");
					$stmt->bind_param(
						"ssssssssssss",
						trim($t[0]),
						trim($t[1]),
						trim($staffdetails[0]),
						trim($staffdetails[1]),
						trim($t[3]),
						trim($t[4]),
						trim($t[5]),
						trim($resallrec['code']),
						trim($customer_ref),
						$this->sheel->db->escape_string(DATETODAY),
						$userid,
						$bulk_id
					);
					$stmt->execute();
				}
			}

		} else {
			foreach ($data as $t) {
				$stmt = $this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
					(id, staffcode, staffname, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
					VALUES (
						NULL,
						'" . $t['staffcode'] . "',
						'" . $t['staffname'] . "',
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
					staffname = " . DB_PREFIX . "bulk_tmp_sizes.staffname,
					positioncode = " . DB_PREFIX . "bulk_tmp_sizes.positioncode,
					departmentcode = " . DB_PREFIX . "bulk_tmp_sizes.departmentcode,
					fit = " . DB_PREFIX . "bulk_tmp_sizes.fit,
					cut = " . DB_PREFIX . "bulk_tmp_sizes.cut,
					size = " . DB_PREFIX . "bulk_tmp_sizes.size,
					type = " . DB_PREFIX . "bulk_tmp_sizes.type,
					customerno = " . DB_PREFIX . "bulk_tmp_sizes.customerno,
					errors = " . DB_PREFIX . "bulk_tmp_sizes.errors,
					dateuploaded =" . DB_PREFIX . "bulk_tmp_sizes.dateuploaded,
					uploaded = 0,
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
			$measurement = explode('>', $t[3]);
			if($t[4] != '0' && $t[4] != ''){
				$this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "bulk_tmp_measurements
                        (id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
                        VALUES (
                        NULL,
						'" . $t[0] . "',
						'" . $measurement[0] . "',
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