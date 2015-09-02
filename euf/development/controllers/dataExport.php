<?

require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
if( !initConnectAPI() ){
   phpoutlog("Error initializing Connect");
   throw new Exception("Connect initialization failure; ensure that II_CONNECT_ENABLED is set");
}
use RightNow\Connect\v1_2 as RNCPHP;

class DataExport extends ControllerBase {

    function __construct()
    {
        parent::__construct();
        $this->load->model( 'custom/DataExportJobManager_model' );
        $this->load->model( 'custom/ContactPermissions_model' );
        $this->load->helper( 'config_helper' );
    }

    function incidents()
    {
        // Perform security checks.
        AbuseDetection::check( $this->input->post( 'f_tok' ) );
        $organizationID = $this->getOrganizationID();
		
        // Gather needed data.
        $reports = json_decode( $this->input->post( 'reports' ) );
        $filters = $this->input->post( 'filters' );

		//security check if for some reaaon the state filter hasn't been passed through, when one is associated with the contact, manually reapply it
		$CI = get_instance();
		$c_id = $CI->session->getProfileData('c_id');
		
		$contact = RNCPHP\Contact::fetch($c_id);
		$state_jurisdiction = $contact->Organization->CustomFields->c->state_jurisdiction->LookupName;
		
		$filter_check = json_decode($this->input->post( 'filters'),true);

		if(strlen($state_jurisdiction) > 0 && $state_jurisdiction != $filter_check['consumer_state'])
		{
			$filter_check['consumer_state'] = $state_jurisdiction;
			$filters = json_encode($filter_check);
		}
					
        $success = $this->DataExportJobManager_model->createJob( $reports, $filters );

        if( $success )
            $message = 'Successfully created your data export job.';
        else
            $message = 'Error while creating your data export job.';

        echo json_encode( array( 'success' => $success, 'message' => $message, 'job_id' => $this->DataExportJobManager_model->getJobId() ) );
    }

    function attachment()
    {
        // Perform security checks.
        AbuseDetection::check( $this->input->post( 'f_tok' ) );
        $organizationID = $this->getOrganizationID();

        $fileName = $this->input->post( 'file' );
        if( $fileName != '' && file_exists( $fileName ) )
        {
            // $fileName should be a full path to tmp. Grab the last segment as the user-friendly file name.
            $token = explode( '/', $fileName );
            $nameIndex = count( $token ) - 1;

            header( sprintf( 'Content-disposition: attachment; filename=%s', $token[$nameIndex] ) );
            header( 'Content-type: application/zip' );
            readfile( $fileName );
        }
        else
        {
            echo sprintf( "<p>Could not find '%s'</p>", $fileName );
        }
    }

    function numberFinishedJobsForContact()
    {
        // Perform security checks.
        AbuseDetection::check( $this->input->post( 'f_tok' ) );
        $organizationID = $this->getOrganizationID();

        $numJobs = $this->DataExportJobManager_model->numberFinishedJobsForContact();

        echo json_encode( array( 'numJobs' => $numJobs ) );
    }

    private function getOrganizationID()
    {
        $organizationID = $this->DataExportJobManager_model->getOrganizationIdForUser();
        if( $organizationID )
        {
            return $organizationID;
        }
        else
        {
            header("HTTP/1.1 403 Forbidden");
            exit(getMessage(ACCESS_DENIED_HDG));
        }
    }
}
