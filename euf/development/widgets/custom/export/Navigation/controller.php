<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Navigation extends Widget
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
        $this->info['notes'] =  "This widget conditionally shows users a navigation tab to the job status page.";
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
        }
    }
}
