<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class ComplaintDetailSection extends Widget
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

        // first find incident id associated with complaint
        $compId = getUrlParm('comp_id');
        $compObj = $this->CI->instagent_model->getComplaint($compId);
        $incident = $compObj->Incident;
        $this->data['i_id']=$incident->ID;

	// Lowercase Product and Product Parent Names for comparison
        if( isset( $incident->Product->Name ) )
        {
	    $lc_prod_name = trim(strtolower($incident->Product->Name));
	    $lc_prod_parent_name = (!empty($incident->Product->Parent) ? trim(strtolower($incident->Product->Parent->Name)) : "");
	}

        // On Behalf Fields
        $this->data['showOnBehalfFields'] = ($incident->CustomFields->onbehalf_someone) ? true : false;

        // Credit Reporting
        $this->data['js']['credit_reporting'] = ($incident->Product->Name == 'Credit reporting') ? true : false;
        $this->data['js']['display_credit_reporting_field_checkbox'] = false;
		$this->data['js']['display_credit_reporting_field'] = false;
        $this->data['cr'] = ($this->data['js']['credit_reporting']) ? 'true' : 'false';

        if ($this->data['js']['credit_reporting'])
        {
            switch( $incident->Category->Parent->ID )
            {
                case getSetting( 'CRDT_INCORRECT_INFO_CAT_ID' ) :
                case getSetting( 'CRDT_COMPANY_INVESTIGATION_CAT_ID' ) :
                    $this->data['js']['display_credit_reporting_field_checkbox'] = true;
                    break;
                default :
                    $this->data['js']['display_credit_reporting_field_checkbox'] = false;
                    break;
            }
			$this->data['js']['display_credit_reporting_field'] = true;
        }

        // Money Transfers
        if (isset($incident->Product->Parent->Name))
        {
            $this->data['mt']['display_money_trans_fields'] = ($incident->Product->Parent->Name == 'Money transfers') ? true : false;

            $this->data['showTransMethod'] = ($incident->CustomFields->transfer_method->ID) ? true : false;
            $this->data['showTransMethodSendAgent'] = ($incident->CustomFields->transfer_method->ID == getSetting('MONEY_TRANS_TRANS_METHOD_IN_PERSON_MENU_ID')) ? true : false;
            $this->data['showReceiptMethod'] = ($incident->CustomFields->receipt_method->ID) ? true : false;
        }

        // Debt Collections
        if (isset($incident->Product->Parent->Name))
        {
            $this->data['dc']['display_dc_fields'] = ($incident->Product->Parent->Name == 'Debt collection') ? true : false;

            if ($this->data['dc']['display_dc_fields'])
            {
                $prod_name = $incident->Product->Name;
                $cat_name = $incident->Category->Name;
                $cat_parent_name = $incident->Category->Parent->Name;

                // for complainant phone number called by company, spec says:
                // "Display only when Communication tactics subissues 1, 2, and 5 are selected."
                if ($cat_parent_name == 'Communication tactics' &&
                    (
                        stripos($cat_name, 'Frequent or repeated', 0) === 0 ||
                        stripos($cat_name, 'Called outside of', 0) === 0 ||
                        stripos($cat_name, 'Called after sent', 0) === 0
                    )
                )
                {
                    $this->data['dc']['display_complaintant_phone_called'] = true;
                }
                else {
                    $this->data['dc']['display_complaintant_phone_called'] = false;
                }
            }
        }

        // Payday and Other Financial Services Lending
        if( isset( $incident->Product->Name ) )
        {
            $this->data['pd']['display_pd_fields'] = ($lc_prod_name == 'payday loan') ? true : false;
        }

	// Consumer Loan	
        if( isset( $incident->Product->Name ) )
        {
            $this->data['cl']['display_cl_fields'] = ($lc_prod_parent_name == 'consumer loan') ? true : false;
        }

        // figure out which kind of incident we are showing
        $this->findIncidentType($incident);

        // see if we have any creditor info
        $this->data['dc']['creditorInfoSet'] = !empty($incident->DebtCollection->CreditorCompanyName) ? true : false;

    }

    private function findIncidentType($incident)
    {
        // Debt Collection - look for thread indication of "creditor incident"
        // in case you need to create a thread, create a NOTE with this exact text:
        //     AUTOMATED NOTE: This is a Debt Collection "Creditor" case.

        $this->data['dc']['showDCDebtOwnerFields'] = true;
        if (isset($incident->Product->Parent->Name))
        {
            if ($incident->Product->Parent->Name == 'Debt collection')
            {
                $isCreditorInc = false;
                if (isset($incident->Threads) && count($incident->Threads))
                {
                    foreach ($incident->Threads as $thread)
                    {
                        if ($thread->EntryType->LookupName == 'Note' && $thread->Text == getLabel('DC_IS_CREDITOR_THREAD_NOTE'))
                        {
                            $this->data['dc']['showDCDebtOwnerFields'] = false;
                            break;
                        }
                    }
                }
            }
        }
    }


}
