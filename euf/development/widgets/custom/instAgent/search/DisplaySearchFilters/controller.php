<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class DisplaySearchFilters extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['remove_icon_path'] = new Attribute(getMessage(REMOVE_ICON_PATH_CMD), 'STRING', getMessage(PATH_ICON_DISPLAY_FILTER_REMOVAL_LBL), 'images/remove.png');
        $this->attrs['label_filter_remove'] = new Attribute(getMessage(FILTER_REMOVE_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_FILTER_REMOVAL_LBL), getMessage(REMOVE_CMD));
        $this->attrs['label_title'] = new Attribute(getMessage(TITLE_LABEL_LBL), 'STRING', getMessage(LABEL_HEADING_SET_TXT_STRING_MSG_MSG), getMessage(SEARCH_FILTERS_APPLIED_CMD));
        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WDGET_DISP_SRCH_FLTRS_APPLIED_RPT_MSG);
        $this->parms['filter_name'] = new UrlParam(getMessage(FLTR_NAME_LBL), '{filter_name}', false, getMessage(FLTR_NAME_URL_PARAM_MATCHES_FLTR_MSG), 'p/1,4,6');
    }

    function getData()
    {
        $this->CI->load->model('custom/Report_model2');
        setFiltersFromUrl($this->data['attrs']['report_id'], $filters);

        $prodCatFilters = array();

        //products
        if($filters['p']->filters->data[0])
        {
            array_push($prodCatFilters, array('name' => 'p', 'filterID' => $filters['p']->filters->fltr_id, 'type' => 'map_prod_hierarchy', 'linkType' => 'p', 'label' => getMessage(PRODUCT_LBL), 'hierList' =>$filters['p']->filters->data[0]));
        }
        //categories
        if($filters['c']->filters->data[0])
        {
            array_push($prodCatFilters, array('name' => 'c', 'filterID' => $filters['c']->filters->fltr_id, 'type' => 'map_cat_hierarchy', 'linkType' => 'c', 'label' => getMessage(CATEGORY_LBL), 'hierList' =>$filters['c']->filters->data[0]));
        }
        $this->CI->load->model('standard/Prodcat_model');
        foreach($prodCatFilters as $key => $value)
        {
            $prodCatFilters[$key]['data'] = $this->_getDefaults($value['type'], $value['hierList']);
            $prodCatFilters[$key]['type'] = 'menufilter';
            unset($prodCatFilters[$key]['hierList']);
        }
        $this->data['js']['filters'] = $prodCatFilters;

        //get default filters (so they're displayed only if they change):
        // $this->CI->load->model('standard/Report_model');
        $defaultFilters = array();

        //search type
        $searchTypeFilters = $this->CI->Report_model2->getSearchFilterData($this->data['attrs']['report_id']);
        array_push($defaultFilters, array('name' => $filters['searchType']->type, 'filterID' => $filters['searchType']->filters->fltr_id));
        //orgs
        $profile = $this->CI->session->getProfile();
        if($profile && $profile->org_id->value > 0)
            array_push($defaultFilters, array('name' => 'org', 'filterID' => (getUrlParm('org')) ? getUrlParm('org') : 0));
        //custom

        $this->data['js']['defaultFilters'] = $defaultFilters;
        $this->data['js']['searchPage'] = '/app/' . $this->CI->page . '/';
        $this->data['widgetClass'] = (!count($this->data['js']['filters'])) ? 'rn_Hidden' : '';
    }

    /**
     * Utility function to retrieve hier menus and massage
     * the data for our usage.
     * @param $filterName String
     * @param $hierItems String Comma-separated list of hier menu IDs
     */
    private function _getDefaults($filterName, $hierItems)
    {
        $hierItems = explode(',', $hierItems);
        $hierArray = array();
        $hierList = '';
        for($i = 0; $i < count($hierItems) + 1; $i++)
        {
            if($i <= 5)
            {
                $arrayIndex = ($i === 0) ? 0 : $hierItems[$i - 1];
                $hierData = $this->CI->Prodcat_model->hierMenuGet($filterName, $i+1, $arrayIndex, $this->data['js']['linking_on']);
                foreach($hierData[0] as $value)
                {
                    //only care about selected nodes
                    if($value[0] == $hierItems[$i])
                    {
                        $hierList .= $value[0];
                        array_push($hierArray, array('value' => $value[0], 'label' => $value[1], 'hierList' => $hierList));
                        $hierList .= ',';
                    }
                }
            }
        }
        return $hierArray;
    }
}
