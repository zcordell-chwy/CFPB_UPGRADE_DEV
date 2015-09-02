<?php /* Originating Release: August 2012 */

  if (!defined('BASEPATH')) exit('No direct script access allowed');

class FilterDropdown2 extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_ID_LC_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['filter_name'] = new Attribute(getMessage(FILTER_NAME_LBL), 'STRING', getMessage(FILTER_DISP_DROPDOWN_INFORMATION_LBL), '');
        $this->attrs['filter_name']->required = true;
        $this->attrs['label_any'] = new Attribute(getMessage(ANY_LBL), 'STRING', getMessage(TEXT_FOR_FIRST_DROP_DOWN_ITEM_LBL), getMessage(ANY_LBL));
        $this->attrs['search_on_select'] = new Attribute(getMessage(SEARCH_ON_SELECTED_CMD), 'BOOL', getMessage(START_SEARCH_SOON_ITEM_IS_SELECTED_MSG), false);
        $this->attrs['report_page_url'] = new Attribute(getMessage(REPORT_PAGE_LBL), 'STRING', getMessage(PG_DISP_ITEM_SEL_SRCH_SEL_SET_TRUE_MSG), '');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(CTRL_RQS_RUNTIME_FLTR_TYPE_MENU_LBL);
        $this->parms['{filter name}'] = new UrlParam(getMessage(FILTER_NAME_LBL), '{filter name}', false, getMessage(SETS_CUST_MENU_PD_SELECTED_IDX_LBL), 'customMenu/22');
    }

    function getData()
    {
        $list = array();
        $this->CI->load->model('standard/Report_model');
        setFiltersFromAttributesAndUrl($this->data['attrs'], $allFilters);
        $filters = $this->CI->Report_model->getFilterByName($this->data['attrs']['report_id'], $this->data['attrs']['filter_name']);
        if ($filters)
        {
            $list = optlistGet($filters['optlist_id']);
            // get values
            $i = 0;
            foreach ($list as $key => $value)
            {
                if (is_int($key))
                {
                    $optl[$i]['id'] = $key;
                    $optl[$i]['label'] = $value;
                    $i++;
                }
            }
            $parm = $allFilters[$this->data['attrs']['filter_name']]->filters->data[0];
            $this->data['js'] = array('filters' => $filters,
                                      'name' => $filters['prompt'],
                                      'list' => $optl,
                                      'defaultValue' => $parm ? $parm : $filters['default_value']
                                      );

        }
        else
        {
            echo $this->reportError(sprintf(getMessage(FILTER_PCT_S_EXIST_REPORT_PCT_S_LBL), $this->data['attrs']['filter_name'], $this->data['attrs']['report_id']));
            return false;
        }
        $this->data['js']['rnSearchType'] = 'filterDropdown';
        $this->data['js']['searchName'] = $this->data['attrs']['filter_name'];
    }
}
