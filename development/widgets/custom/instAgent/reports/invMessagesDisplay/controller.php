<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
class invMessagesDisplay extends Widget {
	function __construct () {
		parent::__construct();
        $this->attrs['show_last_from_investigation'] = new Attribute('Show last request from investigators', 'BOOL', 'Flag to show last request from CFPB', false);
        
        $this->CI->load->helper('config');
    }
	function generateWidgetInformation () {
        $this->info['notes'] =  'Display investigation messsages';
	}

	function getData () {
        // lookup investigation object associated with incident
		$this->CI->load->model('custom/instagent_model');
        
        // first find incident id associated with complaint
        $compId = getUrlParm('comp_id');
        $compObj = $this->CI->instagent_model->getComplaint($compId);
        $i_id = $compObj->Incident->ID;
		
        $this->CI->load->model('custom/dr_model');
        // search for investigation object by incident id
        $invObj = $this->CI->dr_model->getInvestigationByIncidentComplaint($i_id, $compId);
        $this->data['investigation_id'] = $invObj->ID;
        
        // retrieve all investigation messages
        $messages = $this->CI->dr_model->getMessages($invObj->ID, getSetting('MSG_PERMISSION_VIS_AGENT_ID'));
        $messages = array_reverse($messages);

        foreach ($messages as $key => $obj) {
            // get file attachments for each message
            unset($attachments);
            //lazy loading so temp access attachments
            $temp = $this->CI->dr_model->getAttachments($obj->FileAttachments);
            $attachments = $this->CI->dr_model->getAttachments($obj->FileAttachments);
            if (count($attachments) > 0)
                $obj->attachments = $attachments;
            
            if ($obj->visibility->ID == getSetting('MSG_PERMISSION_VIS_AGENT_ID')) {
                // look for last response from investigation
                if ($this->attrs['show_last_from_investigation'] && is_null($this->data['show_last_from_investigation']))
                    if ($obj->acct_id)
                        $this->data['show_last_from_investigation'] = $obj;
                $this->data['results'][$key] =  $obj;
            } 
        }
    }
}

