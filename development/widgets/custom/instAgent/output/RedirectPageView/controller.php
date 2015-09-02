<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class RedirectPageView extends Widget
{
    function __construct()
    {
        parent::__construct();
    }

    function generateWidgetInformation()
    {
        //Create information to display in the tag gallery here
        $this->info['notes'] = "Controls page flow depending on selected closure - used in list.php";
    }

    function getData()
    {
        $this->data['js']['location'] = "/app/instAgent/list_active";

        try
        {
            $this->CI->load->model('custom/instagent_model');
            $this->CI->load->helper('label_helper');

            $compId = getUrlParm('comp_id');
            $compObj = $this->CI->instagent_model->getComplaint($compId);
            $incident = $compObj->Incident;
            $bank_status = $incident->CustomFields->bank_statuses->LookupName;

            $co_status = getLabel('CO_STATUS_ARRAY');

            //$stat = print_r($co_status, true);
            //echo("compId: [$compId] bank_status: [$bank_status] co_status: [$stat]");

            // for non-admin status, go back to detail page
            switch ($bank_status)
            {
                case $co_status['CO_STATUS_CLOSED']:
                case $co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
                case $co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
                case $co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
                    $this->data['js']['location'] = "/app/instAgent/detail/comp_id/$compId";
                    break;

                default:
                    $this->data['js']['location'] = "/app/instAgent/list_active";
                    break;
            }
        }
        catch (Exception $e)
        {
        }

    }

}
