<?
/*
 * RedirectedCase Model
 * Thomas Woodham
 * 5/15/2012
 *
 * Model to handle the various components of the redirected case requirement of Company Portal 3.1.
 */

use RightNow\Connect\v1 as RNCPHP;
require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
initConnectAPI();

class RedirectedCase_model extends Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model( 'custom/Report_model2' );
        $this->load->helper( 'config_helper' );
    }

    function sendForm( $data, $complaintID )
    {
        // First things first - is there anything to be recorded here?
        $assignedOrgID = null;
        $assignedOrgReason = null;

        for( $counter = 0; $counter < count( $data ); $counter++ )
        {
            if( $data[$counter]->table == 'orgcomplainthandler' && $data[$counter]->name == 'authorizedcomplainthandler' && $data[$counter]->value > 0 )
            {
                $assignedOrgID = intval( $data[$counter]->value );

                // This is a dummy field that could cause other processes to choke - get rid of it.
                unset( $data[$counter] );
            }

            if( $data[$counter]->table == 'incidents' && $data[$counter]->name == 'redirect_explanation' )
                $assignedOrgReason = $data[$counter]->value;

            if( isset( $assignedOrgID ) && isset( $assignedOrgReason ) )
                break;
        }

        if( isset( $assignedOrgID ) && isset( $assignedOrgReason ) )
        {
            try
            {
                $CI = get_instance();

                $complaintAgainstOrg = RNCPHP\CO\ComplaintAgainstOrg::fetch( $complaintID );
                $incident = $complaintAgainstOrg->Incident;
                $assignedOrg = RNCPHP\Organization::fetch( $assignedOrgID );
                $contact = RNCPHP\Contact::fetch( $CI->session->getProfileData( 'c_id' ) );
                logmessage( sprintf( "ID: %d\nIncident: %d\nOrganization: %d", $complaintAgainstOrg->ID, $complaintAgainstOrg->Incident->ID, $complaintAgainstOrg->Organization->ID ) );

                $log = new RNCPHP\auditLog\RedirectedCaseLog();
                $log->Contact = $contact;
                $log->Incident = $incident;
                $log->OriginalOrg = $complaintAgainstOrg->Organization;
                $log->AssignedOrg = $assignedOrg;
                $log->Reason = $assignedOrgReason;

                $log->save();
                RNCPHP\ConnectApi::commit();
            }
            catch( RNCPHP\ConnectApiErrorBase $err )
            {
                logmessage( sprintf( "Critical error occured when saving incident %d through RedirectedCase model:", $incidentID ) );
                logmessage( $err );
                RNCPHP\ConnectApi::rollback();
            }
        }
    }

    function caseHistory( $incidentID )
    {
        $reportID = getSetting( 'REDIRECTED_CASE_HISTORY_REPORT_ID' );
        $filterID = getSetting( 'REDIRECTED_CASE_HISTORY_REPORT_FILTER_ID' );
        $reportFilters = array();
        $reportFilters[] = $this->Report_model2->createSearchFilter(
            $reportID,
            getSetting( 'REDIRECTED_CASE_HISTORY_REPORT_FILTER_NAME' ),
            $filterID,
            intval( $incidentID )
        );

        return $this->_executeReport( $reportID, $reportFilters );
    }

    function complaintHandlersForOrg( $orgID )
    {
        $reportID = getSetting( 'REDIRECTED_COMPLAINT_HANDLER_REPORT_ID' );
        $filterID = getSetting( 'REDIRECTED_COMPLAINT_HANDLER_REPORT_FILTER_ID' );
        $reportFilters = array();
        $reportFilters[] = $this->Report_model2->createSearchFilter(
            $reportID,
            getSetting( 'REDIRECTED_COMPLAINT_HANDLER_REPORT_FILTER_NAME' ),
            $filterID,
            intval( $orgID )
        );

        $results = $this->_executeReport( $reportID, $reportFilters );
        $caseHandlers = array();

        // Records in this report have 2 columns - ID and Organization name. Reformat it to be an associative array.
        foreach( $results['data'] as $record )
        {
            $caseHandlers[$record[0]] = $record[1];
        }

        return $caseHandlers;
    }

    private function _executeReport( $reportID, $reportFilters )
    {
        $reportToken = createToken( $reportID );
        return $this->Report_model2->getDataHTML( $reportID, $reportToken, $reportFilters, array() );
    }
}
