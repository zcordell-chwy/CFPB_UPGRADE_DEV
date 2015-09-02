<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class SearchTypeList2 extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_ID_LC_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['label_text'] = new Attribute(getMessage(LABEL_LBL), 'STRING', getMessage(STRING_LABEL_TO_DISPLAY_LBL), getMessage(SEARCH_TYPE_LBL));
        $this->attrs['search_on_select'] = new Attribute(getMessage(SEARCH_ON_SELECTED_CMD), 'BOOL', getMessage(START_SEARCH_SOON_ITEM_IS_SELECTED_MSG), false);
        $this->attrs['report_page_url'] = new Attribute(getMessage(REPORT_PAGE_LBL), 'STRING', getMessage(PG_DISP_ITEM_SEL_SRCH_SEL_SET_TRUE_MSG), '');
        $this->attrs['search_type_only'] = new Attribute(getMessage(ONLY_SEARCH_TYPE_LBL), 'BOOL', getMessage(TRUE_DISP_FLTRS_SRCH_FALSE_DISP_LBL), false);
        $this->attrs['hide_options'] = new Attribute('hide_option', 'STRING', 'Hide search type options by filter name', '');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WIDGET_DEFINES_OPTS_LISTED_SRCH_MSG);
        $this->parms['st'] = new UrlParam(getMessage(SEARCH_TYPE_LBL), 'st', false, getMessage(SETS_SEARCH_TYPE_URL_PARAM_VALUE_LBL), 'st/6');
    }

    function getData()
    {
        $this->CI->load->model('custom/Report_model2');
        if ($this->data['attrs']['search_type_only'])
            $filters = $this->CI->Report_model2->getSearchFilterData($this->data['attrs']['report_id']);
        else
            $filters = $this->CI->Report_model2->getRuntimeIntTextData($this->data['attrs']['report_id']);
        if (!(count($filters)))
        {
            echo $this->reportError(getMessage(SEARCH_FILTERS_AVAILABLE_REPORT_MSG) . ' ' . $this->data['attrs']['report_id']);
            return false;
        }
        else
        {
            //rename ref_no from "Reference #" to "Case number"
            // not sure why we needed this, this is defined in the report search filter
            /*foreach($filters as $key => $value)
            {
                if (stringContains($value['expression1'], 'ref_no'))
                {
                    $value['prompt'] = getLabel('CASE_NO');
                    $filters[$key] = $value;
                }
            }*/

            $this->data['js']['filters'] = $filters;
        }
        setFiltersFromUrl($this->data['attrs']['report_id'], $default);
        $searchType = $this->CI->Report_model2->getSearchFilterTypeDefault($this->data['attrs']['report_id']);
        $this->data['js']['defaultFilter'] = $default['searchType']->filters->fltr_id;
        $this->data['js']['rnSearchType'] = 'searchType';
        $this->data['js']['searchName'] = 'searchType';
        
        //echo"filters<pre>";print_r($this->data);echo"</pre>";
        //hide search options by filter name
        if ($this->data['attrs']['hide_options'])
            $this->data['js']['filters'] = $this->hideFilters($this->data['js']['filters']);
        //echo"filters<pre>";print_r($this->data['js']['filters']);echo"</pre>";
    }

    // local function to hide filters specified by hide_options attribute
    // only hide at the view level
    // NOTE: make sure to hide filters that are not displayed first!
    private function hideFilters($filters)
    {
        $newFilters = array();
        $hideFilters = explode(",", $this->data['attrs']['hide_options']);
        foreach ($filters as $key => $filter)
        {
            if (!in_array($filter['name'], $hideFilters))
                $newFilters[$key] = $filter;
        }
        
        return $newFilters;
    }
}
