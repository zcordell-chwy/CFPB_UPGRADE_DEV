<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class GenericFileAttachments extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['table'] = new Attribute('Table name', 'STRING', 'Name of the Custom Object', '');
        $this->attrs['comp_id'] = new Attribute('Complaint ID', 'STRING', 'Complaint is the source object to find the investigation', '');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = 'Provides a way to retrieve generic file attachments';
    }

    function getData()
    {
        
    }
}
