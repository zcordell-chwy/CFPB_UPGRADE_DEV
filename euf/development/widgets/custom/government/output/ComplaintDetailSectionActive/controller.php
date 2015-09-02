<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class ComplaintDetailSectionActive extends Widget
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
        $this->CI->load->model('custom/contactpermissions_model');

        $compId = getUrlParm('comp_id');
        $compObj = $this->CI->instagent_model->getComplaint($compId);
        $incident = $compObj->Incident;

        $this->data['i_id']= $incident->ID;
        $this->data['comp_status_archive'] = $incident->CustomFields->comp_status_archive->ID;

        // get contact permissions
        $contactID = $this->CI->contactpermissions_model->getProfileContactID();
        $orgID = $this->CI->contactpermissions_model->getProfileOrganizationID();
        $this->data['userType'] = $this->CI->contactpermissions_model->userTypeByContactIDandOrganizationID($contactID, $orgID);

        // Lowercase Product and Product Parent Names for comparison
        if( isset( $incident->Product->Name ) )
        {
            $lc_prod_name = trim(strtolower($incident->Product->Name));
            $lc_prod_parent_name = (!empty($incident->Product->Parent) ? trim(strtolower($incident->Product->Parent->Name)) : "");
        }

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

    }
}
