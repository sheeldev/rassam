<?php
/**
 * common_order.
 *
 * @package      sheel\Common\Order
 * @version      1.0.0.0
 * @author       sheel
 */
class common_order extends common
{
	function get_order_details($orderno, $customerno)
	{
		$quoteexist = false;
		$warningmessage = '';
		$warningcount = 0;
		$html='';
		$sqlEvents = $this->sheel->db->query("
                SELECT e.eventid, e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, e.companyid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, comp.name as companyname, comp.isfactory as isfactory
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
				LEFT JOIN " . DB_PREFIX . "companies comp ON e.companyid = comp.company_id
                WHERE e.eventfor = 'customer' AND e.reference = '" . $orderno . "' AND e.eventidentifier = '" . $customerno . "' and e.topic='Order'
                ORDER BY max_eventtime DESC, eventid DESC
            ");
		while ($resEvent = $this->sheel->db->fetch_array($sqlEvents, DB_ASSOC)) {
			$resEventData = json_decode($resEvent['eventdata'], true);
			if (isset($resEventData['quoteNo']) and $resEventData['quoteNo'] != '' and !$quoteexist) {
				$quoteexist = true;
				$sqlQuoteEvents = $this->sheel->db->query("
						SELECT e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, e.companyid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, comp.name as companyname, comp.isfactory as isfactory
						FROM " . DB_PREFIX . "events e
						LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
						LEFT JOIN " . DB_PREFIX . "companies comp ON e.companyid = comp.company_id
						WHERE e.eventfor = 'customer' AND e.reference = '" . $resEventData['quoteNo'] . "' and e.topic='Quote'
						ORDER BY max_eventtime DESC
					");
				if ($this->sheel->db->num_rows($sqlQuoteEvents) > 0) {
					while ($resQuoteEvent = $this->sheel->db->fetch_array($sqlQuoteEvents, DB_ASSOC)) {
						$resQuoteEventData = json_decode($resQuoteEvent['eventdata'], true);
						$resQuoteEvent['customername'] = $resQuoteEventData['sellToCustomerName'];
						$resQuoteEvent['createdby'] = $resQuoteEventData['createdUser'];
						$resQuoteEvent['modifiedby'] = $resQuoteEventData['modifiedUser'];
						$resQuoteEvent['createdat'] = $this->sheel->common->print_date($resQuoteEventData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
						$resQuoteEvent['eventtime'] = $this->sheel->common->print_date($resQuoteEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
						$events[] = $resQuoteEvent;
					}
				} else {
					$warningcount++;
					$warningmessage .= '<b>' . $warningcount . '. </b>{_quote_not_found}<br>';
				}
			}
			$resEvent['icno'] = $resEventData['icCustomerSONo'] != '' ? $resEventData['no'] : '';
			$resEvent['customername'] = $resEventData['sellToCustomerName'];
			$resEvent['createdby'] = $resEventData['createdUser'];
			$resEvent['modifiedby'] = $resEventData['modifiedUser'];
			$resEvent['createdat'] = $this->sheel->common->print_date($resEventData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
			$resEvent['eventtime'] = $this->sheel->common->print_date($resEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
			$events[] = $resEvent;
		}
		usort($events, function ($a, $b) {
			return strtotime($b['eventtime']) - strtotime($a['eventtime']);
		});
		$html .= '<div><h1><span class="' . ($this->sheel->config["template_textdirection"] == 'ltr' ? 'right' : 'left') . ' bold"></span>' . $orderno . ' / <span class="breadcrumb"><a href="javascript:;" onclick="showAssemblyDetails(\'' . $orderno . '\', \'' . $customerno . '\')">{_assemblies}</a></span></h1>';
		$html .= '<div class="hr-20-0-20-0"></div>';
		$html .= '</div>';
		$html .= '<div id="assmeblies_status">';
		$html .= '<div class="draw-card__section">';
		$html .= '<div class="table-wrapper bulk-action-div" style="">';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="is-sortable" width="8%"><span><label>{_number}</label></span>' . ($warningmessage != '' ? '<span class="litegray right prl-6 pt-12 uc"><img src="' . $this->sheel->config['imgcdn'] . 'v5/img_warning.png" width="18" height="18" alt="{_info}" onclick="return display_info_message(\'\',\'' . $warningmessage . '\');" /></span>' : '') . '</th>';
		$html .= '<th width="8%"> <span><label>{_account}</label></span> </th>';
		$html .= '<th width="20%"> <span><label>{_name}</label></span></th>';
		
		$html .= '<th width="10%"> <span><label>{_updated_by}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_date}</label></span></th>';
		
		$html .= '<th width="14%"> <span><label>{_time}</label></span></th>';
		$html .= '<th width="20%"> <span><label>{_status}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_source}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_checkpoint}</label></span></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach ($events as $event) {
			$html .= '<tr valign="top">';
			$html .= '<td class="no-wrap">' . $event['reference'] . '</td>';
			$html .= '<td class="no-wrap"> <span>' . $event['eventidentifier'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $event['customername'] . ($event['icno'] != '' ? ' <span class="badge badge--complete fw-strong-black" style="max-width:150px;white-space: nowrap;text-overflow: ellipsis">' . $event['icno'] . '</span>':'') . '</span></td>';
			$html .= '<td class="no-wrap">' . $event['modifiedby'] . '</td>';
			$html .= '<td class="status no-wrap">' . $event['createdat'] . '</td>';
			$html .= '<td class="status no-wrap">' . $event['eventtime'] . '</td>';
			$html .= '<td class="status no-wrap"> <span class="badge badge--complete fw-strong-black" style="max-width:150px;white-space: nowrap;text-overflow: ellipsis">' . $event['checkpointmessage'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . ($event['isfactory'] ? 'purple' : '') . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $event['companyname'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . $event['color'] . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $event['checkpointcode'] . '</span></span></td>';
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

	function get_assembly_details($orderno, $customerno)
	{
		$html='';
		$sqlAssemblies = $this->sheel->db->query("
			SELECT e.eventid, e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, e.companyid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, comp.name as companyname, comp.isfactory as isfactory
			FROM " . DB_PREFIX . "events e
			LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
			LEFT JOIN " . DB_PREFIX . "companies comp ON e.companyid = comp.company_id
			WHERE e.eventfor = 'customer' AND e.reference = '" . $orderno . "' AND e.eventidentifier = '" . $customerno . "' and e.topic='Assembly'
			ORDER BY max_eventtime DESC , eventid DESC
		");
		$previousassembly = '';
		while ($resAssemblies = $this->sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
			$resAssemblyData = json_decode($resAssemblies['eventdata'], true);
			$resAssemblies['assemblynumber'] = $resAssemblyData['no'];
			$resAssemblies['customername'] = $resAssemblyData['sellToCustomerName'];
			$resAssemblies['description'] = $resAssemblyData['description'];
			$resAssemblies['itemno'] = $resAssemblyData['itemNo'];
			$resAssemblies['quantity'] = $resAssemblyData['quantity'];
			$resAssemblies['mo'] = $resAssemblyData['erManufacturingOrderNo'];
			$resAssemblies['createdby'] = $resAssemblyData['createdBy'];
			$resAssemblies['modifiedby'] = $resAssemblyData['modifiedUser'];
			$resAssemblies['createdat'] = $this->sheel->common->print_date($resAssemblyData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
			$resAssemblies['eventtime'] = $this->sheel->common->print_date($resAssemblies['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
			static $processedAssemblies = array();
			if ($previousassembly != $resAssemblyData['assemblyNo'] and !isset($processedAssemblies[$resAssemblyData['assemblyNo']])) {
				$processedAssemblies[$resAssemblyData['assemblyNo']] = true;
				$previousassembly = $resAssemblyData['assemblyNo'];
				$assemblies[] = $resAssemblies;
			}
		}
		usort($assemblies, function ($a, $b) {
			return strtotime($b['eventtime']) - strtotime($a['eventtime']);
		});
		$html .= '<div><h1><span class="' . ($this->sheel->config["template_textdirection"] == 'ltr' ? 'right' : 'left') . ' bold"></span><span class="breadcrumb"><a href="javascript:;" onclick="showOrderDetails(\'' . $orderno . '\', \'' . $customerno . '\')">' . $orderno . '</a></span> / {_assemblies}</h1>';
		$html .= '<div class="hr-20-0-20-0"></div>';
		$html .= '</div>';
		$html .= '<div id="assmeblies_status">';
		$html .= '<div class="draw-card__section">';
		$html .= '<div class="table-wrapper bulk-action-div" style="">';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="is-sortable"><span><label>{_number}</label></span></th>';
		$html .= '<th> <span><label>{_order}</label></span> </th>';
		$html .= '<th> <span><label>{_manufacturing_order}</label></span></th>';
		$html .= '<th> <span><label>{_item_code}</label></span></th>';
		$html .= '<th> <span><label>{_item_name}</label></span></th>';
		$html .= '<th> <span><label>{_quantity}</label></span></th>';
		$html .= '<th> <span><label>{_updated_by}</label></span></th>';
		$html .= '<th> <span><label>{_date}</label></span></th>';
		$html .= '<th> <span><label>{_time}</label></span></th>';
		$html .= '<th> <span><label>{_status}</label></span></th>';
		$html .= '<th> <span><label>{_source}</label></span></th>';
		$html .= '<th> <span><label>{_checkpoint}</label></span></th>';
		$html .= '<th><span><label>{_updates}</label></span></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach ($assemblies as $assembly) {
			$html .= '<tr valign="top">';
			$html .= '<td class="no-wrap">' . $assembly['assemblynumber'] . '</td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['reference'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['mo'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['itemno'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['description'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['quantity'] . '</span></td>';
			$html .= '<td class="no-wrap">' . $assembly['createdby'] . '</td>';
			$html .= '<td class="status no-wrap">' . $assembly['modifiedby'] . '</td>';
			$html .= '<td class="status no-wrap">' . $assembly['eventtime'] . '</td>';
			$html .= '<td class="status no-wrap"> <span class="badge badge--complete fw-strong-black" style="max-width:150px;white-space: nowrap;text-overflow: ellipsis">' . $assembly['checkpointmessage'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . ($assembly['isfactory'] ? 'purple' : '') . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $assembly['companyname'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . $assembly['color'] . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $assembly['checkpointcode'] . '</span></span></td>';
			$html .= '<td><div id="toggleOrder"><i class="fa fa-ellipsis-h" onclick="showAssemblyScans(\'' . $assembly['assemblynumber'] . '\',\'' . $assembly['reference'] . '\', \'' . $assembly['eventidentifier'] . '\')" style="cursor: pointer;"></i></div></td>';
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

	function get_assembly_scans($assemblyno, $orderno, $customerno)
	{
		$html='';
		$sqlAssemblies = $this->sheel->db->query("
			SELECT e.eventid, e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, e.companyid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, comp.name as companyname, comp.isfactory as isfactory
			FROM " . DB_PREFIX . "events e
			LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
			LEFT JOIN " . DB_PREFIX . "companies comp ON e.companyid = comp.company_id
			WHERE e.eventfor = 'customer' AND e.reference = '" . $orderno . "' AND e.eventidentifier = '" . $customerno . "' and e.topic='Assembly'
			ORDER BY max_eventtime DESC, eventid DESC
		");
		while ($resAssemblies = $this->sheel->db->fetch_array($sqlAssemblies, DB_ASSOC)) {
			$resAssemblyData = json_decode($resAssemblies['eventdata'], true);
			$resAssemblies['assemblynumber'] = $resAssemblyData['no'];
			$resAssemblies['customername'] = $resAssemblyData['sellToCustomerName'];
			$resAssemblies['description'] = $resAssemblyData['description'];
			$resAssemblies['itemno'] = $resAssemblyData['itemNo'];
			$resAssemblies['mo'] = $resAssemblyData['erManufacturingOrderNo'];
			$resAssemblies['createdby'] = $resAssemblyData['createdBy'];
			$resAssemblies['modifiedby'] = $resAssemblyData['modififedBy'];
			$resAssemblies['createdat'] = $this->sheel->common->print_date($resAssemblyData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
			$resAssemblies['eventtime'] = $this->sheel->common->print_date($resAssemblies['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');

			if ($assemblyno == $resAssemblyData['assemblyNo']) {
				$assemblies[] = $resAssemblies;
			}
		}
		usort($assemblies, function ($a, $b) {
			return strtotime($b['eventtime']) - strtotime($a['eventtime']);
		});
		$html .= '<div><h1><span class="' . ($this->sheel->config["template_textdirection"] == 'ltr' ? 'right' : 'left') . ' bold"></span><span class="breadcrumb"><a href="javascript:;" onclick="showOrderDetails(\'' . $orderno . '\', \'' . $customerno . '\')">' . $orderno . '</a> / <a href="javascript:;" onclick="showAssemblyDetails(\'' . $orderno . '\', \'' . $customerno . '\')">{_assemblies}</a> / </span>'.$assemblyno.'</h1>';
		$html .= '<div class="hr-20-0-20-0"></div>';
		$html .= '</div>';
		$html .= '<div id="assmeblies_status">';
		$html .= '<div class="draw-card__section">';
		$html .= '<div class="table-wrapper bulk-action-div" style="" style="">';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th> <span><label>{_item_code}</label></span></th>';
		$html .= '<th> <span><label>{_item_name}</label></span></th>';
		$html .= '<th> <span><label>{_manufacturing_order}</label></span></th>';
		$html .= '<th> <span><label>{_updated_by}</label></span></th>';
		$html .= '<th> <span><label>{_date}</label></span></th>';
		$html .= '<th> <span><label>{_time}</label></span></th>';
		$html .= '<th> <span><label>{_status}</label></span></th>';
		$html .= '<th> <span><label>{_source}</label></span></th>';
		$html .= '<th> <span><label>{_checkpoint}</label></span></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach ($assemblies as $assembly) {
			$html .= '<tr valign="top">';
			$html .= '<td class="no-wrap"> <span>' . $assembly['itemno'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['description'] . '</span></td>';
			$html .= '<td class="no-wrap"> <span>' . $assembly['mo'] . '</span></td>';
			$html .= '<td class="no-wrap">' . $assembly['modifiedby'] . '</td>';
			$html .= '<td class="status no-wrap">' . $assembly['createdat'] . '</td>';
			$html .= '<td class="status no-wrap">' . $assembly['eventtime'] . '</td>';
			$html .= '<td class="status no-wrap"> <span class="badge badge--complete fw-strong-black" style="max-width:150px;white-space: nowrap;text-overflow: ellipsis">' . $assembly['checkpointmessage'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . ($assembly['isfactory'] ? 'purple' : '') . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $assembly['companyname'] . '</span></span></td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . $assembly['color'] . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $assembly['checkpointcode'] . '</span></span></td>';
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