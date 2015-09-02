<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class CompanyNameDisplay extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label'] = new Attribute(getMessage(FIELD_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_FORM_FIELD_VALUE_LBL), '{default label}');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  "Widget that displays the company name assigned to a case. If not blank or default, will display the Organization name associated with an incident by way of its ComplaintAgainstOrg object. Otherwise it will display the name of the company entered by the consumer.";
        $this->parms['comp_id'] = new UrlParam( 'Complaint ID', 'comp_id', false, "ID of a ComplaintAgainstOrg custom object.", 'comp_id/7');

    }

    function getData()
    {
        $this->CI->load->model( 'custom/InstAgent_model' );
        $complaint = $this->CI->InstAgent_model->getComplaint( getUrlParm('comp_id') );
        $this->data['value'] = ( $complaint->Organization->ID == getSetting( 'PENDING_COMPANY_MATCH_ORG_ID' ) ) ? $complaint->Incident->CustomFields->cc_co_name : $complaint->Organization->LookupName;
    }
}

