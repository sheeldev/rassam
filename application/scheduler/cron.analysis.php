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
        $isontime = 0;
        $country = '';
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
        $days = 0;
        while ($resEvents = $this->sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
                $resData = json_decode($resEvents['eventdata'], true);
                if ($resEvents['isend'] == '1' and $resEvents['isarchive'] == '0') {
                        $days = intval($resData['promisedDeliveryDate'] == '0001-01-01' ? '0' : $this->sheel->common->getBusinessDays(date('Y-m-d', $resEvents['eventtime']), $resData['promisedDeliveryDate']));
                        if ($days < 0) {
                                $isontime = 0;
                        }
                        else {
                                $isontime = 1;
                        }
                } 
                if ($totalquantity == 0 || $totalquantity <> $resData['TotalQuantity']) {
                        $totalquantity = $resData['totalQuantity'];
                }
                if (isset($resData['quoteNo']) and $resData['quoteNo'] != '' and $quoteexist == 0) {
                        $quoteexist = 1;
                }
                if ($resEvents['checkpointcode'] == 'AVO') {
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
        $sqlAssemblies = $this->sheel->db->query("
                SELECT e.eventid, e.systemid, e.eventtime, e.reference, e.eventdata, e.entityid, e.companyid, e.createdtime, e.eventtime, e.checkpointid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.topic='Assembly' AND e.reference = '" . $resanalysis['analysisreference'] . "'
                ORDER BY eventtime DESC, eventid DESC");
        $previousassembly = '';
        $assemblies = array();
        while ($resAssemblies = $this->sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
                static $processedAssemblies = array();
                $resAssemblyData = json_decode($resAssemblies['eventdata'], true);
                $resAssemblies['assemblynumber'] = $resAssemblyData['assemblyNo'];
                $resAssemblies['itemcategory'] = $resAssemblyData['itemCategory'];
                $resAssemblies['customername'] = $resAssemblyData['sellToCustomerName'];
                $resAssemblies['description'] = $resAssemblyData['description'];
                $resAssemblies['itemno'] = $resAssemblyData['itemNo'];
                $resAssemblies['quantity'] = $resAssemblyData['quantity'];
                $resAssemblies['mo'] = $resAssemblyData['erManufacturingOrderNo'];
                $resAssemblies['createdby'] = $resAssemblyData['createdBy'];
                $resAssemblies['modifiedby'] = $resAssemblyData['modifiedUser'];
                $resAssemblies['createdat'] = $this->sheel->common->print_date($resAssemblyData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
                $resAssemblies['eventtime'] = $this->sheel->common->print_date($resAssemblyData['systemModifiedAt'], 'Y-m-d H:i:s', 0, 0, '');

                if ($resAssemblyData['itemCategory'] != '' and $previousassembly != $resAssemblyData['assemblyNo'] and !isset($processedAssemblies[$resAssemblyData['assemblyNo']])) {
                        $processedAssemblies[$resAssemblyData['assemblyNo']] = true;
                        $previousassembly = $resAssemblyData['assemblyNo'];
                        $assemblies[] = $resAssemblies;
                }
        }
        usort($assemblies, function ($a, $b) {
                return strcmp($a['assemblynumber'], $b['assemblynumber']);
        });
        foreach ($assemblies as $assembly) {
                $sqlanalysisrecord = $this->sheel->db->query("
                        SELECT analysisrecordid
                        FROM " . DB_PREFIX . "analysis_records
                        WHERE recordreference = '" . $assembly['assemblynumber'] . "'
                        LIMIT 1
                ");
                if ($this->sheel->db->num_rows($sqlanalysisrecord) == 0) {
                        $this->sheel->db->query("
                                INSERT INTO " . DB_PREFIX . "analysis_records
                                (systemid, createdtime, modifiedtime, recordfor, recordidentifier, recordreference, category, totalquantity, lastcheckpoint, entityid, companyid)
                                VALUES(
                                '" . $this->sheel->db->escape_string($assembly['systemid']) . "',
                                " . strtotime($assembly['createdat']) . ",
                                " . strtotime($assembly['eventtime']) . ",
                                'assembly',
                                '" . $assembly['reference'] . "',
                                '" . $assembly['assemblynumber'] . "',
                                '" . $assembly['itemcategory'] . "',
                                '" . $assembly['quantity'] . "',
                                '" . $assembly['checkpointid'] . "',
                                '" . $assembly['entityid'] . "',
                                '" . $assembly['companyid'] . "'
                        )", 0, null, __FILE__, __LINE__);
                } else {
                        $sqlanalysisrecordupdate = $this->sheel->db->query("
                                SELECT analysisrecordid
                                FROM " . DB_PREFIX . "analysis_records
                                WHERE recordreference = '" . $assembly['assemblynumber'] . "' and lastcheckpoint = '" . $assembly['checkpointid'] . "'
                                LIMIT 1
                        ");
                        if ($this->sheel->db->num_rows($sqlanalysisrecordupdate) == 0) {
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "analysis_records
                                        SET lastcheckpoint = '" . $assembly['checkpointid'] . "',
                                        totalquantity = '" . $assembly['quantity'] . "'
                                        WHERE recordreference = '" . $assembly['assemblynumber'] . "'
                                ");
                        }
                }
        }
        $this->sheel->db->query("
                UPDATE " . DB_PREFIX . "analysis
                SET totalquantity = '" . $totalquantity . "',
                countrycode = '" . $country . "',
                issmall = '" . $issmall . "',
                ismedium = '" . $ismedium . "',
                islarge = '" . $islarge . "',
                hasquote = '" . $quoteexist . "',
                isactive = '" . $activeorder . "',
                isontime = '" . $isontime . "'
                WHERE analysisid = '" . $resanalysis['analysisid'] . "'
        ");
        $sqlLastEvent = $this->sheel->db->query("
                SELECT e.eventid, COALESCE(cs.sequence,'0') as sequence, cs.isend, cs.isarchive
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
                LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and e.entityid = cs.fromid
                WHERE e.topic='Order' AND e.reference = '" . $resanalysis['analysisreference'] . "' and  cs.isend = '1' and cs.isarchive = '0'
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