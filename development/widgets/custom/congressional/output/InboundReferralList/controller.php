<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class InboundReferralList extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->CI->load->model('custom/InboundReferral_model');

        $this->attrs['incident_id'] = new Attribute( 'Incident ID', 'INT', 'The ID of an incident object. Optional. Uses the referral_id url parameter if not provided. Defaults to zero.', 0 );
        $this->attrs['comp_id'] = new Attribute( 'Complaint ID', 'INT', 'The ID of a ComplaintAgainstOrg object. Optional. Should be used instead of Incident ID or Referral ID. Defaults to zero.', 0 );
}

    function generateWidgetInformation()
    {
        $this->info['notes'] = "Display case details section";
    }

    function getData()
    {
        $referId = getUrlParm('referral_id');
        $this->data['activeReferrals'] = array();
        $incidentId = ( $this->data['attrs']['incident_id'] > 0 ) ? $this->data['attrs']['incident_id'] : null;
        $compId = empty($this->data['attrs']['comp_id'])?null:$this->data['attrs']['comp_id'];

        if( !isset( $incidentId ) )
        {
            $incident = $this->CI->InboundReferral_model->getIncident( $referId );
            $incidentId = $incident->ID;
        }

if( empty($incidentId) && $compId){
        $compObj = $this->CI->instagent_model->getComplaint($compId);
        $incident = $compObj->Incident;
        $incidentId = $incident->ID;
}

        if( $incidentId )
        {
            $this->data['activeReferrals'] = $this->CI->InboundReferral_model->getActiveReferrals( $incidentId );

            // Each referral should have one ore more file attachment associated with it. These require special handling to get from the FAS.
            for( $counter = 0; $counter < count( $this->data['activeReferrals'] ); $counter++ )
            {
                $this->data['attachments'][$counter] = $this->CI->InboundReferral_model->getAttachments( $this->data['activeReferrals'][$counter]->FileAttachments );
            }
        }
    }
}