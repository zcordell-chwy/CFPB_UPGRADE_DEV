<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class InboundReferralDetailActive extends Widget
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

    }
}
