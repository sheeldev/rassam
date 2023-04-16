<?php
class dynamics_activities 
{
    function bulkdelete($ids = array(), $entity, $companyid)
    {
        
        $allerrors = $successids = $failedids = $display = '';
        $count = 0;
        $display = '{_delete}';
        foreach ($ids as $inc => $entityid) {
            $this->dobulkdelete($entityid, $entity, $companyid);
            if ($response === true) {
                $successids .= "$entityid~";
                $count++;
            } else {
                $failedids .= "$entityid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = $display;
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_record}' : '{_records}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? $entity . ' deleted successfully' : $entity . ' delete failed'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array(
            'success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''),
            'errors' => $allerrors,
            'successids' => $successids,
            'failedids' => $failedids
        );
    }

    function dobulkdelete($id, $entity, $companyid)
    {
        $companycode = $this->sheel->admincp_customers->get_company_name($companyid, true);
        die ($companycode );
        $this->sheel->dynamics->init_dynamics($entity, $companycode);
        $deleteResponse =$this->sheel->dynamics->delete($id);
        if($deleteResponse->isSuccess()) {
            return true;
        }
        else {
            return $deleteResponse->getErrorMessage();
        }
    }


}
?>