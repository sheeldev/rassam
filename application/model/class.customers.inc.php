<?php

class customers
{
    protected $sheel;

	function __construct($sheel)
	{
			$this->sheel = $sheel;
	}
    function get_customer_details($custid)
    {
        $uid = 0;
        $sql = $this->sheel->db->query("
				SELECT customer_ref, customername, subscriptionid
				FROM " . DB_PREFIX . "customers
				WHERE customer_id = '" . $custid . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            return array(
                'customer_ref' => $res['customer_ref'],
                'customername' => $res['customername'],
                'subscriptionid' => $res['subscriptionid']
            );
        } else {
            return array(
                'customer_ref' => '{_staff}',
                'customername' => '{_staff}',
                'subcriptionid' => '0'
            );
        }

    }
}
?>