<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class JobDetail extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->CI->load->helper( 'config_helper' );
        $this->CI->load->helper( 'label_helper' );
        $this->CI->load->model( 'custom/DataExportJobManager_model' );
    }

    function generateWidgetInformation()
    {
        //Create information to display in the tag gallery here
        $this->info['notes'] =  "This widget shows the details for any given Job.";
    }

    function getData()
    {
        $jobId = getUrlParm( 'job' );
        $this->data['js']['dateFormat'] = 'l, M j g:i a';

        if( is_numeric( $jobId ) )
        {
            $this->data['js']['job'] = $this->CI->DataExportJobManager_model->getJob( $jobId );

            // Before we go any further - does this contact match the one who requested this job? If not, bail out immediately.
            $contactID = $this->CI->session->getProfileData( 'c_id' );
            if( $contactID !== $this->data['js']['job']->Contact->ID )
            {
                header( sprintf( "Location: %s", getSetting( 'ERROR_PAGE_URL' ) ) );
                exit;
            }
            else
            {
                $this->data['js']['cached_job_status'] = $this->data['js']['job']->Status->LookupName;
                $this->data['js']['attachments'] = $this->CI->DataExportJobManager_model->getAttachments( $this->data['js']['job']->FileAttachments );
                $this->data['js']['export_kickoff_url'] = getSetting( 'CASE_DATA_EXPORT_JOB_KICKOFF_URL' );

                $this->data['js']['zip_file'] = sprintf( '/tmp/DataExports/%s/%s.zip', $this->data['js']['job']->ID, $this->data['js']['job']->ID );
                $this->data['js']['zip_file_exists'] = file_exists( $this->data['js']['zip_file'] );

                $this->CI->DataExportJobManager_model->archiveJob( $this->data['js']['job']->ID );
            }
        }
        else
        {
            $this->data['js']['job'] = null;
        }
    }
}
