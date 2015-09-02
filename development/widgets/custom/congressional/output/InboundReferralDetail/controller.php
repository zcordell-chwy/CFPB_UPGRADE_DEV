<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class InboundReferralDetail extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->CI->load->helper('label_helper');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = "Display case details section";
    }

    function getData()
    {
	$this->CI->load->model('custom/instagent_model');
	$compId = getUrlParm('comp_id');
	$compObj = $this->CI->instagent_model->getComplaint($compId);
	$incident = $compObj->Incident;
	
	// On Behalf Fields
        $this->data['showOnBehalfFields'] = ($incident->CustomFields->onbehalf_someone) ? true : false;
	
	// Money Transfers
	if(isset($incident->Product->Parent->Name))
	{
		$this->data['mt']['display_money_trans_fields'] = ($incident->Product->Parent->Name == 'Money transfers') ? true : false;
		
		$this->data['showTransMethod'] = ($incident->CustomFields->transfer_method->ID) ? true : false;
                $this->data['showTransMethodSendAgent'] = ($incident->CustomFields->transfer_method->ID == getSetting('MONEY_TRANS_TRANS_METHOD_IN_PERSON_MENU_ID')) ? true : false;
                $this->data['showReceiptMethod'] = ($incident->CustomFields->receipt_method->ID) ? true : false;
	}
	
	// Debt Collections
        if(isset($incident->Product->Parent->Name))
        {
                $this->data['dc']['display_dc_fields'] = ($incident->Product->Parent->Name == 'Debt collection') ? true : false;
        }
    }
}
