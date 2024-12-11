<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();

$sqlanalysis = $this->sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "analysis
                WHERE isfinished = '0' or isarchived = '0'
        ");
$ordersizebrackets = explode("|", $this->sheel->config['ordermagnitude']);
while ($resanalysis = $this->sheel->db->fetch_array($sqlanalysis, DB_ASSOC)) {
        $totalquantity = 0;
        $issmall = 0;
        $ismedium = 0;
        $islarge = 0;
        $quoteexist = 0;
        $activeorder = 0;
        $country='';
        if ($resanalysis['hasquote'] == '1') {
                $quoteexist = 1;
        }
        $sqlEvents = $this->sheel->db->query("
                SELECT e.eventid, e.eventtime, e.eventdata, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.topic='Order' AND e.reference = '" . $resanalysis['analysisreference'] . "'
                ORDER BY eventtime ASC, eventid ASC");
        while ($resEvents = $this->sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
                if ($resEvents['reference'] == 'SO-AVR24-01641') {
                        echo $resData['sellToCountryRegionCode'];
                }
                $resData = json_decode($resEvents['eventdata'], true);
                $resCheckpointCode = $resEvents['checkpointcode'];
                if ($totalquantity == 0 || $totalqunatity <> $resData['TotalQuantity']) {
                        $totalquantity = $resData['totalQuantity'];
                }
                if (isset($resData['quoteNo']) and $resData['quoteNo'] != '' and $quoteexist == 0) {
                        $quoteexist = 1;
                }
                if ($resCheckpointCode == 'AVO') {
                        $activeorder = 1;
                }
                if (isset($resData['sellToCountryRegionCode']) and $resData['sellToCountryRegionCode'] != '') {
                        $country = $resData['sellToCountryRegionCode'];
                }
        }
        if ($totalquantity >= 0 && $totalquantity < $ordersizebrackets[0]) {
                $issmall = 1;
        } else if ($totalquantity >= $ordersizebrackets[0] && $totalquantity < $ordersizebrackets[1]) {
                $ismedium = 1;
        } else if ($totalquantity >= $ordersizebrackets[1]) {
                $islarge = 1;
        }
        $this->sheel->db->query("
                UPDATE " . DB_PREFIX . "analysis
                SET totalquantity = '" . $totalquantity . "',
                countrycode = '" . $country . "',
                issmall = '" . $issmall . "',
                ismedium = '" . $ismedium . "',
                islarge = '" . $islarge . "',
                hasquote = '" . $quoteexist . "',
                isactive = '" . $activeorder . "'
                WHERE analysisid = '" . $resanalysis['analysisid'] . "'
        ");








        $sqlLastEvent = $this->sheel->db->query("
                SELECT e.eventid, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.topic='Order' AND e.reference = '" . $resanalysis['analysisreference'] . "' and  cs.isend = '1'
                ORDER BY eventtime DESC, eventid DESC LIMIT 1");
        if ($this->sheel->db->num_rows($sqlLastEvent) == 1) {
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "analysis
                        SET isfinished = '1'
                        WHERE analysisid = '" . $resanalysis['analysisid'] . "'
                ");
        }
        $sqlLastEvent = $this->sheel->db->query("
                SELECT e.eventid, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.topic='Order' AND e.reference = '" . $resanalysis['analysisreference'] . "' and  cs.isarchive = '1'
                ORDER BY eventtime DESC, eventid DESC LIMIT 1");

        if ($this->sheel->db->num_rows($sqlLastEvent) == 1) {
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "analysis
                        SET isarchived = '1'
                        WHERE analysisid = '" . $resanalysis['analysisid'] . "'
                ");
        }
}
if (!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.analysis.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>