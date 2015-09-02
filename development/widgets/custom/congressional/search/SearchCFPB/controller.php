<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class SearchCFPB extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_ID_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['search_prod'] = new Attribute("search_prod", 'BOOL', "Flag to hide/display the product filter", true);
        $this->attrs['search_cat'] = new Attribute("search_cat", 'BOOL', "Flag to hide/display the category filter", true);
        $this->attrs['search_state'] = new Attribute( "Search State", 'BOOL', "Flag to optionally hide the state filter. Defaults to true, showing the filter if a government portal user.", FALSE );
        $this->attrs['hide_prod'] = new Attribute("hide_prod", 'STRING', "String of product id's to hide", null);
        $this->attrs['hide_cat'] = new Attribute("hide_cat", 'STRING', "String of category id's to hide", null);
        $this->attrs['search_date_range'] = new Attribute( 'Search Date Range', 'BOOL', "Flag to optionally allow users to search by date range. Needed for federal government portal. Defaults to false.", false );
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  'Contains formatting to display search widgets in a single row';
    }

    function getData()
    {
        $this->CI->load->model( 'custom/ContactPermissions_model' );
        $this->data['userType'] = $this->CI->ContactPermissions_model->userType();
    }
}
