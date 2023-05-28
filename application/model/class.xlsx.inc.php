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
	function size_xlsx_to_db($data, $staffs,  $customer_ref, $userid = 0, $bulk_id = 0)
	{
		if ($bulk_id > 0) {
			foreach ($data as $t) {
				$staffdetails = explode('|',$staffs[$t[0]]);
				$this->sheel->db->query("
							INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
							(id, staffcode, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
							VALUES (
							NULL,
							'" . $t[0] . "',
							'" . $staffdetails[0] . "',
							'" . $staffdetails[1] . "',
							'" . $t[1] . "',
							'" . $t[2] . "',
							'" . $t[3] . "',
							'" . $t[4] . "',
							'" . $customer_ref . "',
							'',
							'" . $this->sheel->db->escape_string(DATETODAY) . "',
							'0',
							'" . $userid . "',
							'" . $bulk_id . "')
						", 0, null, __FILE__, __LINE__);
			}
		}
		else {
			foreach ($data as $t) {
				$this->sheel->db->query("
							INSERT INTO " . DB_PREFIX . "bulk_tmp_sizes
							(id, staffcode, positioncode, departmentcode, fit, cut, size, type, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
							VALUES (
							NULL,
							'" . $t['staffcode'] . "',
							'" . $t['positioncode']  . "',
							'" . $t['departmentcode']  . "',
							'" . $t['fit']  . "',
							'" . $t['cut']  . "',
							'" . $t['size']  . "',
							'" . $t['type']  . "',
							'" . $customer_ref . "',
							'" . $t['error']  . "',
							'" . $this->sheel->db->escape_string(DATETODAY) . "',
							'0',
							'" . $userid . "',
							'" . $bulk_id . "')
						", 0, null, __FILE__, __LINE__);
			}
		}
		
	}
	function measurement_xlsx_to_db($data, $staffs,  $customer_ref, $userid = 0, $bulk_id = 0)
	{
		foreach ($data as $t) {
			$staffdetails = explode('|',$staffs[$t[0]]);
			$this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "bulk_tmp_measurements
                        (id, staffcode, measurementcategory, positioncode, departmentcode, mvalue, uom, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
                        VALUES (
                        NULL,
						'" . $t[0] . "',
						'" . $t[1] . "',
						'" . $staffdetails[0] . "',
						'" . $staffdetails[1] . "',
						'" . $t[2] . "',
						'" . $t[3] . "',
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
			$this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "bulk_tmp_staffs
                        (id, code, name, gender, positioncode, departmentcode, customerno, errors, dateuploaded, uploaded, user_id, bulk_id)
                        VALUES (
                        NULL,
                        '" . $customer_ref .'-'. $nextid . "',
						'" . $t[0] . "',
						'" . $t[1] . "',
						'" . $t[2] . "',
						'" . $t[3] . "',
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
	function isEmptyRow($row) {
		foreach($row as $cell){
			if (null !== $cell) return false;
		}
		return true;
	}	
}
?>
