<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();

$sqlanalysis = $this->sheel->db->query("
                SELECT *
                FROM " . DB_PREFIX . "analysis
                WHERE isfinished = '0' and isarchived = '0'
        ");
$ordersizebrackets = explode("|",$this->sheel->config['ordermagnitude']);
while ($resanalysis = $this->sheel->db->fetch_array($sqlanalysis, DB_ASSOC)) {
        $totalquantity = 0;
        $issmall = 0;
        $ismedium = 0;
        $islarge = 0;
        $quoteexist = 0;
        $sqlEvents = $this->sheel->db->query("
                SELECT e.eventid, e.eventtime, e.eventdata, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.reference = '" . $resanalysis['analysisreference'] . "'
                ORDER BY eventtime ASC, eventid ASC");
        while ($resEvents = $this->sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
                $resData = json_decode($resEvents['eventdata'], true);
                if ($totalquantity == 0 || $totalqunatity <> $resData['TotalQuantity']) {
                        $totalquantity = $resData['totalQuantity'];
                }
                if (isset($resData['quoteNo']) and $resData['quoteNo'] != '') {
                        $quoteexist = 1;
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
                issmall = '" . $issmall . "',
                ismedium = '" . $ismedium . "',
                islarge = '" . $islarge . "',
                hasquote = '" . $quoteexist . "'
                WHERE analysisid = '" . $resanalysis['analysisid'] . "'
        ");
        $sqlLastEvent = $this->sheel->db->query("
                SELECT e.eventid, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.reference = '" . $resanalysis['analysisreference'] . "'
                ORDER BY eventtime DESC, eventid DESC LIMIT 1");
        $resLastEvent = $this->sheel->db->fetch_array($sqlLastEvent, DB_ASSOC);
        if ($resLastEvent['isend'] == '1') {
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "analysis
                        SET isfinished = '1'
                        WHERE analysisid = '" . $resanalysis['analysisid'] . "'
                ");
        } else if ($resLastEvent['isarchive'] == '1') {
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