<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

use RightNow\Connect\v1_2 as RNCPHP;

class InvestigationDetailForm extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->CI->load->helper('label_helper');

        $this->attrs['error_label'] = new Attribute('Custom error label', 'STRING', 'Descriptive label for error message', '');
        $this->attrs['co_status'] = new Attribute("Company status array", 'STRING', "Array of company status labels", getLabel('CO_STATUS_ARRAY'));
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = "Display case details or directed request messaging.";
    }

    function getData()
    {
        // get the company status
        $this->CI->load->model('custom/instagent_model');

        // first find incident id associated with complaint
        $compId = getUrlParm('comp_id');
        $compObj = $this->CI->instagent_model->getComplaint($compId);
        $incident = $compObj->Incident;
        $this->data['js']['bank_statuses'] = $incident->CustomFields->bank_statuses->LookupName;

        $this->data['js']['credit_reporting'] = ( $incident->Product->Name == 'Credit reporting' ) ? true : false;
        $this->data['js']['display_credit_reporting_field'] = false;

        if( $this->data['js']['credit_reporting'] )
        {
            switch( $incident->Category->Parent->ID )
            {
                case getSetting( 'CRDT_INCORRECT_INFO_CAT_ID' ):
                    $this->data['js']['display_credit_reporting_field'] = true;
                case getSetting( 'CRDT_COMPANY_INVESTIGATION_CAT_ID' ):
                    $this->data['js']['display_credit_reporting_field'] = true;
                break;
                default:
                    $this->data['js']['display_credit_reporting_field'] = false;
                break;
            }
        }

        if ($incident->CustomFields->comp_explanation_of_closure ||
            $incident->CustomFields->agency_name)
            $this->data['js']['is_explain'] = true;
        if ($incident->CustomFields->comp_describe_relief)
            $this->data['js']['is_relief'] = true;
        if ($incident->CustomFields->comp_provide_a_response ||
            $incident->CustomFields->redirect_explanation)
            $this->data['js']['is_response'] = true;
        if ($incident->CustomFields->cfpb_explanation_of_closure ||
            $incident->CustomFields->cfpb_describe_relief ||
            $incident->CustomFields->cfpb_provide_a_response)
            $this->data['js']['is_delinquent'] = true;
            
       
        
        // 2014.12.24 - Eric Gottesman - if company_public_comment is empty, then we'll show the form
        $status = $this->data['attrs']['co_status'];
        $bank_status = $this->data['js']['bank_statuses'];

        // after 180 days, company cannot add a comment - so need to calculate the "sent to company" date and add 180
        $now = new DateTime("now");
        $sent_to_company = $incident->CustomFields->sent_bank_date;      
        $sent_to_company_date = new DateTime("@$sent_to_company"); 
        $cutoff_date = new DateTime("@$sent_to_company");
        
        // FOR PRODUCTION: 
        $cutoff_date->add(new DateInterval('P180D')); // 180 day window 
        // FOR TEST:
        //$cutoff_date->add(new DateInterval('PT4H')); // 4 hour window

        if ($now > $cutoff_date) {  $too_late = true; } else { $too_late = false; } 

        // cannot add comments to cases that were created before the launch date       
        $launch_date_cfg = RNCPHP\Configuration::fetch(CUSTOM_CFG_COMPANY_COMMENT_LAUNCH);  // should return the launch date for company comment
        $launch_date_value = $launch_date_cfg->Value;
        $launch_date = new DateTime("$launch_date_value");
        $incident_created = new DateTime("@$incident->CreatedTime");       
        if ($sent_to_company_date < $launch_date) { $too_early = true; } else { $too_early = false; }

        // check for past due date
        if ($incident->CustomFields->past_due_flag == 1 && $incident->CustomFields->no_response_flag == 1) { $no_response = true; } else { $no_response = false; }      

        // check for alerted cfpb
        if ($bank_status == $status['CO_STATUS_ALERTED_CFPB'] 
            || $incident->CustomFields->company_status_1->LookupName == $status['CO_STATUS_ALERTED_CFPB'] 
            || $incident->CustomFields->company_status_2->LookupName == $status['CO_STATUS_ALERTED_CFPB'] ) 
            {
                $alerted_cfpb = true; 
            } else {
                $alerted_cfpb = false; 
            }      

            $this->data['js']['debug'] = "now:[{$now->format('Y-m-d H:i:s')}]<br>" .
                                     "sent_to_company_date: [{$sent_to_company_date->format('Y-m-d H:i:s')}]<br>" . 
                                     "cutoff_date:[{$cutoff_date->format('Y-m-d H:i:s')}]<br>" . 
                                     "too_late:[$too_late]<br><br>" . 
                                     "launch_date: [{$launch_date->format('Y-m-d H:i:s')}]<br>" .
                                     "incident_created: [{$incident_created->format('Y-m-d H:i:s')}]<br>" . 
                                     "too_early:[$too_early]<br><br>" .
                                     "no_response:[$no_response]<br><br>" .
                                     "alerted_cfpb:[$alerted_cfpb]<br><br>" 
                                     ;
        
        
        if ( 
            (is_null($incident->CustomFields->company_public_comment) || $incident->CustomFields->company_public_comment=="") 
            && (!$too_late) && (!$too_early) && (!$no_response) && (!$alerted_cfpb)
            && ( $this->isNonAdminResponse($bank_status, $status)  ) 
        ) 
        {
            $this->data['js']['display_company_comment_form'] = true;
            
            if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'list_archive') !== false)
            {
                $this->data['js']['return_to_page'] = "list_archive"; // return to archive tab
            }
            else 
            {
                $this->data['js']['return_to_page'] = "list_active"; // this means we came from active tab, so we will return there
            }
                                
        }
        else {
            $this->data['js']['display_company_comment_form'] = false;  
            $this->data['js']['display_company_comment'] = (($incident->CustomFields->company_public_comment == "") ? false : true);    
        }
         
        /* 2014.12.24 - Eric Gottesman - Only show consent status after a non-admin status has been selected */
        /* 2015.02.03 - Eric Gottesman - Decision has been made to NEVER show consent status. Keeping code around in case they change this decision..
        if  ( $this->isNonAdminResponse($bank_status, $status) )
        {
            $this->data['js']['display_consent_status'] = ($incident->CustomFields->consent_status->LookupName == 'Consent provided') ? 'Y' : 'N';
        } else {
            $this->data['js']['display_consent_status'] = '';
        }
        */
        $this->data['js']['display_consent_status'] = '';
           
        // Debt Collection - look for thread indication of "creditor incident"
        $this->data['dc']['isDebtCollection'] = false;
        $this->data['dc']['showDCDebtOwnerFields'] = true;
        if (isset($incident->Product->Parent->Name))
        {
            if ($incident->Product->Parent->Name == 'Debt collection')
            {
                $this->data['dc']['isDebtCollection'] = true;
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

    // These are the conditions under which we display the company public comment and consumer consent fields
    function isNonAdminResponse($bank_status, $status) {

        
        if (   $bank_status == $status['CO_STATUS_CLOSED'] 
            || $bank_status == $status['CO_STATUS_CLOSED_W_EXPLANATION']
            || $bank_status == $status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']
            || $bank_status == $status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF'] 
            || $bank_status == $status['CO_STATUS_INFO_PROVIDED'] 
            // || $bank_status == $status['CO_STATUS_ALERTED_CFPB'] 
            // || $bank_status == $status['CO_STATUS_DELINQUENT_RESPONSE']     
           ) 
       {
            return true; 
       }
       else 
       {
            return false;
       }
    }
}