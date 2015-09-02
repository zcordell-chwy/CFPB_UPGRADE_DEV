<?php
use RightNow\Connect\v1 as RNCPHP;

class Page_render_model extends Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("standard/Incident_model");
        $this->load->model("custom/Instagent_model");
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();
    }

     /**
     * Apply attributes to page
     * If the any of the detail page opens an incident, we will check to see if 
     * isunread is "Yes", then set it to "No"
     */
    
    function applyPageAttributes()
    {
        $CI =& get_instance();
        $i_id = getUrlParm('i_id');
        $comp_id = getUrlParm('comp_id');
        
        if (strpos($CI->page, "detail") > 0)
        {
            // if we have comp id, get incident object from instagent model
            if ($comp_id) 
            {   
                $complaint = $CI->Instagent_model->getComplaint($comp_id);
                $incident = $complaint->Incident;
            } 
            else if ($i_id)
            {
                $incident = $CI->Incident_model->get($i_id);
            }
            if ($incident->CustomFields->isunread == 1)
            {
                $incident->CustomFields->isunread = 0;
                $incident->save();
            }
        }
    }   
}
