<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('ProdCatNotificationManager'))
    requireWidgetController('standard/notifications/ProdCatNotificationManager');

class SubscribeLink extends ProdCatNotificationManager
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['filter_type'] = new Attribute('filter_type', 'STRING', 'Hiermenu type name (products/categories)', 'categories');
        $this->attrs['hier_map'] = new Attribute('hier_map', 'STRING', 'Hiermenu mapping array of comma delimited by level starting from parent (i.e. 1222,1223)', null);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  'Displays subscribe/unsubscribe link/button based on filter_type and hier_map attributes';
    }

    function getData()
    {
        parent::getData();
    }
}
