<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class JobNotification extends Widget
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
        $organizationID = $this->CI->DataExportJobManager_model->getOrganizationIdForUser();

        if( !$organizationID )
        {
            $this->data['js']['authorized'] = false;
        }
        else
        {
            $this->data['js']['authorized'] = true;
            $this->data['js']['num_finished_jobs'] = $this->CI->DataExportJobManager_model->numberFinishedJobsForContact();
            if( $this->data['js']['num_finished_jobs'] > 1 )
                $this->data['js']['num_finished_jobs_notify_text'] = getLabel( 'EXPORT_PLURAL_JOBS_NOTIF_LBL' );
            else
                $this->data['js']['num_finished_jobs_notify_text'] = getLabel( 'EXPORT_SINGULAR_JOBS_NOTIF_LBL' );
        }
    }
}
