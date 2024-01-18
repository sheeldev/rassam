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
	function get_order_details($orderno)
	{
		$quoteexist = false;
		$sqlEvents = $this->sheel->db->query("
                SELECT e.eventidentifier, e.eventtime as max_eventtime, e.eventdata as eventdata, e.reference as reference, e.checkpointid, e.companyid, c.code as checkpointcode, c.message as checkpointmessage, c.topic as color, comp.name as companyname, comp.isfactory as isfactory
                FROM " . DB_PREFIX . "events e
                LEFT JOIN " . DB_PREFIX . "checkpoints c ON e.checkpointid = c.checkpointid
				LEFT JOIN " . DB_PREFIX . "companies comp ON e.companyid = comp.company_id
                WHERE e.eventfor = 'customer' AND e.reference = '" . $orderno . "' and e.topic='Order'
                ORDER BY max_eventtime DESC
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
				while ($resQuoteEvent = $this->sheel->db->fetch_array($sqlQuoteEvents, DB_ASSOC)) {
					$resQuoteEventData = json_decode($resQuoteEvent['eventdata'], true);
					$resQuoteEvent['customername'] = $resQuoteEventData['sellToCustomerName'];
					$resQuoteEvent['createdby'] = $resQuoteEventData['createdUser'];
					$resQuoteEvent['createdat'] = $this->sheel->common->print_date($resQuoteEventData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
					$resQuoteEvent['eventtime'] = $this->sheel->common->print_date($resQuoteEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
					$events[] = $resQuoteEvent;
				}
			}
			$resEvent['customername'] = $resEventData['sellToCustomerName'];
			$resEvent['createdby'] = $resEventData['createdUser'];
			$resEvent['createdat'] = $this->sheel->common->print_date($resEventData['systemCreatedAt'], 'Y-m-d H:i:s', 0, 0, '');
			$resEvent['eventtime'] = $this->sheel->common->print_date($resEvent['max_eventtime'], 'Y-m-d H:i:s', 0, 0, '');
			$events[] = $resEvent;

		}
		usort($events, function ($a, $b) {
			return strtotime($b['eventtime']) - strtotime($a['eventtime']);
		});
		$html .= '<div id="assmeblies_status">';
		$html .= '<div class="draw-card__section">';
		$html .= '<div class="table-wrapper">';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="is-sortable" width="8%"><span><label>{_number}</label></span><span class="litegray right prl-6 pt-12 uc"><img src="'. $this->sheel->config['imgcdn'] .'v5/ico_info.png" width="18" height="18" alt="{_info}" onclick="return display_info_message(\'\',\'this is a test\');" /></span></th>';
		$html .= '<th width="8%"> <span><label>{_account}</label></span> </th>';
		$html .= '<th width="20%"> <span><label>{_name}</label></span></th>';
		$html .= '<th width="20%"> <span><label>{_status}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_created_by}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_created_date}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_checkpoint}</label></span></th>';
		$html .= '<th width="14%"> <span><label>{_time}</label></span></th>';
		$html .= '<th width="10%"> <span><label>{_source}</label></span></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach ($events as $event) {
			$html .= '<tr valign="top">';
			$html .= '<td>' . $event['reference'] . '</td>';
			$html .= '<td> <span>' . $event['eventidentifier'] . '</span></td>';
			$html .= '<td> <span>' . $event['customername'] . '</span></td>';
			$html .= '<td> <span class="draw-status__badge subdued draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $event['checkpointmessage'] . '</span></span></td>';
			$html .= '<td class="no-wrap">' . $event['createdby'] . '</td>';
			$html .= '<td class="status no-wrap">' . $event['createdat'] . '</td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . $event['color'] . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $event['checkpointcode'] . '</span></span></td>';
			$html .= '<td class="status no-wrap">' . $event['eventtime'] . '</td>';
			$html .= '<td class="status no-wrap"><span class="draw-status__badge ' . ($event['isfactory'] ? 'green' : $event['color']) . ' draw-status__badge--adjacent-chevron"><span class="draw-status__badge-content">' . $event['companyname'] . '</span></span></td>';
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