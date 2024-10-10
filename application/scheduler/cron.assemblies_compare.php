<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();
$checkpoint = 0;
$sqlcheckpoint = $this->sheel->db->query("
                        SELECT checkpointid
                        FROM " . DB_PREFIX . "checkpoints
                        WHERE type = 'Assembly' AND triggeredon = '0-Out'
                        LIMIT 1
                        ");
if ($this->sheel->db->num_rows($sqlcheckpoint) > 0) {
        $rescheckpoint = $this->sheel->db->fetch_array($sqlcheckpoint, DB_ASSOC);
        $checkpoint = $rescheckpoint['checkpointid'];
}
$sqlcompany = $this->sheel->db->query("
        SELECT *
        FROM " . DB_PREFIX . "companies
        WHERE status = 'active'
        ");
$searchcondition = '';
if ($this->sheel->db->num_rows($sqlcompany) > 0) {

        while ($rescompanies = $this->sheel->db->fetch_array($sqlcompany, DB_ASSOC)) {

                $searchcondition = " AND e.entityid = '" . $rescompanies['company_id'] . "'";
                $additioncondition = "AND EXISTS (
                                SELECT 1
                                FROM " . DB_PREFIX . "events e2
                                LEFT JOIN " . DB_PREFIX . "checkpoints c2 ON e2.checkpointid = c2.checkpointid
                                WHERE e2.reference = e.reference
                                AND c2.type = 'Order'
                                AND c2.triggeredon = 'Active'
                        )";
                $sqlEvents = $this->sheel->db->query("
                        SELECT e.reference, e.eventidentifier, e.eventdata as eventdata
                        FROM " . DB_PREFIX . "events e
                        LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                        INNER JOIN (
                        SELECT reference, MAX(eventtime) as max_eventtime
                        FROM " . DB_PREFIX . "events
                        WHERE eventfor = 'customer' and topic='Order' 
                        GROUP BY reference
                        ) r ON e.reference = r.reference AND e.eventtime = r.max_eventtime
                        WHERE e.eventfor = 'customer' and e.topic='Order' 
                        $searchcondition
                        GROUP BY e.reference
                        ORDER BY createdtime DESC");

                while ($resEvent = $this->sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
                        $assemblies = [];
                        $resEventData = json_decode($resEvent['eventdata'], true);
                        $tqty = (isset($resEventData['totalQuantity']) && $resEventData['totalQuantity'] > 0) ? $resEventData['totalQuantity'] : 0;
                        $sqlAssemblies = $this->sheel->db->query("
                                SELECT eventdata, checkpointid
                                FROM " . DB_PREFIX . "events e
                                WHERE eventfor = 'customer' AND eventidentifier = '" . $resEvent['eventidentifier'] . "' AND reference = '" . $resEvent['reference'] . "' and topic='Assembly'
                                ORDER BY eventtime DESC , eventid DESC
                                ");
                        $assemblycount = 0;
                        $aqty = 0;
                        if ($this->sheel->db->num_rows($sqlAssemblies) > 0) {
                                static $processedAssemblies = [];
                                while ($resAssemblies = $this->sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
                                        $resAssemblyData = json_decode($resAssemblies['eventdata'], true);
                                        if (!isset($processedAssemblies[$resAssemblyData['assemblyNo']]) && $resAssemblies['checkpointid'] != $checkpoint) {
                                                $aqty += intval($resAssemblyData['quantity']);
                                                $processedAssemblies[$resAssemblyData['assemblyNo']] = true;
                                                $assemblies[] = $resAssemblyData['assemblyNo'];
                                        } else if (!isset($processedAssemblies[$resAssemblyData['assemblyNo']]) && $resAssemblies['checkpointid'] == $checkpoint) {
                                                $processedAssemblies[$resAssemblyData['assemblyNo']] = true;
                                        } else if (isset($processedAssemblies[$resAssemblyData['assemblyNo']]) && $resAssemblies['checkpointid'] == $checkpoint) {
                                                $aqty -= intval($resAssemblyData['quantity']);
                                                if (($key = array_search($resAssemblyData['assemblyNo'], $assemblies)) !== false) {
                                                        unset($assemblies[$key]);
                                                }
                                        }
                                }
                        }
                        if ($aqty > $tqty && $tqty > 0) {
                                //echo $resEvent['reference'] . ' - ' . $tqty . ' - ' . $aqty . "\n";
                                $orders[] = [
                                        'reference' => $resEvent['reference'],
                                        'assemblies' => $assemblies,
                                        'tqty' => $tqty,
                                        'aqty' => $aqty
                                ];
                        }



                }

        }
        foreach ($orders as $order) {
                echo 'Reference: ' . $order['reference'] . ', ';
                echo 'TQTY: ' . $order['tqty'] . ', ';
                echo 'AQTY: ' . $order['aqty'] . ', ';
                echo 'Assemblies: ' . count($order['assemblies']) . "\n";
            }
}
if (!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.assemblies_compare.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>